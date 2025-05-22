<?php
session_start();
include '../src/View/navbar.php';
// Vérifie si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ShopCessory</title>
</head>
<body>
<h1>Bienvenue sur ShopCessory</h1>
<p>Bonjour, <?php echo htmlspecialchars($_SESSION['username'] ?? 'visiteur'); ?> !</p>
    <p><a href="products.php">Voir les produits</a></p>

<?php include '../src/View/footer.php'; ?>

</body>
</html>