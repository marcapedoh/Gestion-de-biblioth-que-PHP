<?php

/**
 * models/Utilisateur.php
 *
 * Opérations sur la table "utilisateurs" (comptes administrateurs),
 * en fonctions PHP simples (pas de classe). Sert de support à
 * AuthController.php (connexion / déconnexion).
 */

/**
 * Recherche un utilisateur par son email. Retourne null si introuvable.
 */
function trouverUtilisateurParEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch();

    return $utilisateur ?: null;
}

/**
 * Vérifie les identifiants de connexion (email + mot de passe en clair
 * saisi dans le formulaire). Retourne les données de l'utilisateur si
 * c'est correct, sinon false. Ne renvoie jamais le hash du mot de passe.
 */
function verifierIdentifiants(PDO $pdo, string $email, string $motDePasseSaisi): array|false
{
    $utilisateur = trouverUtilisateurParEmail($pdo, $email);

    if ($utilisateur === null) {
        return false;
    }

    if (!password_verify($motDePasseSaisi, $utilisateur['mot_de_passe'])) {
        return false;
    }

    unset($utilisateur['mot_de_passe']); // on ne garde jamais le hash en session

    return $utilisateur;
}

/**
 * Crée un nouvel utilisateur (admin) avec mot de passe hashé.
 * Retourne l'id créé.
 */
function creerUtilisateur(PDO $pdo, string $nom, string $email, string $motDePasseEnClair): int
{
    $hash = password_hash($motDePasseEnClair, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (:nom, :email, :mot_de_passe)"
    );
    $stmt->execute(['nom' => $nom, 'email' => $email, 'mot_de_passe' => $hash]);

    return (int) $pdo->lastInsertId('utilisateurs_id_seq');
}
