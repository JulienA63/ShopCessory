<?php
session_start(); // Démarrer ou reprendre une session existante

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/src');

$public_path_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($public_path_base === '' || $public_path_base === '.' || $public_path_base === '/' || $public_path_base === '\\') {
    define('PUBLIC_PATH_ASSET', '/');
} else {
    define('PUBLIC_PATH_ASSET', $public_path_base . '/');
}
define('INDEX_FILE_PATH', $_SERVER['SCRIPT_NAME']);

// Chemins pour l'upload des images de produits
define('PRODUCT_IMAGE_UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR);
define('PRODUCT_IMAGE_BASE_URL', PUBLIC_PATH_ASSET . 'uploads/products/');

// Chargement des fichiers de configuration et de la bibliothèque
require_once ROOT_PATH . '/config/database.php';
require_once APP_PATH . '/lib/autoloader.php';
registerAppAutoloader();
require_once APP_PATH . '/lib/auth.php';
require_once APP_PATH . '/lib/flash_messages.php';
require_once APP_PATH . '/lib/router.php';

// Traitement de la requête
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
handleRoute($url);
?>