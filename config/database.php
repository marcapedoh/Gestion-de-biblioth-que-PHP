<?php

require_once "config.php";

try {
    $pdo = new PDO(
        "mysql:host=" . HOST . ";dbname=" . DB . ";charset=utf8",
        USER,
        PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    die("Erreur de connexion à la base de données : " . $e->getMessage());

}