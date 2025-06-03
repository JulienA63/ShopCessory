<?php
// src/views/home.php
// Les variables $pageTitle et $products sont disponibles.
?>
<div class="welcome-banner" style="text-align: center; margin-bottom: 30px; padding: 20px; background-color: #e9f5ff; border-radius: 8px;">
    <h2>Bienvenue sur Shopcessory !</h2>
    <p>Découvrez les dernières trouvailles ou vendez vos propres accessoires.</p>
    <p>
        <a href="<?php echo INDEX_FILE_PATH; ?>?url=products_list_public" class="button-like">Voir tous les articles</a> 
        <?php if (isUserLoggedIn()): ?>
            <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add" class="button-like" style="background-color: #28a745;">Vendre un article</a>
        <?php else: ?>
            <a href="<?php echo INDEX_FILE_PATH; ?>?url=login" class="button-like" style="background-color: #28a745;">Connectez-vous pour Vendre</a>
        <?php endif; ?>
    </p>
</div>

<h2>Nos derniers articles !</h2>

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
                        if (strlen($description) > 70) { // Limiter la description pour la carte
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
    <p>Aucun article n'est actuellement en vente sur le site.</p>
    <?php if (isUserLoggedIn()): ?>
        <p><a href="<?php echo INDEX_FILE_PATH; ?>?url=product_add" class="button-like">Vendre votre premier article</a></p>
    <?php else: ?>
        <p><a href="<?php echo INDEX_FILE_PATH; ?>?url=login" class="button-like">Connectez-vous pour vendre un article</a></p>
    <?php endif; ?>
<?php endif; ?>