<?php

namespace Orm\Entity;

use ReflectionClass;

/**
 * Classe de base pour toutes les entités (Livre, Etudiant, Emprunt, Utilisateur...).
 *
 * Fournit :
 *  - l'hydratation automatique depuis une ligne PDO (fetch assoc en snake_case)
 *  - la conversion inverse (propriétés PHP en camelCase -> colonnes SQL en snake_case)
 *  - le nom de table par convention (Livre -> livres, Etudiant -> etudiants, ...)
 *
 * Les entités concrètes (dans backend/Model/) doivent hériter de cette classe
 * et déclarer leurs propriétés publiques (titre, auteur, isbn, etc.).
 */
abstract class AbstractEntity
{
    public ?int $id = null;

    /**
     * Remplit les propriétés de l'entité à partir d'une ligne PDO.
     * Ex: ['date_emprunt' => '2026-01-01'] -> $entity->dateEmprunt
     */
    public function hydrate(array $row): static
    {
        foreach ($row as $column => $value) {
            $property = self::snakeToCamel($column);

            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            } elseif (property_exists($this, $column)) {
                $this->{$column} = $value;
            }
        }

        return $this;
    }

    /**
     * Retourne les propriétés publiques de l'entité en tableau
     * associatif snake_case => valeur, prêt pour un INSERT/UPDATE.
     * (id volontairement exclu, géré par le repository)
     */
    public function toArray(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($property === 'id') {
                continue;
            }
            $data[self::camelToSnake($property)] = $value;
        }

        return $data;
    }

    /**
     * Nom de table déduit du nom de la classe : Livre -> livres.
     * Peut être surchargé dans une entité si le pluriel est irrégulier.
     */
    public static function tableName(): string
    {
        $short = (new ReflectionClass(static::class))->getShortName();

        return strtolower($short) . 's';
    }

    private static function snakeToCamel(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    private static function camelToSnake(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
}
