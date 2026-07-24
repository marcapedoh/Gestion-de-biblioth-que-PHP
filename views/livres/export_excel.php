<?php
/**
 * views/livres/export_excel.php
 * Bonus : Export de la liste des livres au format Excel (.xls).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Livre.php';
require_once __DIR__ . '/../../includes/ExportHelper.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$tri       = $_GET['tri'] ?? 'titre';
$ordre     = $_GET['ordre'] ?? 'ASC';

$livres = listerLivresPourExport($recherche, $tri, $ordre);

$entetes = ['Titre', 'Auteur', 'Catégorie', 'ISBN', 'Année', 'Quantité disponible'];
$lignes = [];

foreach ($livres as $livre) {
    $lignes[] = [
        $livre['titre'],
        $livre['auteur'],
        $livre['nom_categorie'] ?? 'Sans catégorie',
        $livre['isbn'],
        $livre['annee'],
        $livre['quantite'],
    ];
}

exporterVersExcel('livres_bibliotheque', $entetes, $lignes);
