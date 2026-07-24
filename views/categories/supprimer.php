<?php
/**
 * views/categories/supprimer.php
 * Traite la suppression d'une catégorie puis redirige vers la liste.
 * Les livres associés ne sont pas supprimés (categorie_id passe à NULL).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Categorie.php';
require_once __DIR__ . '/../../models/Journal.php';

protegerPage();

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $categorie = trouverCategorieParId($id);
    supprimerCategorie($id);

    if ($categorie) {
        enregistrerAction('Suppression', 'Catégories', 'Suppression de la catégorie "' . $categorie['nom'] . '"');
    }

    header('Location: liste.php?succes=suppression');
    exit;
}

header('Location: liste.php');
exit;
