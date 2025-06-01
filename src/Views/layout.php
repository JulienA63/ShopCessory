<?php
// src/views/layout.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "SHOPCESSORY"; ?></title>
    <link rel="stylesheet" href="<?php echo PUBLIC_PATH_ASSET; ?>css/style.css">
</head>
<body>
    <header>
        <h1>SHOPCESSORY</h1>
        <nav>
            <a href="<?php echo INDEX_FILE_PATH; ?>?url=accueil">Accueil</a>
            <a href="#">Produits</a>
            <?php if (isUserLoggedIn()): ?>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</span>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard" style="color: #ffc107; font-weight: bold;">Administration</a>
                <?php endif; ?>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=dashboard">Mon Compte</a>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add">Vendre un article</a>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=logout">Se déconnecter</a>
            <?php else: ?>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=inscription">S'inscrire</a>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=login">Se connecter</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?php display_flash_messages(); ?>
        <?php
        if (isset($contentView) && file_exists($contentView)) {
            require_once $contentView;
        } else {
            echo "<p class='message error-message'>Erreur : Contenu de la vue introuvable. (Chemin : " . htmlspecialchars(isset($contentView) ? $contentView : 'Non défini') . ")</p>";
        }
        ?>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> SHOPCESSORY - Tous droits réservés.</p>
    </footer>
</body>
</html>