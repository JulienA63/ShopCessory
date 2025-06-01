<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'shopcessory'); // Assure-toi que c'est le nom correct de ta BDD
define('DB_USER', 'root');          // Ton nom d'utilisateur BDD
define('DB_PASS', '');              // Ton mot de passe BDD
define('DB_CHARSET', 'utf8mb4');

function getPDOConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        // En mode développement, il est utile de voir l'erreur si la connexion échoue.
        // Si tu as une page blanche et suspectes la BDD, décommente la ligne suivante :
        // die("Erreur de connexion DB: " . $e->getMessage()); 
        return null;
    }
}
?>