<?php

declare(strict_types=1);

use Orm\Connection\Database;
use Orm\QueryBuilder\QueryBuilder;

require_once __DIR__ . '/support/validator.php';

/** Fonctions métier "étudiants" — même logique que livre.php. */

/** Exercice complémentaire 1 : recherche multicritère + pagination. */
function etudiant_lister(int $page = 1, int $perPage = 10, ?string $recherche = null): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    $recherche = trim((string) $recherche);

    $base = new QueryBuilder('etudiants');
    if ($recherche !== '') {
        $base->whereLike('nom', $recherche)
            ->orWhereLike('prenom', $recherche)
            ->orWhereLike('email', $recherche)
            ->orWhereLike('filiere', $recherche);
    }

    $total = (clone $base)->count();
    $rows = (clone $base)->orderBy('nom')->limit($perPage, $offset)->get();

    return [
        'data'        => $rows,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int) ceil($total / max(1, $perPage)),
    ];
}

/** Liste complète, sans pagination — utile pour les listes déroulantes (ex. formulaire d'emprunt). */
function etudiant_tous(): array
{
    return (new QueryBuilder('etudiants'))->orderBy('nom')->get();
}

function etudiant_trouver(int $id): ?array
{
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM etudiants WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/** @return array<string,string> */
function etudiant_valider(array $data): array
{
    $errors = [];
    valider_requis($data, 'nom', $errors);
    valider_longueur_max($data, 'nom', 50, $errors);
    valider_requis($data, 'prenom', $errors);
    valider_longueur_max($data, 'prenom', 50, $errors);
    valider_requis($data, 'email', $errors);
    valider_email($data, 'email', $errors);
    valider_longueur_max($data, 'email', 100, $errors);

    return $errors;
}

/** @return array{success:bool, errors:array<string,string>, etudiant:?array} */
function etudiant_creer(array $data): array
{
    $errors = etudiant_valider($data);
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors, 'etudiant' => null];
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare(
        'INSERT INTO etudiants (nom, prenom, email, telephone, filiere) VALUES (:nom, :prenom, :email, :telephone, :filiere)'
    );
    $stmt->execute([
        'nom'       => nettoyer_chaine((string) $data['nom']),
        'prenom'    => nettoyer_chaine((string) $data['prenom']),
        'email'     => nettoyer_chaine((string) $data['email']),
        'telephone' => isset($data['telephone']) && $data['telephone'] !== '' ? nettoyer_chaine((string) $data['telephone']) : null,
        'filiere'   => isset($data['filiere']) && $data['filiere'] !== '' ? nettoyer_chaine((string) $data['filiere']) : null,
    ]);

    $id = (int) $pdo->lastInsertId();

    return ['success' => true, 'errors' => [], 'etudiant' => etudiant_trouver($id)];
}

/** @return array{success:bool, errors:array<string,string>} */
function etudiant_modifier(int $id, array $data): array
{
    if (etudiant_trouver($id) === null) {
        return ['success' => false, 'errors' => ['id' => 'Étudiant introuvable.']];
    }

    $errors = etudiant_valider($data);
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare(
        'UPDATE etudiants SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, filiere = :filiere WHERE id = :id'
    );
    $stmt->execute([
        'nom'       => nettoyer_chaine((string) $data['nom']),
        'prenom'    => nettoyer_chaine((string) $data['prenom']),
        'email'     => nettoyer_chaine((string) $data['email']),
        'telephone' => isset($data['telephone']) && $data['telephone'] !== '' ? nettoyer_chaine((string) $data['telephone']) : null,
        'filiere'   => isset($data['filiere']) && $data['filiere'] !== '' ? nettoyer_chaine((string) $data['filiere']) : null,
        'id'        => $id,
    ]);

    return ['success' => true, 'errors' => []];
}

function etudiant_supprimer(int $id): bool
{
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('DELETE FROM etudiants WHERE id = :id');

    return $stmt->execute(['id' => $id]);
}
