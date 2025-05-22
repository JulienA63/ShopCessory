<?php

$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<nav>
    <p><a href="/ShopCessory/public/index.php">🏠 Accueil</a> |
        <a href="/ShopCessory/public/products.php">📦 Produits</a>
        <?php if ($isAdmin): ?>
            | <a href="/ShopCessory/public/admin/dashboard.php">⚙️ Admin</a>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            | Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> |
            <a href="/ShopCessory/public/logout.php">Se déconnecter</a>
        <?php else: ?>
            | <a href="/ShopCessory/public/login.php">Se connecter</a>
            | <a href="/ShopCessory/public/register.php">S'inscrire</a>
            | <a href="/ShopCessory/public/cart.php">🛒 Panier</a>
        <?php endif; ?>
    </p>
    <hr>
</nav>
