<?php
/**
 * models/Utilisateur.php
 * Fonctions d'accès aux données pour la table `utilisateurs`.
 * Approche procédurale : pas de classe, uniquement des fonctions.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Recherche un utilisateur par son email.
 * Retourne un tableau associatif ou false si non trouvé.
 */
function trouverUtilisateurParEmail(string $email)
{
    $pdo = getPDO();
    $sql = "SELECT * FROM utilisateurs WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Crée un nouvel utilisateur (administrateur) avec un mot de passe déjà hashé.
 * Utile pour créer des comptes admin en dehors du formulaire de connexion.
 */
function creerUtilisateur(string $nom, string $email, string $motDePasseHash): bool
{
    $pdo = getPDO();
    $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $email, $motDePasseHash]);
}
