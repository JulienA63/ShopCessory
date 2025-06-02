<?php
// src/views/product/my_products_list.php
// $pageTitle et $myProducts (ou $products, vérifie ce qui est passé par le contrôleur ProductController::listMyProducts) sont disponibles.
// Assumons $myProducts
?>
<h2>Mes Annonces</h2>

<?php if (!empty($myProducts)): ?>
    <div class="product-list">
        <?php foreach ($myProducts as $product): ?>
            <div class="product-item">
                <div>
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>">
                    <?php else: ?>
                        <div class="product-no-image"><span>Pas d'image</span></div>
                    <?php endif; ?>
                    <h3>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_detail&id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </a>
                    </h3>
                    <p>
                        <?php 
                        $description = htmlspecialchars($product['description']);
                        if (strlen($description) > 80) { echo substr($description, 0, 80) . '...'; } 
                        else { echo $description; }
                        ?>
                    </p>
                </div>
                <div>
                    <p><strong>Prix :</strong> <?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</p>
                    <p><small>Ajouté le : <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></small></p>
                    {/* Lien pour modifier (à implémenter si tu veux que l'utilisateur modifie ses propres annonces) */}
                    {/* <a href="<?php //echo INDEX_FILE_PATH; ?>?url=product_edit_user&id=<?php //echo $product['id']; ?>" class="button-like">Modifier</a> */}
                    <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_delete&id=<?php echo $product['id']; ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');"
                       class="button-delete">Supprimer</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Vous n'avez aucune annonce en ligne pour le moment.</p>
    <p><a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add" class="button-like">Vendre votre premier article</a></p>
<?php endif; ?>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=dashboard">&laquo; Retour à Mon Compte</a></p>