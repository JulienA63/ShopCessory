<?php
// src/views/admin/dashboard.php
?>
<h2>Panneau d'Administration</h2>
<p>Bienvenue, Administrateur <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?> !</p>
<p>À partir d'ici, vous pourrez gérer les utilisateurs et les annonces du site.</p>
<ul>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_users_list">Gérer les utilisateurs</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_products_list">Gérer toutes les annonces</a></li>
    <li><a href="<?php echo INDEX_FILE_PATH; ?>?url=accueil">Retour au site public</a></li>
</ul>