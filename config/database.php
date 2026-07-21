<?php

/**
 * config/database.php
 *
 * Connexion PDO à la base de données, en PHP procédural (pas de classe,
 * conformément à la consigne du groupe).
 *
 * getConnexionPDO() renvoie toujours LA MÊME connexion grâce à une
 * variable "static" à l'intérieur de la fonction : ça évite d'ouvrir
 * plusieurs connexions inutiles, sans avoir besoin d'un singleton en POO.
 *
 * Les identifiants viennent des variables d'environnement (.env, jamais
 * commité). Voir .env.example à la racine du projet.
 */

function getConnexionPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $driver   = getenv('DB_DRIVER')   ?: 'pgsql';
        $host     = getenv('DB_HOST')     ?: '127.0.0.1';
        $port     = getenv('DB_PORT')     ?: '5432';
        $database = getenv('DB_DATABASE') ?: 'bibliotheque';
        $username = getenv('DB_USERNAME') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: '';

        // Le format du DSN diffère entre PostgreSQL et MySQL
        $dsn = ($driver === 'pgsql')
            ? "pgsql:host={$host};port={$port};dbname={$database}"
            : "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
            ]);
        } catch (PDOException $e) {
            // On ne révèle jamais les identifiants dans le message d'erreur
            die('Connexion à la base de données impossible. Vérifiez le fichier .env.');
        }
    }

    return $pdo;
}
