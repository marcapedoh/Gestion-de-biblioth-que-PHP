<?php

namespace Orm\Repository;

use InvalidArgumentException;
use LogicException;
use Orm\Connection\Database;
use Orm\Entity\AbstractEntity;
use PDO;

/**
 * Repository générique : toute la logique CRUD commune à toutes les entités.
 *
 * Règle QA du README d'équipe : aucune requête SQL brute en dehors de orm/.
 * Les repositories spécifiques (backend/Repository/LivreRepository.php, etc.)
 * héritent de cette classe et se contentent de déclarer getEntityClass().
 *
 * Exemple d'utilisation dans backend/Repository/LivreRepository.php :
 *
 *   class LivreRepository extends AbstractRepository
 *   {
 *       protected function getEntityClass(): string
 *       {
 *           return Livre::class;
 *       }
 *   }
 */
abstract class AbstractRepository
{
    protected PDO $pdo;
    protected string $table;
    protected string $entityClass;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->entityClass = $this->getEntityClass();
        $this->table = $this->entityClass::tableName();
    }

    /**
     * Chaque repository concret indique quelle entité il gère.
     * Ex: return Livre::class;
     */
    abstract protected function getEntityClass(): string;

    public function find(int $id): ?AbstractEntity
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrateOne($row) : null;
    }

    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $orderBy = $this->sanitizeColumn($orderBy);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");

        return $this->hydrateAll($stmt->fetchAll());
    }

    /**
     * Recherche par égalité stricte sur une ou plusieurs colonnes.
     * Ex: findBy(['statut' => 'En cours']) pour les emprunts en cours.
     */
    public function findBy(array $criteria): array
    {
        if (empty($criteria)) {
            return $this->findAll();
        }

        $conditions = [];
        $params = [];
        foreach ($criteria as $column => $value) {
            $column = $this->sanitizeColumn($column);
            $conditions[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->hydrateAll($stmt->fetchAll());
    }

    /**
     * Pagination générique : 10 résultats par page par défaut (cahier des charges).
     * Retourne les données ET les métadonnées, prêtes à être affichées par le front.
     */
    public function paginate(int $page = 1, int $perPage = 10, string $orderBy = 'id'): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $orderBy = $this->sanitizeColumn($orderBy);

        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} ASC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $total = (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

        return [
            'data'        => $this->hydrateAll($stmt->fetchAll()),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function save(AbstractEntity $entity): AbstractEntity
    {
        $data = $entity->toArray();
        $columns = array_keys($data);

        $columnsSql = implode(', ', $columns);
        $placeholders = implode(', ', array_map(static fn ($c) => ":{$c}", $columns));

        $sql = "INSERT INTO {$this->table} ({$columnsSql}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        $entity->id = (int) $this->pdo->lastInsertId();

        return $entity;
    }

    public function update(AbstractEntity $entity): bool
    {
        if ($entity->id === null) {
            throw new LogicException('Impossible de mettre à jour une entité sans id.');
        }

        $data = $entity->toArray();
        $assignments = implode(', ', array_map(static fn ($c) => "{$c} = :{$c}", array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$assignments} WHERE id = :id";
        $data['id'] = $entity->id;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    protected function hydrateOne(array $row): AbstractEntity
    {
        /** @var AbstractEntity $entity */
        $entity = new $this->entityClass();

        return $entity->hydrate($row);
    }

    protected function hydrateAll(array $rows): array
    {
        return array_map(fn (array $row) => $this->hydrateOne($row), $rows);
    }

    /**
     * Anti-injection sur les NOMS de colonnes utilisés dans ORDER BY / WHERE
     * (impossible à passer en requête préparée). Les valeurs, elles, passent
     * toujours par des paramètres liés.
     */
    protected function sanitizeColumn(string $column): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new InvalidArgumentException("Nom de colonne invalide : {$column}");
        }

        return $column;
    }
}
