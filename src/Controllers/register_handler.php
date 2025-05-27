<?php
require '../../config/db.php';
require '../Models/user_model.php';

$username = $_POST['username'];
$password = $_POST['password'];

if (user_exists($username)) {
    echo "Nom d'utilisateur déjà utilisé.";
} else {
    create_user($username, $password);
    // redirection
    header("Location: login.php");
    exit;
}
