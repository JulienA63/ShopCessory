<?php
// src/views/layout.php
// session_start(); est déjà appelé au début de public/index.php, donc $_SESSION et les fonctions de auth.php sont disponibles ici.
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
            <a href="#">Produits</a> {/* Placeholder */}

            <?php if (isUserLoggedIn()): ?>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</span>
                <?php if (isAdmin()): // Vérifie si l'utilisateur est un admin (fonction de src/lib/auth.php) ?>
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
        <?php
        // Afficher les messages de statut (sans style en ligne)
        if (isset($_GET['login_status']) && $_GET['login_status'] === 'success' && isset($_SESSION['user_id'])) {
            echo "<p class='message success-message'>Connexion réussie ! Bienvenue, " . htmlspecialchars($_SESSION['username']) . ".</p>";
        }
        if (isset($_GET['logout_status']) && $_GET['logout_status'] === 'success') {
            echo "<p class='message success-message'>Vous avez été déconnecté avec succès.</p>";
        }
        if (isset($_GET['error']) && $_GET['error'] === 'admin_required') {
            echo "<p class='message error-message'>Accès refusé. Vous devez être administrateur pour accéder à cette page.</p>";
        }
         if (isset($_GET['require_login']) && $_GET['require_login'] === 'true') {
            echo "<p class='message error-message'>Veuillez vous connecter pour accéder à cette page.</p>";
        }
        
        // Inclusion de la vue de contenu
        if (isset($contentView) && file_exists($contentView)) {
            // echo "\n"; // Décommentez pour déboguer le chemin de la vue
            require_once $contentView;
        } else {
            echo "<p class='message error-message'>Erreur : Contenu de la vue introuvable. (Chemin vérifié pour \$contentView: " . htmlspecialchars(isset($contentView) ? $contentView : 'Non défini') . ")</p>";
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> SHOPCESSORY - Tous droits réservés.</p>
    </footer>
</body>
</html>