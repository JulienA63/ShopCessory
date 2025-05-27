<?php
require '../../config/db.php';
require '../Models/ad_model.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour publier une annonce.";
    exit;
}

$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$category_id = $_POST['category_id'];
$user_id = $_SESSION['user_id'];

// GESTION DE L’IMAGE
$image_name = $_FILES['image']['name'];
$image_tmp = $_FILES['image']['tmp_name'];
$image_path = '../../assets/uploads/' . $image_name;

if (move_uploaded_file($image_tmp, $image_path)) {
    $image = $image_name;
} else {
    echo "Erreur lors de l'envoi de l'image.";
    exit;
}

create_ad($title, $description, $price, $image, $category_id, $user_id);
echo "Annonce ajoutée avec succès.";

header("Location: ../Views/home.php");
exit;