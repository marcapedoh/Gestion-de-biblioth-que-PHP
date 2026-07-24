<?php
/**
 * config.php
 * Configuration générale de l'application.
 */

// Démarre la session sur toutes les pages qui incluent ce fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'bibliotheque');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Pagination : nombre de résultats affichés par page
define('RESULTATS_PAR_PAGE', 10);

// URL de base de l'application (à adapter selon votre configuration serveur).
// Exemple : si le projet est accessible via http://localhost/bibliotheque/,
// laissez '/bibliotheque/'. Utilisée pour que les liens et redirections
// fonctionnent depuis n'importe quel sous-dossier (views/livres, etc.).
define('BASE_URL', '/bibliotheque/');

// Bonus : upload des couvertures de livres
// Chemin absolu sur le disque (pour move_uploaded_file) et URL publique (pour <img src="">)
define('CHEMIN_COUVERTURES', __DIR__ . '/../assets/uploads/couvertures/');
define('URL_COUVERTURES', BASE_URL . 'assets/uploads/couvertures/');
define('COUVERTURE_TAILLE_MAX', 2 * 1024 * 1024); // 2 Mo
define('COUVERTURE_TYPES_AUTORISES', ['image/jpeg', 'image/png', 'image/webp']);

// Affichage des erreurs en développement (à mettre à 0 en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);