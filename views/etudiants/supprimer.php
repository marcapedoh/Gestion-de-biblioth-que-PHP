<?php
/**
 * views/etudiants/supprimer.php
 * Traite la suppression d'un étudiant puis redirige vers la liste.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Etudiant.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $etudiant = trouverEtudiantParId($id);

    try {
        supprimerEtudiant($id);

        if ($etudiant) {
            enregistrerAction('Suppression', 'Étudiants', 'Suppression de l\'étudiant ' . $etudiant['prenom'] . ' ' . $etudiant['nom']);
        }

        header('Location: liste.php?succes=suppression');
        exit;
    } catch (PDOException $e) {
        header('Location: liste.php?erreur=emprunts_lies');
        exit;
    }
}

header('Location: liste.php');
exit;
