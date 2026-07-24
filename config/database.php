<?php
/**
 * config.php
 * Configuration générale de l'application (Mode intelligent Local / Production).
 */

// Démarre la session sur toutes les pages qui incluent ce fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détection automatique de l'environnement (Local vs Hébergeur en ligne)
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    // --- CONFIGURATION LOCALE (WampServer) ---
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'bibliotheque');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', '/bibliotheque/');
    
    // Erreurs visibles en local pour le debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // --- CONFIGURATION EN LIGNE (InfinityFree) ---
    define('DB_HOST', 'sql202.infinityfree.com');
    define('DB_NAME', 'if0_42484346_depot');
    define('DB_USER', 'if0_42484346');
    define('DB_PASS', 'xmFTQrYp2r2ye');
    define('BASE_URL', '/');
    
    // Erreurs masquées en production pour la sécurité
    error_reporting(0);
    ini_set('display_errors', 0);
}

define('DB_CHARSET', 'utf8mb4');

// Pagination : nombre de résultats affichés par page
define('RESULTATS_PAR_PAGE', 10);

// Bonus : upload des couvertures de livres
// Chemin absolu sur le disque (pour move_uploaded_file) et URL publique (pour <img src="">)
define('CHEMIN_COUVERTURES', __DIR__ . '/../assets/uploads/couvertures/');
define('URL_COUVERTURES', BASE_URL . 'assets/uploads/couvertures/');
define('COUVERTURE_TAILLE_MAX', 2 * 1024 * 1024); // 2 Mo
define('COUVERTURE_TYPES_AUTORISES', ['image/jpeg', 'image/png', 'image/webp']);