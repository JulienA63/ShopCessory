

<?php


session_start(); // Démarrer ou reprendre une session existante (TRÈS IMPORTANT : tout en haut)

// public/index.php

// 1. Définir des constantes pour les chemins
// Chemin racine du projet (ex: C:/wamp64/www/Shopcessory)
define('ROOT_PATH', dirname(__DIR__));

// Chemin vers le dossier src (ex: C:/wamp64/www/Shopcessory/src)
define('APP_PATH', ROOT_PATH . '/src');

// Chemin de base pour les assets (CSS, JS, images) depuis la racine du site web
// ex: /Shopcessory/public/ (note le slash final)
$public_path_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($public_path_base === '' || $public_path_base === '.' || $public_path_base === '/' || $public_path_base === '\\') {
    define('PUBLIC_PATH_ASSET', '/');
} else {
    define('PUBLIC_PATH_ASSET', $public_path_base . '/');
}

// Chemin vers ce fichier index.php lui-même, tel qu'accessible depuis la racine du site web
// ex: /Shopcessory/public/index.php
define('INDEX_FILE_PATH', $_SERVER['SCRIPT_NAME']);

// Chemins pour l'upload des images de produits
// Chemin ABSOLU sur le serveur vers le dossier d'upload des images de produits
define('PRODUCT_IMAGE_UPLOAD_DIR', ROOT_PATH . '/public/uploads/products/');
// Chemin RELATIF DEPUIS LA RACINE DU SITE WEB pour accéder aux images de produits via URL
define('PRODUCT_IMAGE_BASE_URL', PUBLIC_PATH_ASSET . 'uploads/products/');


// Charger la configuration de la base de données
require_once ROOT_PATH . '/config/database.php';

// 2. Charger les fichiers de la bibliothèque (lib)
require_once APP_PATH . '/lib/autoloader.php';
registerAppAutoloader(); // Activer l'autoloading

require_once APP_PATH . '/lib/auth.php'; // Fonctions d'authentification

require_once APP_PATH . '/lib/router.php'; // Notre routeur

// 3. Traiter la requête via le routeur
// Récupérer l'URL demandée (la partie après index.php?url=)
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

handleRoute($url); // Le routeur s'occupe d'appeler le bon contrôleur

?>