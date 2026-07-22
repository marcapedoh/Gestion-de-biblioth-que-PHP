<?php

/**
 * models/Livre.php
 *
 * Toutes les opérations sur la table "livres", en fonctions PHP simples
 * (pas de classe). Chaque fonction prend $pdo en paramètre pour rester
 * facilement testable et réutilisable depuis n'importe quel controller.
 *
 * Utilisation typique dans un controller :
 *   require_once __DIR__ . '/../config/database.php';
 *   require_once __DIR__ . '/../models/Livre.php';
 *   $pdo = getConnexionPDO();
 *   $livres = listerLivres($pdo);
 */

/**
 * Ajoute un livre. Retourne l'id du livre créé.
 */
function ajouterLivre(PDO $pdo, string $titre, string $auteur, string $isbn, int $annee, int $quantite): int
{
    $sql = "INSERT INTO livres (titre, auteur, isbn, annee, quantite)
            VALUES (:titre, :auteur, :isbn, :annee, :quantite)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'titre'    => $titre,
        'auteur'   => $auteur,
        'isbn'     => $isbn,
        'annee'    => $annee,
        'quantite' => $quantite,
    ]);

    return (int) $pdo->lastInsertId('livres_id_seq'); // PostgreSQL : nom de la séquence
}

/**
 * Modifie un livre existant. Retourne true si la mise à jour a réussi.
 */
function modifierLivre(PDO $pdo, int $id, string $titre, string $auteur, string $isbn, int $annee, int $quantite): bool
{
    $sql = "UPDATE livres
            SET titre = :titre, auteur = :auteur, isbn = :isbn, annee = :annee, quantite = :quantite
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        'titre'    => $titre,
        'auteur'   => $auteur,
        'isbn'     => $isbn,
        'annee'    => $annee,
        'quantite' => $quantite,
        'id'       => $id,
    ]);
}

/**
 * Supprime un livre par son id.
 */
function supprimerLivre(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare("DELETE FROM livres WHERE id = :id");

    return $stmt->execute(['id' => $id]);
}

/**
 * Retourne un livre par son id, ou null si introuvable.
 */
function trouverLivreParId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $livre = $stmt->fetch();

    return $livre ?: null;
}

/**
 * Liste tous les livres, triés par une colonne donnée.
 */
function listerLivres(PDO $pdo, string $colonneTri = 'titre', string $direction = 'ASC'): array
{
    $colonnesAutorisees = ['id', 'titre', 'auteur', 'isbn', 'annee', 'quantite'];
    if (!in_array($colonneTri, $colonnesAutorisees, true)) {
        $colonneTri = 'titre';
    }
    $direction = (strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';

    $stmt = $pdo->query("SELECT * FROM livres ORDER BY {$colonneTri} {$direction}");

    return $stmt->fetchAll();
}

/**
 * Recherche multicritère : titre, auteur ou ISBN contiennent le terme.
 */
function rechercherLivres(PDO $pdo, string $terme): array
{
    $sql = "SELECT * FROM livres
            WHERE titre ILIKE :terme OR auteur ILIKE :terme OR isbn ILIKE :terme
            ORDER BY titre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['terme' => "%{$terme}%"]);

    return $stmt->fetchAll();
}

/**
 * Exercice 2 : uniquement les livres disponibles (quantité > 0).
 */
function listerLivresDisponibles(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM livres WHERE quantite > 0 ORDER BY titre ASC");

    return $stmt->fetchAll();
}

/**
 * Pagination : 10 résultats par page par défaut (cahier des charges).
 * Retourne un tableau avec les données ET les métadonnées de pagination.
 */
function paginerLivres(PDO $pdo, int $page = 1, int $parPage = 10): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $parPage;

    $stmt = $pdo->prepare("SELECT * FROM livres ORDER BY titre ASC LIMIT :limite OFFSET :offset");
    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $total = (int) $pdo->query("SELECT COUNT(*) FROM livres")->fetchColumn();

    return [
        'donnees'     => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'par_page'    => $parPage,
        'total_pages' => (int) ceil($total / max(1, $parPage)),
    ];
}
