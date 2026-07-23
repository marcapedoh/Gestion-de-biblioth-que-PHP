<?php
/**
 * models/Emprunt.php
 * Fonctions d'accès aux données pour la table `emprunts`.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Enregistre un emprunt.
 * Exercice complémentaire 5 : refuse l'emprunt si la quantité disponible
 * du livre est égale à 0.
 *
 * @return true|string true si succès, sinon un message d'erreur
 */
function enregistrerEmprunt(int $idLivre, int $idEtudiant, string $dateEmprunt, string $dateRetourPrevue)
{
    $pdo = getPDO();

    $livre = trouverLivreParId($idLivre);

    if (!$livre) {
        return "Livre introuvable.";
    }

    if ((int) $livre['quantite'] <= 0) {
        return "Ce livre n'est plus disponible (quantité épuisée).";
    }

    $pdo->beginTransaction();
    try {
        $sql = "INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, statut)
                VALUES (?, ?, ?, ?, 'En cours')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idLivre, $idEtudiant, $dateEmprunt, $dateRetourPrevue]);

        // Décrémente la quantité disponible du livre
        $stmtMaj = $pdo->prepare("UPDATE livres SET quantite = quantite - 1 WHERE id = ? AND quantite > 0");
        $stmtMaj->execute([$idLivre]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return "Erreur lors de l'enregistrement de l'emprunt : " . $e->getMessage();
    }
}

/**
 * Enregistre le retour d'un livre emprunté.
 */
function retournerLivre(int $idEmprunt): bool
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM emprunts WHERE id = ?");
    $stmt->execute([$idEmprunt]);
    $emprunt = $stmt->fetch();

    if (!$emprunt || $emprunt['statut'] === 'Retourné') {
        return false;
    }

    $pdo->beginTransaction();
    try {
        $sqlMaj = "UPDATE emprunts SET date_retour = CURDATE(), statut = 'Retourné' WHERE id = ?";
        $stmtMaj = $pdo->prepare($sqlMaj);
        $stmtMaj->execute([$idEmprunt]);

        $stmtLivre = $pdo->prepare("UPDATE livres SET quantite = quantite + 1 WHERE id = ?");
        $stmtLivre->execute([$emprunt['id_livre']]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Supprime un emprunt (suppression pure, sans toucher au stock).
 */
function supprimerEmprunt(int $id): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM emprunts WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Retourne un emprunt par son id (avec titre du livre et nom de l'étudiant).
 */
function trouverEmpruntParId(int $id)
{
    $pdo = getPDO();
    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
            FROM emprunts e
            JOIN livres l ON e.id_livre = l.id
            JOIN etudiants et ON e.id_etudiant = et.id
            WHERE e.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Colonnes autorisées pour le tri (Bonus : tri des listes).
 */
function colonnesTriEmpruntsAutorisees(): array
{
    return [
        'titre'   => 'l.titre',
        'date'    => 'e.date_emprunt',
        'retour'  => 'e.date_retour_prevue',
    ];
}

/**
 * Liste les emprunts avec recherche (sur le titre du livre ou le nom/prénom
 * de l'étudiant), tri (Bonus) et pagination.
 */
function listerEmprunts(string $recherche, int $page, int $parPage, string $tri = 'date', string $ordre = 'DESC'): array
{
    $pdo = getPDO();
    $offset = ($page - 1) * $parPage;

    $colonnes = colonnesTriEmpruntsAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['date'];
    $ordre = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';

    $base = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
             FROM emprunts e
             JOIN livres l ON e.id_livre = l.id
             JOIN etudiants et ON e.id_etudiant = et.id";

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = $base . " WHERE l.titre LIKE ? OR et.nom LIKE ? OR et.prenom LIKE ?
                ORDER BY $colonneTri $ordre LIMIT ? OFFSET ?";
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
 * Comme listerEmprunts() mais sans pagination : utilisé pour l'export PDF/Excel.
 */
function listerEmpruntsPourExport(string $recherche, string $tri = 'date', string $ordre = 'DESC'): array
{
    $pdo = getPDO();

    $colonnes = colonnesTriEmpruntsAutorisees();
    $colonneTri = $colonnes[$tri] ?? $colonnes['date'];
    $ordre = strtoupper($ordre) === 'ASC' ? 'ASC' : 'DESC';

    $base = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
             FROM emprunts e
             JOIN livres l ON e.id_livre = l.id
             JOIN etudiants et ON e.id_etudiant = et.id";

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = $base . " WHERE l.titre LIKE ? OR et.nom LIKE ? OR et.prenom LIKE ? ORDER BY $colonneTri $ordre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif]);
    } else {
        $stmt = $pdo->query($base . " ORDER BY $colonneTri $ordre");
    }

    return $stmt->fetchAll();
}

/**
 * Compte le nombre total d'emprunts correspondant à la recherche.
 */
function compterEmprunts(string $recherche): int
{
    $pdo = getPDO();

    $base = "SELECT COUNT(*) FROM emprunts e
             JOIN livres l ON e.id_livre = l.id
             JOIN etudiants et ON e.id_etudiant = et.id";

    if ($recherche !== '') {
        $motif = '%' . $recherche . '%';
        $sql = $base . " WHERE l.titre LIKE ? OR et.nom LIKE ? OR et.prenom LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$motif, $motif, $motif]);
    } else {
        $stmt = $pdo->query($base);
    }

    return (int) $stmt->fetchColumn();
}

/**
 * Exercice complémentaire 3 : liste des emprunts en retard
 * (en cours, avec date de retour prévue dépassée).
 */
function listerEmpruntsEnRetard(): array
{
    $pdo = getPDO();
    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
            FROM emprunts e
            JOIN livres l ON e.id_livre = l.id
            JOIN etudiants et ON e.id_etudiant = et.id
            WHERE e.statut = 'En cours' AND e.date_retour_prevue < CURDATE()
            ORDER BY e.date_retour_prevue ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Exercice complémentaire 4 : nombre total de livres actuellement empruntés
 * (emprunts avec statut "En cours").
 */
function compterLivresEmpruntesEnCours(): int
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours'");
    return (int) $stmt->fetchColumn();
}

/**
 * Statistiques bonus pour le tableau de bord.
 */
function compterTousLesEmprunts(): int
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) FROM emprunts");
    return (int) $stmt->fetchColumn();
}

/**
 * Bonus (graphique tableau de bord) : répartition des emprunts par statut
 * réel (En cours à temps / En retard / Retourné).
 */
function statistiquesEmpruntsParStatut(): array
{
    $pdo = getPDO();

    $enCours = (int) $pdo->query(
        "SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours' AND date_retour_prevue >= CURDATE()"
    )->fetchColumn();

    $enRetard = (int) $pdo->query(
        "SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours' AND date_retour_prevue < CURDATE()"
    )->fetchColumn();

    $retournes = (int) $pdo->query(
        "SELECT COUNT(*) FROM emprunts WHERE statut = 'Retourné'"
    )->fetchColumn();

    return [
        'En cours'   => $enCours,
        'En retard'  => $enRetard,
        'Retourné'   => $retournes,
    ];
}

/**
 * Bonus (graphique tableau de bord) : nombre d'emprunts enregistrés
 * par mois sur les 6 derniers mois.
 */
function statistiquesEmpruntsParMois(): array
{
    $pdo = getPDO();
    $sql = "SELECT DATE_FORMAT(date_emprunt, '%Y-%m') AS mois, COUNT(*) AS total
            FROM emprunts
            WHERE date_emprunt >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY mois
            ORDER BY mois ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
