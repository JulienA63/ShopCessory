<?php
// seed_products.php (version corrigée et améliorée)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Peuplement Produits (Scan Images)</title>";
echo "<style> body {font-family: sans-serif; padding: 20px;} ul {list-style-type: none; padding-left:0;} li {margin-bottom: 5px;} .success {color: green;} .error {color: red;} .warning {color: orange;} </style>";
echo "</head><body>";
echo "<h1>Résultat du peuplement de la table produits</h1>";

// Définition des constantes de chemin si elles ne sont pas déjà définies (utile si ce script est appelé en dehors d'index.php)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/src');
}
// On a besoin de PRODUCT_IMAGE_UPLOAD_DIR
if (!defined('PRODUCT_IMAGE_UPLOAD_DIR')) {
    // S'assurer que DIRECTORY_SEPARATOR est utilisé pour la compatibilité
    define('PRODUCT_IMAGE_UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR);
}

// Charger la configuration de la base de données
// Ce chemin doit être correct par rapport à l'emplacement de seed_products.php (qui est à la racine)
require_once ROOT_PATH . '/config/database.php';

$pdo = getPDOConnection();
if (!$pdo) {
    echo "<p class='error'>ERREUR : Impossible de se connecter à la base de données.</p></body></html>";
    exit;
}

// --- Configuration du Seeder ---
$numberOfProductsToCreate = 25; // Combien de produits tu veux créer

$sampleTitles = ["Montre Raffinée", "Bracelet Unique", "Collier Splendide", "Bague Majestueuse", "Accessoire Tendance", "Chronomètre de Collection", "Bijou Fait Main", "Boucles d'Oreilles Élégantes", "Pendentif Original"];
$sampleDescriptions = [
    "Un véritable bijou de technologie et d'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée.",
    "Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées.",
    "Léger, confortable et discret, cet article est un plaisir à porter. C'est aussi une excellente idée de cadeau.",
    "Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d'acquérir un objet d'exception.",
    "Alliant robustesse et finesse, cet accessoire est conçu pour durer. Un excellent investissement pour un style impeccable."
];
// --- Fin Configuration ---


// 1. Scanner le dossier public/uploads/products/ pour lister les images disponibles
$availableImageFiles = [];
$imageUploadDir = PRODUCT_IMAGE_UPLOAD_DIR; 

if (is_dir($imageUploadDir)) {
    $files = scandir($imageUploadDir);
    if ($files !== false) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && !is_dir($imageUploadDir . $file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $availableImageFiles[] = $file; 
                }
            }
        }
    } else {
         echo "<p class='warning'>AVERTISSEMENT : Impossible de lire le contenu du dossier d'images '" . htmlspecialchars($imageUploadDir) . "'.</p>";
    }
} else {
    echo "<p class='error'>ERREUR CRITIQUE : Le dossier d'images '" . htmlspecialchars($imageUploadDir) . "' n'existe pas ou n'est pas un dossier. Veuillez le créer.</p>";
    // On pourrait s'arrêter ici si les images sont cruciales, mais on continue pour créer des produits sans image
}

if (empty($availableImageFiles)) {
    echo "<p class='warning'>AVERTISSEMENT : Aucun fichier image valide (jpg, jpeg, png, gif) trouvé dans '" . htmlspecialchars($imageUploadDir) . "'. Les produits seront créés sans image.</p>";
    // On ajoute null pour que array_rand fonctionne et que les produits soient créés sans image.
    $availableImageFiles[] = null; 
} else {
    echo "<p>Images trouvées et utilisables : " . count($availableImageFiles) . " (\"" . implode('", "', array_map('htmlspecialchars', $availableImageFiles)) . "\")</p>";
    // Optionnel: ajouter explicitement des null pour avoir une chance de créer des produits sans image même si des images sont disponibles
    // $availableImageFiles[] = null;
    // $availableImageFiles[] = null;
}


// Récupérer les IDs des utilisateurs existants
$userIds = [];
try {
    $stmtUsers = $pdo->query("SELECT id FROM users");
    if ($stmtUsers) {
        $userIds = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    echo "<p class='error'>ERREUR : Impossible de récupérer les utilisateurs existants : " . htmlspecialchars($e->getMessage()) . "</p></body></html>";
    exit;
}

if (empty($userIds)) {
    echo "<p class='error'>ERREUR : Aucun utilisateur trouvé dans la base de données. Veuillez d'abord importer des utilisateurs ou en créer via le site.</p></body></html>";
    exit;
}

echo "<ul>";
$importedCount = 0;
$errorCount = 0;

for ($i = 0; $i < $numberOfProductsToCreate; $i++) {
    $randomUserId = $userIds[array_rand($userIds)];
    $randomTitle = $sampleTitles[array_rand($sampleTitles)] . " Série " . (mt_rand(100, 999)); 
    $randomDescription = $sampleDescriptions[array_rand($sampleDescriptions)] . " (Référence unique: " . substr(md5(uniqid(mt_rand(), true)), 0, 10) . ")";
    $randomPrice = mt_rand(1000, 50000) / 100; // Prix entre 10.00 et 500.00

    // Choisir une image au hasard parmi celles trouvées (peut être null si le tableau $availableImageFiles contient null)
    $randomImageName = $availableImageFiles[array_rand($availableImageFiles)];
    
    try {
        $sql = "INSERT INTO products (user_id, title, description, price, image_path) 
                VALUES (:user_id, :title, :description, :price, :image_path)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $randomUserId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $randomTitle);
        $stmt->bindParam(':description', $randomDescription);
        $stmt->bindParam(':price', $randomPrice);
        $stmt->bindParam(':image_path', $randomImageName);

        if ($stmt->execute()) {
            echo "<li class='success'>Produit '" . htmlspecialchars($randomTitle) . "' (Vendeur ID: $randomUserId, Image: " . ($randomImageName ? htmlspecialchars($randomImageName) : 'Aucune') . ") ajouté.</li>";
            $importedCount++;
        } else {
            echo "<li class='error'>Échec de l'ajout du produit '" . htmlspecialchars($randomTitle) . "'.</li>";
            $errorCount++;
        }
    } catch (PDOException $e) {
        echo "<li class='error'>Erreur PDO pour le produit '" . htmlspecialchars($randomTitle) . "': " . htmlspecialchars($e->getMessage()) . "</li>";
        error_log("Erreur PDO Seed Products: " . $e->getMessage());
        $errorCount++;
    }
}

echo "</ul>";
echo "<hr>";
echo "<p><strong>Peuplement terminé.</strong></p>";
echo "<p class='success'>Produits ajoutés avec succès : $importedCount</p>";
echo "<p class='error'>Produits avec erreurs ou non ajoutés : $errorCount</p>";
echo "</body></html>";

?>