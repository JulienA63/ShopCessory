<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord admin</title>
</head>
<body>
    <h1>Tableau de bord Administrateur</h1>
    <p>Bonjour <?php echo htmlspecialchars($_SESSION['username']); ?> (admin)</p>

    <ul>
        <li><a href="../products.php">📦 Gérer les produits</a></li>
        <li><a href="users.php">👤 Gérer les utilisateurs</a></li>
        <li><a href="../index.php">🏠 Retour à l'accueil du site</a></li>
        <li><a href="../logout.php">🚪 Se déconnecter</a></li>
    </ul>
</body>
</html>
