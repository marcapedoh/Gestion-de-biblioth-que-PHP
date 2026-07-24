<?php
/**
 * controllers/AuthController.php
 * Gère la connexion, la déconnexion et la protection des pages.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Journal.php';

/**
 * Tente de connecter un utilisateur.
 * Vérifie le mot de passe avec password_verify() et crée la session
 * en cas de succès.
 *
 * @return true|string true si connexion réussie, sinon message d'erreur
 */
function tenterConnexion(string $email, string $motDePasse)
{
    $email = trim($email);

    if ($email === '' || $motDePasse === '') {
        return "Veuillez renseigner l'email et le mot de passe.";
    }

    $utilisateur = trouverUtilisateurParEmail($email);

    if (!$utilisateur || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        return "Email ou mot de passe incorrect.";
    }

    // Régénère l'identifiant de session pour éviter la fixation de session
    session_regenerate_id(true);

    $_SESSION['utilisateur_id']   = $utilisateur['id'];
    $_SESSION['utilisateur_nom']  = $utilisateur['nom'];
    $_SESSION['utilisateur_mail'] = $utilisateur['email'];

    enregistrerAction('Connexion', 'Authentification', 'Connexion réussie à l\'application');

    return true;
}

/**
 * Détruit la session et déconnecte l'utilisateur.
 */
function deconnecterUtilisateur(): void
{
    if (isset($_SESSION['utilisateur_id'])) {
        enregistrerAction('Déconnexion', 'Authentification', 'Déconnexion de l\'application');
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Indique si un utilisateur est actuellement connecté.
 */
function estConnecte(): bool
{
    return isset($_SESSION['utilisateur_id']);
}

/**
 * Protège une page : redirige vers la page de connexion si l'utilisateur
 * n'est pas connecté. À appeler en tout début de chaque page protégée.
 */
function protegerPage(): void
{
    if (!estConnecte()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}
