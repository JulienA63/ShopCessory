<?php
// src/views/admin/users_list.php
?>
<h2>Gestion des Utilisateurs</h2>

<div class="admin-actions-bar">
    <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_create_form" class="button-like">Créer un nouvel utilisateur</a>
    
    <form action="<?php echo INDEX_FILE_PATH; ?>" method="GET" class="search-form">
        <input type="hidden" name="url" value="admin_users_list">
        <input type="text" name="search_term" placeholder="Nom, prénom, pseudo, email..." 
               value="<?php echo isset($searchTerm) ? htmlspecialchars($searchTerm) : ''; ?>" class="search-input">
        <button type="submit" class="button-like search-button">Rechercher</button>
        <?php if (isset($searchTerm) && $searchTerm !== ''): ?>
            <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_users_list" class="button-like button-secondary">Effacer la recherche</a>
        <?php endif; ?>
    </form>
</div>

<?php // Les messages flash sont affichés par le layout ?>

<?php if (!empty($users)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Prénom</th><th>Nom</th><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_edit_form&id=<?php echo $user['id']; ?>" class="button-like admin-edit-button">Modifier</a>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): ?>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_delete&id=<?php echo $user['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Toutes ses annonces seront aussi supprimées.');"
                           class="button-like button-delete">Supprimer</a>
                        <?php else: echo " (Soi-même)"; endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (isset($searchTerm) && $searchTerm !== ''): ?>
    <p>Aucun utilisateur trouvé correspondant à votre recherche "<?php echo htmlspecialchars($searchTerm); ?>".</p>
<?php else: ?>
    <p>Aucun utilisateur trouvé dans la base de données.</p>
<?php endif; ?>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard" class="button-back button-like">&laquo; Retour au tableau de bord Admin</a></p>