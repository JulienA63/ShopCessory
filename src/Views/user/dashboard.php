<?php
// src/views/user/dashboard.php
?>
<h2>Mon Tableau de Bord</h2>
<p>Bienvenue sur votre espace personnel, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Utilisateur'; ?> !</p>

<ul>
    <li><a href="#">Modifier mon profil (à venir)</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=my_products">Voir mes annonces</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add">Vendre un nouvel article</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=logout">Se déconnecter</a></li>
</ul>