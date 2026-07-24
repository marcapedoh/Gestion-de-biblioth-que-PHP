<?php
/**
 * database.php
 * Fournit une connexion PDO unique et centralisée à la base de données.
 * Aucune autre partie du code ne doit créer de connexion PDO directement :
 * on appelle toujours getPDO().
 */

require_once __DIR__ . '/config.php';

/**
 * Retourne l'instance PDO unique (pattern singleton sans classe,
 * simplement grâce à une variable statique locale à la fonction).
 *
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // vraies requêtes préparées côté MySQL
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    return $pdo;
}