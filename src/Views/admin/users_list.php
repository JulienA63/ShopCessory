<?php
// src/views/admin/users_list.php
?>
<h2>Gestion des Utilisateurs</h2>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_create_form" class="button-like">Créer un nouvel utilisateur</a></p>

<?php // Afficher les messages de statut/erreur (simpliste pour l'instant)
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') echo "<p class='message success-message'>Utilisateur créé avec succès.</p>";
    if ($_GET['status'] === 'updated') echo "<p class='message success-message'>Utilisateur mis à jour avec succès.</p>";
    if ($_GET['status'] === 'deleted') echo "<p class='message success-message'>Utilisateur supprimé avec succès.</p>";
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'cannot_delete_self') echo "<p class='message error-message'>Vous ne pouvez pas supprimer votre propre compte administrateur.</p>";
    else echo "<p class='message error-message'>Une erreur s'est produite.</p>";
}
?>

<?php if (!empty($users)): ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>ID</th><th>Prénom</th><th>Nom</th><th>Nom d'utilisateur</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                    <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_edit_form&id=<?php echo $user['id']; ?>">Modifier</a> |
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): // Ne pas afficher le lien de suppression pour soi-même ?>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_user_delete&id=<?php echo $user['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action supprimera aussi toutes ses annonces.');"
                           class="button-delete">Supprimer</a>
                        <?php else: echo " (Soi-même)"; endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <style> /* Styles temporaires pour la table */
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
<?php else: ?>
    <p>Aucun utilisateur trouvé.</p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard">&laquo; Retour au tableau de bord Admin</a></p>