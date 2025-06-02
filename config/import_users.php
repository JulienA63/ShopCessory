<?php
// import_users.php

// Activer l'affichage des erreurs pour ce script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Comme ce script est à la racine, ajustons les chemins pour les inclusions
define('ROOT_PATH', __DIR__); // Le dossier actuel où se trouve import_users.php
define('APP_PATH', ROOT_PATH . '/src'); // Chemin vers le dossier src

require_once ROOT_PATH . '/config/database.php'; // Pour getPDOConnection()
// On n'a pas besoin de l'autoloader, du routeur, etc., pour ce script simple.

echo "<!DOCTYPE html><html><head><title>Importation Utilisateurs</title></head><body>";
echo "<h1>Résultat de l'importation des utilisateurs</h1>";

$csvFilePath = ROOT_PATH . '/utilisateurs_a_importer.csv'; // Chemin vers ton fichier CSV

if (!file_exists($csvFilePath)) {
    echo "<p style='color:red;'>ERREUR : Le fichier CSV '" . htmlspecialchars($csvFilePath) . "' n'a pas été trouvé.</p>";
    echo "</body></html>";
    exit;
}

$pdo = getPDOConnection();
if (!$pdo) {
    echo "<p style='color:red;'>ERREUR : Impossible de se connecter à la base de données.</p>";
    echo "</body></html>";
    exit;
}

$fileHandle = fopen($csvFilePath, 'r');
if ($fileHandle === false) {
    echo "<p style='color:red;'>ERREUR : Impossible d'ouvrir le fichier CSV.</p>";
    echo "</body></html>";
    exit;
}

$header = fgetcsv($fileHandle); // Lire la ligne d'en-tête pour la sauter (ou l'utiliser pour mapper les colonnes)
if ($header === false) {
    echo "<p style='color:red;'>ERREUR : Fichier CSV vide ou en-tête illisible.</p>";
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
    // Supposons que les colonnes sont dans l'ordre : firstname, lastname, username, email, password, role
    if (count($data) < 6) { // S'assurer qu'on a assez de colonnes
        echo "<li style='color:orange;'>Ligne $lineNumber : Données incomplètes, ignorée.</li>";
        $errorCount++;
        continue;
    }

    $firstname = trim($data[0]);
    $lastname = trim($data[1]);
    $username = trim($data[2]);
    $email = trim($data[3]);
    $password = trim($data[4]);
    $role = trim($data[5]);

    // Validation simple (tu peux l'étendre)
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($role)) {
        echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Données manquantes, ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Format d'email invalide ('" . htmlspecialchars($email) . "'), ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (strlen($password) < 6) {
        echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Mot de passe trop court, ignorée.</li>";
        $errorCount++;
        continue;
    }
    if (!in_array($role, ['user', 'admin'])) {
        echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Rôle invalide ('" . htmlspecialchars($role) . "'), ignorée.</li>";
        $errorCount++;
        continue;
    }

    try {
        // Vérifier unicité username
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmtCheck->bindParam(':username', $username);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Nom d'utilisateur déjà existant, ignorée.</li>";
            $errorCount++;
            continue;
        }

        // Vérifier unicité email
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            echo "<li style='color:orange;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Email déjà existant ('" . htmlspecialchars($email) . "'), ignorée.</li>";
            $errorCount++;
            continue;
        }

        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur
        $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES (:firstname, :lastname, :username, :email, :password, :role)";
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->bindParam(':firstname', $firstname);
        $stmtInsert->bindParam(':lastname', $lastname);
        $stmtInsert->bindParam(':username', $username);
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':password', $hashedPassword);
        $stmtInsert->bindParam(':role', $role);
        
        if ($stmtInsert->execute()) {
            echo "<li style='color:green;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Importé avec succès.</li>";
            $importedCount++;
        } else {
            echo "<li style='color:red;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Échec de l'insertion en base de données.</li>";
            $errorCount++;
        }

    } catch (PDOException $e) {
        echo "<li style='color:red;'>Ligne $lineNumber (Utilisateur: " . htmlspecialchars($username) . "): Erreur PDO - " . $e->getMessage() . ", ignorée.</li>";
        error_log("Erreur PDO Import CSV Ligne $lineNumber: " . $e->getMessage());
        $errorCount++;
        continue;
    }
}

fclose($fileHandle);

echo "</ul>";
echo "<hr>";
echo "<p><strong>Importation terminée.</strong></p>";
echo "<p style='color:green;'>Utilisateurs importés avec succès : $importedCount</p>";
echo "<p style='color:red;'>Lignes avec erreurs ou ignorées : $errorCount</p>";
echo "</body></html>";

?>