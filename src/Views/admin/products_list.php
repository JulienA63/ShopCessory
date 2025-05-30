<?php
// src/views/admin/products_list.php
?>
<h2>Gestion de Toutes les Annonces</h2>

<?php 
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'product_deleted') echo "<p class='message success-message'>Annonce supprimée avec succès.</p>";
    if ($_GET['status'] === 'product_updated') echo "<p class='message success-message'>Annonce mise à jour avec succès.</p>";
}
if (isset($_GET['error'])) {
    // Gérer différents messages d'erreur si besoin (ex: invalid_id, db_error)
    echo "<p class='message error-message'>Une erreur s'est produite lors de l'opération.</p>";
}
?>

<?php if (!empty($products)): ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>ID</th><th>Titre</th><th>Vendeur</th><th>Prix</th><th>Date Création</th><th>Image</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_detail&id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($product['seller_username']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</td>
                    <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                    <td>
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($product['image_path']); ?>" alt="Aperçu" style="width: 50px; height: auto;">
                        <?php else: ?>
                            Aucune
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_product_edit_form&id=<?php echo $product['id']; ?>" class="button-like" style="background-color: #ffc107; color: #212529;">Modifier</a> |
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_product_delete&id=<?php echo $product['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Ceci est irréversible.');"
                           class="button-delete">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <style> th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle; } </style>
<?php else: ?>
    <p>Aucune annonce n'a été trouvée dans la base de données.</p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard">&laquo; Retour au tableau de bord Admin</a></p>