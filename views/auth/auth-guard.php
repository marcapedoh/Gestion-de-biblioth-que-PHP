<?php

/**
 * Fichier de gestion de l'authentification (Auth Guard).
 *
 * Contient toutes les fonctions procédurales nécessaires pour :
 * - démarrer/vérifier la session utilisateur
 * - protéger l'accès aux pages (guard)
 * - gérer les messages flash (notifications Notyf)
 * - vérifier la correspondance mot de passe clair / mot de passe haché
 *
 * @package auth
 */

/**
 * Démarre la session si elle n'est pas déjà active.
 * À appeler en tout début de chaque page (avant tout output HTML).
 *
 * @return void
 */
function demarrerSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Vérifie si un utilisateur est actuellement connecté.
 *
 * On considère l'utilisateur connecté si la clé 'user_id'
 * existe dans la session (définie lors du login).
 *
 * @return bool true si connecté, false sinon
 */
function estConnecte(): bool
{
    demarrerSession();
    return isset($_SESSION['user_id']);
}

/**
 * Définit un message flash à afficher une seule fois
 * (via une notification Notyf) sur la prochaine page chargée.
 *
 * @param string $type    Type du message : 'success', 'error', 'warning', 'info'
 * @param string $message Contenu du message à afficher
 * @return void
 */
function definirMessageFlash(string $type, string $message): void
{
    demarrerSession();
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Récupère et supprime le message flash de la session (usage unique),
 * puis génère le script JS Notyf correspondant.
 *
 * À appeler juste avant </body> dans le template global,
 * après avoir inclus le CDN de Notyf.
 *
 * @return void (affiche directement le script)
 */
function afficherMessageFlash(): void
{
    demarrerSession();

    if (empty($_SESSION['flash'])) {
        return;
    }

    $type    = $_SESSION['flash']['type'];
    $message = htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8');

    // On supprime le message pour qu'il ne s'affiche qu'une seule fois
    unset($_SESSION['flash']);

    // Sécurité : on n'autorise que des types Notyf connus
    $typesAutorises = ['success', 'error', 'warning', 'info'];
    if (!in_array($type, $typesAutorises, true)) {
        $type = 'info';
    }

    echo <<<HTML
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var notyf = new Notyf({ duration: 4000, position: { x: 'right', y: 'top' } });
            notyf.{$type}("{$message}");
        });
    </script>
    HTML;
}

/**
 * Garde d'accès (guard) : protège une page contre l'accès
 * d'un utilisateur non connecté.
 *
 * Si l'utilisateur n'est pas connecté :
 * - on enregistre un message flash d'erreur
 * - on le redirige vers login.php
 * - on stoppe l'exécution du script
 *
 * À appeler en tout début de chaque page protégée.
 *
 * @return void
 */
function verifierAuthentification(): void
{
    demarrerSession();

    if (!estConnecte()) {
        definirMessageFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
        header("Location: login.php");
        exit;
    }
}

/**
 * Vérifie qu'un mot de passe en clair correspond à un mot de passe haché.
 *
 * Utilise password_verify(), fonction native PHP sécurisée
 * (compatible avec les hachages générés par password_hash()).
 *
 * @param string $motDePasseClair Mot de passe saisi par l'utilisateur (en clair)
 * @param string $motDePasseHache Mot de passe haché stocké en base de données
 * @return bool true si les deux correspondent, false sinon
 */
function verifierMotDePasse(string $motDePasseClair, string $motDePasseHache): bool
{
    return password_verify($motDePasseClair, $motDePasseHache);
}