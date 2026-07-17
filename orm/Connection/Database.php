<?php

namespace Orm\Connection;

use PDO;
use PDOException;

/**
 * Connexion PDO unique et centralisée vers la base de données (Render).
 *
 * Implémente le pattern Singleton : une seule instance de PDO est créée
 * et partagée dans toute l'application. C'est la règle de sécurité imposée
 * par le sujet ("Connexion PDO unique et centralisée") et par le README
 * d'équipe (Marc en QA vérifiera qu'aucun autre `new PDO()` n'existe
 * ailleurs dans le code).
 */
final class Database
{
    private static ?PDO $instance = null;

    // Empêche l'instanciation directe et le clonage (Singleton strict)
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';

            // Le format du DSN diffère entre PostgreSQL et MySQL (charset
            // ne se passe pas de la même façon), d'où ce petit branchement.
            $dsn = match ($config['driver']) {
                'pgsql' => sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $config['host'],
                    $config['port'],
                    $config['database']
                ),
                default => sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                ),
            };

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES  => false, // vraies requêtes préparées côté MySQL
                ]);
            } catch (PDOException $e) {
                // On ne renvoie jamais les identifiants de connexion dans le message d'erreur
                throw new PDOException(
                    'Connexion à la base de données impossible. Vérifiez config/database.php et le fichier .env.',
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }

    /**
     * Utile pour les tests unitaires de Brandon : permet de réinitialiser
     * le singleton entre deux tests si besoin.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
