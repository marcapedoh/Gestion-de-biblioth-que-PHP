<?php

/**
 * Fichier de connexion à la base de données.
 *
 * Ce script initialise une connexion PDO sécurisée vers la base de données
 * en utilisant les identifiants définis dans le fichier de configuration.
 */

// Inclusion du fichier de configuration contenant les constantes
// de connexion (HOST, DB, USER, PASSWORD).
require_once "config.php";

try {

    /**
     * Création d'une nouvelle instance PDO pour se connecter à la base
     * de données MySQL avec le jeu de caractères UTF-8.
     *
     * Options PDO utilisées :
     * - PDO::ATTR_ERRMODE            : active le mode exception pour la gestion des erreurs.
     * - PDO::ATTR_DEFAULT_FETCH_MODE : définit le mode de récupération par défaut (tableau associatif).
     * - PDO::ATTR_EMULATE_PREPARES   : désactive l'émulation des requêtes préparées
     *                                  pour utiliser les vraies requêtes préparées côté serveur
     *                                  (meilleure sécurité contre les injections SQL).
     */
    $pdo = new PDO(
        "mysql:host=" . HOST . ";dbname=" . DB . ";charset=utf8",
        USER,
        PASSWORD,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {

    /**
     * En cas d'échec de connexion, on interrompt l'exécution du script
     * et on affiche un message d'erreur explicite.
     *
     * Remarque sécurité :
     * En environnement de production, il est déconseillé d'afficher
     * directement $e->getMessage() à l'utilisateur final (risque de fuite
     * d'informations sensibles sur la configuration serveur).
     * Il est préférable de logger l'erreur et d'afficher un message générique.
     */
    die("Erreur de connexion à la base de données : " . $e->getMessage());

}