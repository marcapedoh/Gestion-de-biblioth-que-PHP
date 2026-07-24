<?php
/**
 * models/Livre.php
 * Fonctions d'accès aux données pour la table `livres`.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Colonnes autorisées pour le tri (Bonus : tri des listes).
 * On ne fait jamais confiance à une valeur $_GET pour construire du SQL :
 * on la valide toujours contre cette liste blanche.
 */
function colonnesTriLivresAutorisees(): array
{
    return [
        'titre'      => 'l.titre',
        'auteur'     => 'l.auteur',
        'date'       => 'l.annee',
        'quantite'   => 'l.quantite',
    ];
}

/**
 * Ajoute un livre.
 */
function ajouterLivre(string $titre, string $auteur, string $isbn, int $annee, int $quantite, ?int $categorieId, ?string $couverture): bool
{
    $pdo = getPDO();
    $sql = "INSERT INTO livres (titre, auteur, isbn, annee, quantite, categorie_id, couverture)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titre, $auteur, $isbn, $annee, $quantite, $categorieId, $couverture]);
}

/**
 * Modifie un livre existant.
 * $couverture peut être null si l'utilisateur ne change pas l'image
 * (le contrôleur transmet alors la valeur déjà en base).
 */
function modifierLivre(int $id, string $titre, string $auteur, string $isbn, int $annee, int $quantite, ?int $categorieId, ?string $couverture): bool
{
    $pdo = getPDO();
    $sql = "UPDATE livres
            SET titre = ?, auteur = ?, isbn = ?, annee = ?, quantite = ?, categorie_id = ?, couverture = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titre, $auteur, $isbn, $annee, $quantite, $categorieId, $couverture, $id]);
}

/**
 * Supprime un livre.
 */
function supprimerLivre(int $id): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Retourne un livre par son id, avec le nom de sa catégorie.
 */
function trouverLivreParId(int $id)
{
    $pdo = getPDO();
    $sql = "SELECT l.*, c.nom AS nom_categorie
            FROM livres l
            LEFT JOIN categories c ON l.categorie_id = c.id
            WHERE l.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Liste les livres avec recherche multicritère (titre, auteur, ISBN),
 * tri (Bonus) et pagination.
 *
 * @param string $tri   Clé de tri : titre | auteur | date | quantite
 * @param string $ordre Sens du tri : ASC | DESC
 */
function listerLivres(string $recherche, int $page, int $parPage, string $tri = 'titre', string $ordre = 'ASC'): array
{
    $pdo = getPDO();
    $offset = ($page - 1) * $parPage;

    $colonnes = colonnesTriLivresAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['titre'];
    $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

    $base = "SELECT l.*, c.nom AS nom_categorie
             FROM livres l
             LEFT JOIN categories c ON l.categorie_id = c.id";

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = $base . " WHERE l.titre LIKE ? OR l.auteur LIKE ? OR l.isbn LIKE ?
                ORDER BY $colonneTri $ordre
                LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $motif, PDO::PARAM_STR);
        $stmt->bindValue(2, $motif, PDO::PARAM_STR);
        $stmt->bindValue(3, $motif, PDO::PARAM_STR);
        $stmt->bindValue(4, $parPage, PDO::PARAM_INT);
        $stmt->bindValue(5, $offset, PDO::PARAM_INT);
    } else {
        $sql = $base . " ORDER BY $colonneTri $ordre LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $parPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Comme listerLivres() mais sans pagination : utilisé pour l'export
 * PDF/Excel afin d'exporter l'intégralité du résultat de la recherche.
 */
function listerLivresPourExport(string $recherche, string $tri = 'titre', string $ordre = 'ASC'): array
{
    $pdo = getPDO();

    $colonnes = colonnesTriLivresAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['titre'];
    $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

    $base = "SELECT l.*, c.nom AS nom_categorie
             FROM livres l
             LEFT JOIN categories c ON l.categorie_id = c.id";

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = $base . " WHERE l.titre LIKE ? OR l.auteur LIKE ? OR l.isbn LIKE ? ORDER BY $colonneTri $ordre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif]);
    } else {
        $stmt = $pdo->query($base . " ORDER BY $colonneTri $ordre");
    }

    return $stmt->fetchAll();
}

/**
 * Compte le nombre total de livres correspondant à la recherche
 * (nécessaire pour calculer le nombre de pages).
 */
function compterLivres(string $recherche): int
{
    $pdo = getPDO();

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = "SELECT COUNT(*) FROM livres WHERE titre LIKE ? OR auteur LIKE ? OR isbn LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif]);
    } else {
        $sql = "SELECT COUNT(*) FROM livres";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    return (int) $stmt->fetchColumn();
}

/**
 * Exercice complémentaire 2 : affiche uniquement les livres disponibles
 * (quantité > 0).
 */
function listerLivresDisponibles(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM livres WHERE quantite > 0 ORDER BY titre ASC");
    return $stmt->fetchAll();
}

/**
 * Bonus (statistiques tableau de bord) : nombre de livres par catégorie.
 */
function statistiquesLivresParCategorie(): array
{
    $pdo = getPDO();
    $sql = "SELECT COALESCE(c.nom, 'Sans catégorie') AS categorie, COUNT(l.id) AS total
            FROM livres l
            LEFT JOIN categories c ON l.categorie_id = c.id
            GROUP BY c.id
            ORDER BY total DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
