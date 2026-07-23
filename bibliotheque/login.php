<?php
/**
 * login.php
 * Page de connexion : affiche le formulaire et traite sa soumission.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Journal.php';

// Si déjà connecté, on redirige directement vers l'accueil
if (estConnecte()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = $_POST['email'] ?? '';
    $motDePasse  = $_POST['mot_de_passe'] ?? '';

    $resultat = tenterConnexion($email, $motDePasse);

    if ($resultat === true) {
        enregistrerAction('Connexion', 'Authentification', 'Connexion réussie à l\'application');
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    $erreur = $resultat; // message d'erreur retourné par tenterConnexion()
}

require __DIR__ . '/views/auth/connexion.php';
