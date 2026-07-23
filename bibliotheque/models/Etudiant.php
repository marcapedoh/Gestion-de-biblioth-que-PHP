<?php
/**
 * models/Etudiant.php
 * Fonctions d'accès aux données pour la table `etudiants`.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Ajoute un étudiant.
 */
function ajouterEtudiant(string $nom, string $prenom, string $email, string $telephone, string $filiere): bool
{
    $pdo = getPDO();
    $sql = "INSERT INTO etudiants (nom, prenom, email, telephone, filiere) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $prenom, $email, $telephone, $filiere]);
}

/**
 * Modifie un étudiant existant.
 */
function modifierEtudiant(int $id, string $nom, string $prenom, string $email, string $telephone, string $filiere): bool
{
    $pdo = getPDO();
    $sql = "UPDATE etudiants
            SET nom = ?, prenom = ?, email = ?, telephone = ?, filiere = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $prenom, $email, $telephone, $filiere, $id]);
}

/**
 * Supprime un étudiant.
 */
function supprimerEtudiant(int $id): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Retourne un étudiant par son id.
 */
function trouverEtudiantParId(int $id)
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Liste tous les étudiants pour un select (formulaire d'emprunt par exemple).
 */
function listerTousLesEtudiants(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY nom ASC");
    return $stmt->fetchAll();
}

/**
 * Colonnes autorisées pour le tri (Bonus : tri des listes). Liste blanche
 * pour éviter toute injection SQL via le paramètre de tri.
 */
function colonnesTriEtudiantsAutorisees(): array
{
    return [
        'nom'     => 'nom',
        'prenom'  => 'prenom',
        'filiere' => 'filiere',
    ];
}

/**
 * Exercice complémentaire 1 : recherche multicritère sur les étudiants
 * (nom, prénom, email, filière), tri (Bonus) et pagination.
 */
function listerEtudiants(string $recherche, int $page, int $parPage, string $tri = 'nom', string $ordre = 'ASC'): array
{
    $pdo = getPDO();
    $offset = ($page - 1) * $parPage;

    $colonnes = colonnesTriEtudiantsAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['nom'];
    $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = "SELECT * FROM etudiants
                WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR filiere LIKE ?
                ORDER BY $colonneTri $ordre
                LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $motif, PDO::PARAM_STR);
        $stmt->bindValue(2, $motif, PDO::PARAM_STR);
        $stmt->bindValue(3, $motif, PDO::PARAM_STR);
        $stmt->bindValue(4, $motif, PDO::PARAM_STR);
        $stmt->bindValue(5, $parPage, PDO::PARAM_INT);
        $stmt->bindValue(6, $offset, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM etudiants ORDER BY $colonneTri $ordre LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $parPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Comme listerEtudiants() mais sans pagination : utilisé pour l'export PDF/Excel.
 */
function listerEtudiantsPourExport(string $recherche, string $tri = 'nom', string $ordre = 'ASC'): array
{
    $pdo = getPDO();

    $colonnes = colonnesTriEtudiantsAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['nom'];
    $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = "SELECT * FROM etudiants
                WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR filiere LIKE ?
                ORDER BY $colonneTri $ordre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif, $motif]);
    } else {
        $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY $colonneTri $ordre");
    }

    return $stmt->fetchAll();
}

/**
 * Compte le nombre total d'étudiants correspondant à la recherche.
 */
function compterEtudiants(string $recherche): int
{
    $pdo = getPDO();

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = "SELECT COUNT(*) FROM etudiants
                WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR filiere LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif, $motif]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM etudiants");
    }

    return (int) $stmt->fetchColumn();
}
