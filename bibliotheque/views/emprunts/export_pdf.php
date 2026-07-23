<?php
/**
 * views/emprunts/export_pdf.php
 * Bonus : Export de la liste des emprunts au format PDF (page imprimable).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Emprunt.php';
require_once __DIR__ . '/../../includes/ExportHelper.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$tri       = $_GET['tri'] ?? 'date';
$ordre     = $_GET['ordre'] ?? 'DESC';

$emprunts = listerEmpruntsPourExport($recherche, $tri, $ordre);

$entetes = ['Livre', 'Étudiant', 'Date emprunt', 'Retour prévu', 'Retour effectif', 'Statut'];
$lignes = [];

foreach ($emprunts as $emprunt) {
    $lignes[] = [
        $emprunt['titre_livre'],
        $emprunt['prenom_etudiant'] . ' ' . $emprunt['nom_etudiant'],
        $emprunt['date_emprunt'],
        $emprunt['date_retour_prevue'],
        $emprunt['date_retour'] ?? '-',
        $emprunt['statut'],
    ];
}

exporterVersPDF('Liste des emprunts - Bibliothèque Universitaire', $entetes, $lignes);
