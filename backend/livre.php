<?php

declare(strict_types=1);

use Orm\Connection\Database;
use Orm\QueryBuilder\QueryBuilder;

require_once __DIR__ . '/support/validator.php';

/**
 * Fonctions métier "livres". Aucune classe : on utilise directement les
 * outils fournis par orm/ (Database::getInstance(), QueryBuilder) plutôt
 * que d'hériter de Orm\Repository\AbstractRepository (ce qui imposerait
 * de créer une classe LivreRepository). Toutes les données circulent sous
 * forme de tableaux associatifs (mêmes clés que les colonnes SQL).
 */

/** Liste paginée, avec recherche optionnelle (titre / auteur / ISBN). */
function livre_lister(int $page = 1, int $perPage = 10, ?string $recherche = null): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    $recherche = trim((string) $recherche);

    $base = new QueryBuilder('livres');
    if ($recherche !== '') {
        $base->whereLike('titre', $recherche)
            ->orWhereLike('auteur', $recherche)
            ->orWhereLike('isbn', $recherche);
    }

    $total = (clone $base)->count();
    $rows = (clone $base)->orderBy('titre')->limit($perPage, $offset)->get();

    return [
        'data'        => $rows,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int) ceil($total / max(1, $perPage)),
    ];
}

function livre_trouver(int $id): ?array
{
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM livres WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/** Exercice complémentaire 2 : livres disponibles (quantité > 0). */
function livre_disponibles(): array
{
    return (new QueryBuilder('livres'))
        ->where('quantite', '>', 0)
        ->orderBy('titre')
        ->get();
}

/** @return array<string,string> Erreurs, vide si les données sont valides. */
function livre_valider(array $data): array
{
    $errors = [];
    valider_requis($data, 'titre', $errors);
    valider_longueur_max($data, 'titre', 150, $errors);
    valider_requis($data, 'auteur', $errors);
    valider_longueur_max($data, 'auteur', 100, $errors);
    valider_requis($data, 'isbn', $errors);
    valider_longueur_max($data, 'isbn', 20, $errors);
    valider_annee($data, 'annee', $errors);
    valider_entier_positif($data, 'quantite', $errors, 'La quantité');

    return $errors;
}

/**
 * @param array<string,mixed> $data
 * @return array{success:bool, errors:array<string,string>, livre:?array}
 */
function livre_creer(array $data): array
{
    $errors = livre_valider($data);
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors, 'livre' => null];
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare(
        'INSERT INTO livres (titre, auteur, isbn, annee, quantite) VALUES (:titre, :auteur, :isbn, :annee, :quantite)'
    );
    $stmt->execute([
        'titre'    => nettoyer_chaine((string) $data['titre']),
        'auteur'   => nettoyer_chaine((string) $data['auteur']),
        'isbn'     => nettoyer_chaine((string) $data['isbn']),
        'annee'    => (int) $data['annee'],
        'quantite' => (int) $data['quantite'],
    ]);

    $id = (int) $pdo->lastInsertId();

    return ['success' => true, 'errors' => [], 'livre' => livre_trouver($id)];
}

/**
 * @param array<string,mixed> $data
 * @return array{success:bool, errors:array<string,string>}
 */
function livre_modifier(int $id, array $data): array
{
    if (livre_trouver($id) === null) {
        return ['success' => false, 'errors' => ['id' => 'Livre introuvable.']];
    }

    $errors = livre_valider($data);
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare(
        'UPDATE livres SET titre = :titre, auteur = :auteur, isbn = :isbn, annee = :annee, quantite = :quantite WHERE id = :id'
    );
    $stmt->execute([
        'titre'    => nettoyer_chaine((string) $data['titre']),
        'auteur'   => nettoyer_chaine((string) $data['auteur']),
        'isbn'     => nettoyer_chaine((string) $data['isbn']),
        'annee'    => (int) $data['annee'],
        'quantite' => (int) $data['quantite'],
        'id'       => $id,
    ]);

    return ['success' => true, 'errors' => []];
}

function livre_supprimer(int $id): bool
{
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('DELETE FROM livres WHERE id = :id');

    return $stmt->execute(['id' => $id]);
}
