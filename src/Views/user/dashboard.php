<?php
// src/views/user/dashboard.php
?>
<h2>Mon Tableau de Bord</h2>
<p>Bienvenue sur votre espace personnel, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Utilisateur'; ?> !</p>

<ul>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=profile_edit_form">Modifier mon profil</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=my_products">Voir mes annonces</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add">Vendre un nouvel article</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=logout">Se déconnecter</a></li>
</ul>   