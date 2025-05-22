<?php
require_once '../../config/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $image = trim($_POST['image_url']);
    $price = floatval($_POST['price']);

    $stmt = $pdo->prepare("INSERT INTO products (name, description, image_url, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $image, $price]);

    header("Location: dashboard.php");
    exit;
}
?>

<h1>Ajouter un produit</h1>
<form method="post">
    <label>Nom : <input type="text" name="name" required></label><br>
    <label>Description : <textarea name="description"></textarea></label><br>
    <label>URL image : <input type="text" name="image_url"></label><br>
    <label>Prix (€) : <input type="number" step="0.01" name="price" required></label><br>
    <button type="submit">Ajouter</button>
</form>
<a href="dashboard.php">Retour</a>
