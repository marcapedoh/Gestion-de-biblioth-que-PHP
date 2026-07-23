<?php
/**
 * logout.php
 * Détruit la session en cours et redirige vers la page de connexion.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Journal.php';

if (estConnecte()) {
    enregistrerAction('Déconnexion', 'Authentification', 'Déconnexion de l\'application');
}

deconnecterUtilisateur();

header('Location: ' . BASE_URL . 'login.php');
exit;
