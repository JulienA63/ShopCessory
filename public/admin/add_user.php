<?php
require_once '../../config/db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $username, $password, $role]);

    header("Location: users.php");
    exit;
}
?>

<h1>Ajouter un utilisateur</h1>
<form method="post">
    <label>Prénom : <input type="text" name="firstname" required></label><br>
    <label>Nom : <input type="text" name="lastname" required></label><br>
    <label>Nom d'utilisateur : <input type="text" name="username" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <label>Rôle :
        <select name="role">
            <option value="user">Utilisateur</option>
            <option value="admin">Administrateur</option>
        </select>
    </label><br>
    <button type="submit">Ajouter</button>
</form>
<a href="users.php">Retour</a>
