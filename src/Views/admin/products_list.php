<?php
// src/views/product/products_list_public.php
// Les variables $pageTitle et $products sont disponibles.
?>
<h2>Tous nos articles en vente</h2>

<?php if (!empty($products)): ?>
    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-item">
                <?php if (!empty($product['image_path'])): ?>
                    <img src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                <?php else: ?>
                    <div class="product-no-image"><span>Pas d'image</span></div>
                <?php endif; ?>
                <div class="product-content">
                    <h3>
                        <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_detail&id=<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </a>
                    </h3>
                    <p class="product-description">
                        <?php 
                        $description = htmlspecialchars($product['description']);
                        if (strlen($description) > 70) { 
                            echo substr($description, 0, 70) . '...';
                        } else {
                            echo $description;
                        }
                        ?>
                    </p>
                </div>
                <div class="product-footer">
                    <p class="price"><strong>Prix :</strong> <?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</p>
                    <?php if (isset($product['seller_username'])): ?>
                        <p class="seller-info"><small>Vendu par : <?php echo htmlspecialchars($product['seller_username']); ?></small></p>
                    <?php endif; ?>
                    <p class="date-info"><small>Ajouté le : <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></small></p>
                    <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_detail&id=<?php echo $product['id']; ?>" class="button-like button-details">Voir détails</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Il n'y a aucun article en vente pour le moment.</p>
<?php endif; ?>