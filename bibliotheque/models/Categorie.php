<?php
/**
 * models/Categorie.php
 * Fonctions d'accès aux données pour la table `categories`.
 * Bonus : Gestion des catégories de livres.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Ajoute une catégorie.
 */
function ajouterCategorie(string $nom, string $description): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
    return $stmt->execute([$nom, $description]);
}

/**
 * Modifie une catégorie existante.
 */
function modifierCategorie(int $id, string $nom, string $description): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ? WHERE id = ?");
    return $stmt->execute([$nom, $description, $id]);
}

/**
 * Supprime une catégorie. Les livres liés ne sont pas supprimés :
 * leur categorie_id passe à NULL (ON DELETE SET NULL, voir schema.sql).
 */
function supprimerCategorie(int $id): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Retourne une catégorie par son id.
 */
function trouverCategorieParId(int $id)
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Liste toutes les catégories, triées par nom (utile pour les <select>).
 */
function listerToutesLesCategories(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
    return $stmt->fetchAll();
}

/**
 * Liste les catégories avec le nombre de livres associés à chacune
 * (utilisé pour la page de liste et pour le graphique du tableau de bord).
 */
function listerCategoriesAvecNbLivres(): array
{
    $pdo = getPDO();
    $sql = "SELECT c.*, COUNT(l.id) AS nb_livres
            FROM categories c
            LEFT JOIN livres l ON l.categorie_id = c.id
            GROUP BY c.id
            ORDER BY c.nom ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Compte le nombre total de catégories.
 */
function compterCategories(): int
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    return (int) $stmt->fetchColumn();
}
