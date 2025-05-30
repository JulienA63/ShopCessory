<?php
// config/database.php

// Remplace ces valeurs par tes propres informations de connexion à la base de données !
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopcessory'); // Le nom de ta base de données
define('DB_USER', 'root');          // Ton nom d'utilisateur pour la base de données
define('DB_PASS', '');              // Ton mot de passe
define('DB_CHARSET', 'utf8mb4');

/**
 * Crée et retourne une instance de PDO pour la connexion à la base de données.
 * @return PDO|null L'instance de PDO en cas de succès, null en cas d'échec.
 */
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
        // En développement, pour voir l'erreur plus facilement :
        // echo "Erreur de connexion DB: " . $e->getMessage(); // Décommente pour déboguer si besoin
        return null;
    }
}
?>