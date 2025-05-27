<?php
require '../../config/db.php';
require '../Models/user_model.php';
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

$user = get_user_by_username($username);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    echo "Connexion réussie. Bonjour " . htmlspecialchars($user['username']);
} else {
    echo "Identifiants incorrects.";
}
