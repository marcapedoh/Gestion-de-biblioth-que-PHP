<?php
/**
 * views/emprunts/retourner.php
 * Enregistre le retour d'un livre emprunté (met à jour le statut,
 * la date de retour et le stock du livre).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $emprunt = trouverEmpruntParId($id);

    if (retournerLivre($id) && $emprunt) {
        enregistrerAction('Retour', 'Emprunts', 'Retour du livre "' . $emprunt['titre_livre'] . '" par ' . $emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant']);
    }

    header('Location: liste.php?succes=retour');
    exit;
}

header('Location: liste.php');
exit;
