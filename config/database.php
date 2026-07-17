<?php

/**
 * Configuration de la connexion à la base de données.
 * Les valeurs viennent des variables d'environnement (.env, jamais commité).
 * Les valeurs par défaut ci-dessous ne servent qu'en local (XAMPP).
 *
 * Sur Render : DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
 * doivent être définies dans les variables d'environnement du service.
 */

return [
    'driver'   => getenv('DB_DRIVER') ?: 'pgsql',
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '5432',
    'database' => getenv('DB_DATABASE') ?: 'bibliotheque',
    'username' => getenv('DB_USERNAME') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset'  => 'utf8', // ignoré par le DSN pgsql, gardé pour compat MySQL
];
