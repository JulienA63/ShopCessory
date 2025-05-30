<?php
// src/views/user/dashboard.php
// (ensureUserIsLoggedIn() a déjà été appelé dans le contrôleur)
?>

<h2>Mon Tableau de Bord</h2>
<p>Bienvenue sur votre espace personnel, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Utilisateur'; ?> !</p>
<p>Ici, vous pourrez bientôt gérer vos annonces, voir vos commandes, etc.</p>

<ul>
    <li><a href="#">Modifier mon profil (à venir)</a></li>
    <li><a href="#">Voir mes annonces (à venir)</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=logout">Se déconnecter</a></li>
</ul>