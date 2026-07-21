<?php

/**
 * models/Etudiant.php
 *
 * Toutes les opérations sur la table "etudiants", en fonctions PHP
 * simples (pas de classe).
 */

/**
 * Ajoute un étudiant. Retourne l'id créé.
 */
function ajouterEtudiant(PDO $pdo, string $nom, string $prenom, string $email, ?string $telephone, ?string $filiere): int
{
    $sql = "INSERT INTO etudiants (nom, prenom, email, telephone, filiere)
            VALUES (:nom, :prenom, :email, :telephone, :filiere)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom'       => $nom,
        'prenom'    => $prenom,
        'email'     => $email,
        'telephone' => $telephone,
        'filiere'   => $filiere,
    ]);

    return (int) $pdo->lastInsertId('etudiants_id_seq');
}

/**
 * Modifie un étudiant existant.
 */
function modifierEtudiant(PDO $pdo, int $id, string $nom, string $prenom, string $email, ?string $telephone, ?string $filiere): bool
{
    $sql = "UPDATE etudiants
            SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, filiere = :filiere
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        'nom'       => $nom,
        'prenom'    => $prenom,
        'email'     => $email,
        'telephone' => $telephone,
        'filiere'   => $filiere,
        'id'        => $id,
    ]);
}

/**
 * Supprime un étudiant par son id.
 */
function supprimerEtudiant(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = :id");

    return $stmt->execute(['id' => $id]);
}

/**
 * Retourne un étudiant par son id, ou null si introuvable.
 */
function trouverEtudiantParId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $etudiant = $stmt->fetch();

    return $etudiant ?: null;
}

/**
 * Liste tous les étudiants, triés par une colonne donnée.
 */
function listerEtudiants(PDO $pdo, string $colonneTri = 'nom', string $direction = 'ASC'): array
{
    $colonnesAutorisees = ['id', 'nom', 'prenom', 'email', 'filiere'];
    if (!in_array($colonneTri, $colonnesAutorisees, true)) {
        $colonneTri = 'nom';
    }
    $direction = (strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';

    $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY {$colonneTri} {$direction}");

    return $stmt->fetchAll();
}

/**
 * Exercice 1 : recherche multicritère sur les étudiants
 * (nom, prénom, email ou filière).
 */
function rechercherEtudiants(PDO $pdo, string $terme): array
{
    $sql = "SELECT * FROM etudiants
            WHERE nom ILIKE :terme OR prenom ILIKE :terme
               OR email ILIKE :terme OR filiere ILIKE :terme
            ORDER BY nom ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['terme' => "%{$terme}%"]);

    return $stmt->fetchAll();
}

/**
 * Pagination : 10 résultats par page par défaut.
 */
function paginerEtudiants(PDO $pdo, int $page = 1, int $parPage = 10): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $parPage;

    $stmt = $pdo->prepare("SELECT * FROM etudiants ORDER BY nom ASC LIMIT :limite OFFSET :offset");
    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $total = (int) $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();

    return [
        'donnees'     => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'par_page'    => $parPage,
        'total_pages' => (int) ceil($total / max(1, $parPage)),
    ];
}
