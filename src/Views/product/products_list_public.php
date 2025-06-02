<?php
// src/views/product/products_list_public.php
// Les variables $pageTitle et $products sont disponibles.
?>
<h2>Tous nos articles</h2>

<?php if (!empty($products)): ?>
    <div class="product-list">
        <?php foreach ($products as $product): ?>
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
                    <p><small>Vendu par : <?php echo htmlspecialchars($product['seller_username']); ?></small></p>
                    <p><small>Ajouté le : <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></small></p>
                    <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_detail&id=<?php echo $product['id']; ?>" class="button-details">Voir détails</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Aucun article n'est actuellement en vente sur le site.</p>
<?php endif; ?>