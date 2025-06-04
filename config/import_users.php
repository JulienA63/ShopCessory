<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ROOT_PATH est le parent du dossier actuel (config), donc la racine du projet Shopcessory
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__)); 
}

// Charger la configuration de la base de données
require_once ROOT_PATH . '/config/database.php'; 

echo "<!DOCTYPE html><html><head><title>Importation Utilisateurs</title>";
echo "<style> body {font-family: sans-serif; padding: 20px;} ul {list-style-type: none; padding-left:0;} li {margin-bottom: 5px;} .success {color: green;} .error {color: red;} .warning {color: orange;} </style>";
echo "</head><body>";
echo "<h1>Résultat de l'importation des utilisateurs</h1>";

// Le fichier CSV est maintenant attendu dans le dossier config/
// __DIR__ pointe vers le dossier actuel du script (config/)
$csvFilePath = __DIR__ . '/utilisateurs_a_importer.csv'; 

if (!file_exists($csvFilePath)) {
    echo "<p class='error'>ERREUR : Le fichier CSV '" . htmlspecialchars($csvFilePath) . "' n'a pas été trouvé dans le dossier config.</p>";
    echo "</body></html>";
    exit;
}

$pdo = getPDOConnection();
if (!$pdo) {
    echo "<p class='error'>ERREUR : Impossible de se connecter à la base de données.</p>";
    echo "</body></html>";
    exit;
}

$fileHandle = fopen($csvFilePath, 'r');
if ($fileHandle === false) {
    echo "<p class='error'>ERREUR : Impossible d'ouvrir le fichier CSV.</p>";
    echo "</body></html>";
    exit;
}

$header = fgetcsv($fileHandle); 
if ($header === false) {
    echo "<p class='error'>ERREUR : Fichier CSV vide ou en-tête illisible.</p>";
    fclose($fileHandle);
    echo "</body></html>";
    exit;
}

$lineNumber = 1;
$importedCount = 0;
$errorCount = 0;

echo "<ul>";

while (($data = fgetcsv($fileHandle)) !== false) {
    $lineNumber++;
    if (count($data) < 6) { 
        echo "<li class='warning'>Ligne $lineNumber : Données incomplètes, ignorée.</li>";
        $errorCount++;
        continue;
    }

    $firstname = trim($data[0]);
    $lastname = trim($data[1]);
    $username = trim($data[2]);
    $email = trim($data[3]);
    $password = trim($data[4]);
    $role = trim($data[5]);

    // Validations (inchangées)
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($role)) {
        echo "<li class='warning'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Données manquantes, ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<li class='warning'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Format e-mail invalide, ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (strlen($password) < 6) {
        echo "<li class='warning'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Mdp trop court, ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (!in_array($role, ['user', 'admin'])) {
        echo "<li class='warning'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Rôle invalide, ignorée.</li>";
        $errorCount++;
        continue;
    }

    try {
        // Vérifications d'unicité et insertion (inchangées)
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmtCheck->bindParam(':username', $username);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            echo "<li class='warning'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . " / Email: " . htmlspecialchars($email) . "): Username ou Email déjà existant, ignorée.</li>";
            $errorCount++;
            continue;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES (:firstname, :lastname, :username, :email, :password, :role)";
        $stmtInsert = $pdo->prepare($sql);
        // ... bindParams ...
        $stmtInsert->bindParam(':firstname', $firstname);
        $stmtInsert->bindParam(':lastname', $lastname);
        $stmtInsert->bindParam(':username', $username);
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':password', $hashedPassword);
        $stmtInsert->bindParam(':role', $role);
        
        if ($stmtInsert->execute()) {
            echo "<li class='success'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Importé.</li>";
            $importedCount++;
        } else { /* ... */ }

    } catch (PDOException $e) { /* ... */ }
}

fclose($fileHandle);

echo "</ul>";
echo "<hr>";
echo "<p><strong>Importation terminée.</strong></p>";
echo "<p class='success'>Utilisateurs importés : $importedCount</p>";
echo "<p class='error'>Lignes avec erreurs/ignorées : $errorCount</p>";
echo "</body></html>";
?>