<?php
/**
 * views/emprunts/supprimer.php
 * Supprime un enregistrement d'emprunt puis redirige vers la liste.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $emprunt = trouverEmpruntParId($id);

    supprimerEmprunt($id);

    if ($emprunt) {
        enregistrerAction('Suppression', 'Emprunts', 'Suppression de l\'emprunt du livre "' . $emprunt['titre_livre'] . '" (' . $emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant'] . ')');
    }

    header('Location: liste.php?succes=suppression');
    exit;
}

header('Location: liste.php');
exit;
