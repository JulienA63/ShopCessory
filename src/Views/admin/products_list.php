<?php
// src/views/admin/products_list.php
// $products et $pageTitle sont disponibles
?>
<h2>Gestion de Toutes les Annonces</h2>

<?php // Les messages flash sont affichés par le layout ?>

<?php if (!empty($products)): ?>
    <table>
        <thead>
            <tr>
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
                            <img class="admin-product-thumbnail" src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($product['image_path']); ?>" alt="Aperçu">
                        <?php else: ?>
                            Aucune
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_product_edit_form&id=<?php echo $product['id']; ?>" class="button-like admin-edit-button">Modifier</a>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_product_delete&id=<?php echo $product['id']; ?>" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Ceci est irréversible.');"
                           class="button-like button-delete">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Aucune annonce n'a été trouvée dans la base de données.</p>
<?php endif; ?>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_dashboard" class="button-back button-like">&laquo; Retour au tableau de bord Admin</a></p>