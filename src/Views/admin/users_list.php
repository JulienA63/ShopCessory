<?php
// src/views/admin/users_list.php
?>
<h2>Gestion des Utilisateurs</h2>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_create_form" class="button-like">Créer un nouvel utilisateur</a>
    
    <form action="<?php echo INDEX_FILE_PATH; ?>" method="GET" style="display: flex; gap: 10px;">
        <input type="hidden" name="url" value="admin_users_list">
        <input type="text" name="search_term" placeholder="Nom, prénom, pseudo, email..." 
               value="<?php echo isset($searchTerm) ? htmlspecialchars($searchTerm) : ''; ?>" 
               style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
        <button type="submit" class="button-like" style="background-color: #28a745;">Rechercher</button>
        <?php if (isset($searchTerm) && $searchTerm !== ''): ?>
            <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_users_list" class="button-like" style="background-color: #6c757d;">Effacer la recherche</a>
        <?php endif; ?>
    </form>
</div>

<?php // Les messages flash sont affichés par le layout ?>

<?php if (!empty($users)): ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
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
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_edit_form&id=<?php echo $user['id']; ?>" class="button-like" style="background-color: #ffc107; color: #212529; padding: 5px 10px; font-size: 0.9em;">Modifier</a>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): ?>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_delete&id=<?php echo $user['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Toutes ses annonces seront aussi supprimées.');"
                           class="button-delete" style="padding: 5px 10px; font-size: 0.9em;">Supprimer</a>
                        <?php else: echo " (Soi-même)"; endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <style> th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } </style>
<?php elseif (isset($searchTerm) && $searchTerm !== ''): ?>
    <p>Aucun utilisateur trouvé correspondant à votre recherche "<?php echo htmlspecialchars($searchTerm); ?>".</p>
<?php else: ?>
    <p>Aucun utilisateur trouvé dans la base de données.</p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard">&laquo; Retour au tableau de bord Admin</a></p>