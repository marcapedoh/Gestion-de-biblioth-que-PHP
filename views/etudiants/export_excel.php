<?php
/**
 * views/etudiants/export_excel.php
 * Bonus : Export de la liste des étudiants au format Excel (.xls).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Etudiant.php';
require_once __DIR__ . '/../../includes/ExportHelper.php';

protegerPage();

$recherche = trim($_GET['recherche'] ?? '');
$tri       = $_GET['tri'] ?? 'nom';
$ordre     = $_GET['ordre'] ?? 'ASC';

$etudiants = listerEtudiantsPourExport($recherche, $tri, $ordre);

$entetes = ['Nom', 'Prénom', 'Email', 'Téléphone', 'Filière'];
$lignes = [];

foreach ($etudiants as $etudiant) {
    $lignes[] = [
        $etudiant['nom'],
        $etudiant['prenom'],
        $etudiant['email'],
        $etudiant['telephone'],
        $etudiant['filiere'],
    ];
}

exporterVersExcel('etudiants_bibliotheque', $entetes, $lignes);
