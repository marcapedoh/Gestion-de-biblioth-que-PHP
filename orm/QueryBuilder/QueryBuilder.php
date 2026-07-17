<?php

namespace Orm\QueryBuilder;

use InvalidArgumentException;
use Orm\Connection\Database;
use PDO;

/**
 * QueryBuilder fluide pour les SELECT plus complexes que ceux couverts par
 * AbstractRepository : recherche multicritère (titre OU auteur OU ISBN),
 * livres disponibles, emprunts en retard, etc. (exercices complémentaires
 * 1 à 3 du sujet).
 *
 * Toujours des requêtes préparées : les valeurs ne sont jamais concaténées
 * dans le SQL, seuls les noms de colonnes/opérateurs sont validés par
 * liste blanche.
 *
 * Exemple (recherche multicritère livres, exercice de base du sujet) :
 *
 *   $rows = (new QueryBuilder('livres'))
 *       ->whereLike('titre', $terme)
 *       ->orWhereLike('auteur', $terme)
 *       ->orWhereLike('isbn', $terme)
 *       ->orderBy('titre')
 *       ->limit(10, 0)
 *       ->get();
 *
 * Exemple (Exercice 2 : livres disponibles) :
 *   (new QueryBuilder('livres'))->where('quantite', '>', 0)->get();
 *
 * Exemple (Exercice 3 : emprunts en retard) :
 *   (new QueryBuilder('emprunts'))
 *       ->where('statut', '=', 'En cours')
 *       ->where('date_retour_prevue', '<', date('Y-m-d'))
 *       ->get();
 */
class QueryBuilder
{
    private const ALLOWED_OPERATORS = ['=', '!=', '<', '<=', '>', '>=', 'LIKE'];

    private PDO $pdo;
    private string $table;
    private array $selects = ['*'];
    private array $wheres = []; // [['sql' => ..., 'boolean' => 'AND'|'OR']]
    private array $params = [];
    private ?string $orderColumn = null;
    private string $orderDirection = 'ASC';
    private ?int $limit = null;
    private ?int $offset = null;
    private int $paramCounter = 0;

    public function __construct(string $table)
    {
        $this->table = $this->sanitizeIdentifier($table);
        $this->pdo = Database::getInstance();
    }

    public function select(array $columns): static
    {
        $this->selects = array_map([$this, 'sanitizeIdentifier'], $columns);

        return $this;
    }

    public function where(string $column, string $operator, mixed $value): static
    {
        return $this->addCondition($column, $operator, $value, 'AND');
    }

    public function orWhere(string $column, string $operator, mixed $value): static
    {
        return $this->addCondition($column, $operator, $value, 'OR');
    }

    public function whereLike(string $column, string $value): static
    {
        return $this->addCondition($column, 'LIKE', "%{$value}%", 'AND');
    }

    public function orWhereLike(string $column, string $value): static
    {
        return $this->addCondition($column, 'LIKE', "%{$value}%", 'OR');
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderColumn = $this->sanitizeIdentifier($column);
        $this->orderDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /** @return array<int, array<string, mixed>> */
    public function get(): array
    {
        [$sql, $params] = $this->buildSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1, $this->offset ?? 0);
        $rows = $this->get();

        return $rows[0] ?? null;
    }

    public function count(): int
    {
        $originalSelects = $this->selects;
        $this->selects = ['COUNT(*) AS total'];
        [$sql, $params] = $this->buildSelect(withLimit: false);
        $this->selects = $originalSelects;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    private function addCondition(string $column, string $operator, mixed $value, string $boolean): static
    {
        $column = $this->sanitizeIdentifier($column);
        $operator = strtoupper($operator);

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException("Opérateur non autorisé : {$operator}");
        }

        $placeholder = 'p' . (++$this->paramCounter);
        $this->wheres[] = [
            'sql'     => "{$column} {$operator} :{$placeholder}",
            'boolean' => $boolean,
        ];
        $this->params[$placeholder] = $value;

        return $this;
    }

    private function buildSelect(bool $withLimit = true): array
    {
        $sql = 'SELECT ' . implode(', ', $this->selects) . " FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            foreach ($this->wheres as $i => $where) {
                $sql .= $i === 0 ? $where['sql'] : " {$where['boolean']} {$where['sql']}";
            }
        }

        if ($this->orderColumn !== null) {
            $sql .= " ORDER BY {$this->orderColumn} {$this->orderDirection}";
        }

        if ($withLimit && $this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return [$sql, $this->params];
    }

    private function sanitizeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Identifiant SQL invalide : {$identifier}");
        }

        return $identifier;
    }
}
