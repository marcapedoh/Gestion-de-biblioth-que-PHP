<?php
/**
 * models/Journal.php
 * Bonus : Journalisation des actions (ajout, modification, suppression...).
 * Chaque contrôleur appelle enregistrerAction() après une opération réussie.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Enregistre une action dans le journal.
 *
 * @param string $action      Ex: 'Ajout', 'Modification', 'Suppression', 'Connexion', 'Emprunt', 'Retour'
 * @param string $module      Ex: 'Livres', 'Étudiants', 'Emprunts', 'Catégories', 'Authentification'
 * @param string $description Description lisible de l'action effectuée
 */
function enregistrerAction(string $action, string $module, string $description): bool
{
    $pdo = getPDO();

    $utilisateurId  = $_SESSION['utilisateur_id'] ?? null;
    $utilisateurNom = $_SESSION['utilisateur_nom'] ?? 'Système';

    $sql = "INSERT INTO journal (utilisateur_id, utilisateur_nom, action, module, description)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$utilisateurId, $utilisateurNom, $action, $module, $description]);
}

/**
 * Liste les entrées du journal (les plus récentes en premier), avec pagination.
 */
function listerJournal(int $page, int $parPage): array
{
    $pdo = getPDO();
    $offset = ($page - 1) * $parPage;

    $sql = "SELECT * FROM journal ORDER BY date_action DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $parPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Compte le nombre total d'entrées du journal.
 */
function compterJournal(): int
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal");
    return (int) $stmt->fetchColumn();
}
