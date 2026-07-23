<?php
/**
 * views/livres/supprimer.php
 * Traite la suppression d'un livre puis redirige vers la liste.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/LivreController.php';
require_once __DIR__ . '/../../models/Livre.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $livre = trouverLivreParId($id);

    try {
        supprimerLivre($id);

        if ($livre) {
            supprimerFichierCouverture($livre['couverture']);
            enregistrerAction('Suppression', 'Livres', 'Suppression du livre "' . $livre['titre'] . '"');
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
