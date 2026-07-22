<?php

/**
 * Fichier de configuration de la base de données.
 *
 * Ce fichier centralise les constantes nécessaires à l'établissement
 * de la connexion PDO (voir fichier de connexion associé).
 *
 * Sécurité :
 * - Ce fichier contient des informations sensibles (identifiants de connexion).
 * - Il ne doit jamais être versionné tel quel dans un dépôt public (Git).
 * - En production, privilégier des variables d'environnement (.env)
 *   plutôt que des constantes en dur dans le code.
 *
 */

/**
 * Adresse ou nom d'hôte du serveur de base de données.
 * "localhost" indique que le serveur MySQL tourne sur la même machine.
 */
define("HOST", "localhost");

/**
 * Nom de la base de données à utiliser.
 */
define("DB", "bibliotheque");

/**
 * Nom d'utilisateur pour la connexion à la base de données.
 * "root" est utilisé ici en environnement de développement uniquement.
 */
define("USER", "root");

/**
 * Mot de passe associé à l'utilisateur de la base de données.
 * Vide ici (environnement local) — à définir obligatoirement
 * avec un mot de passe fort en production.
 */
define("PASSWORD", "");