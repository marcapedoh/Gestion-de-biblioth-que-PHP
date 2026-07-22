<?php

declare(strict_types=1);

use Orm\Connection\Database;

require_once __DIR__ . '/support/validator.php';

/**
 * Authentification par session (connexion, déconnexion, protection des pages).
 *
 * NOTE ÉQUIPE : core/Auth/AuthMiddleware.php (Marc) n'existe pas encore
 * dans le core livré pour l'instant. En attendant, auth_exiger_connexion()
 * sert de garde-fou temporaire à appeler en tête de chaque fonction de
 * contrôleur qui doit être protégée — à remplacer par le vrai middleware
 * dès qu'il sera prêt (un seul endroit à changer).
 */

function auth_demarrer_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * @param array<string,mixed> $data Attendu : email, mot_de_passe
 * @return array{success:bool, errors:array<string,string>}
 */
function auth_connecter(array $data): array
{
    $errors = [];
    valider_requis($data, 'email', $errors);
    valider_requis($data, 'mot_de_passe', $errors, 'Le mot de passe');
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $email = trim((string) $data['email']);
    $motDePasse = (string) $data['mot_de_passe'];

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch();

    // Message volontairement identique que l'utilisateur existe ou non,
    // pour ne pas révéler quels emails sont enregistrés.
    if ($utilisateur === false || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        return ['success' => false, 'errors' => ['general' => 'Email ou mot de passe incorrect.']];
    }

    auth_demarrer_session();
    session_regenerate_id(true); // anti fixation de session

    $_SESSION['user_id'] = (int) $utilisateur['id'];
    $_SESSION['user_nom'] = $utilisateur['nom'];
    $_SESSION['user_email'] = $utilisateur['email'];

    return ['success' => true, 'errors' => []];
}

function auth_deconnecter(): void
{
    auth_demarrer_session();
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

function auth_est_connecte(): bool
{
    auth_demarrer_session();

    return isset($_SESSION['user_id']);
}

/** @return array{id:int,nom:string,email:string}|null */
function auth_utilisateur_connecte(): ?array
{
    auth_demarrer_session();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id'    => (int) $_SESSION['user_id'],
        'nom'   => (string) $_SESSION['user_nom'],
        'email' => (string) $_SESSION['user_email'],
    ];
}
