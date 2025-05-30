<?php
// src/views/product/detail.php
// La variable $product est disponible ($product peut être null si non trouvé)
echo ""; // Commentaire HTML pour le source de la page
?>

<?php if (isset($product) && $product): ?>
    <article class="product-detail">
        <h2><?php echo htmlspecialchars($product['title']); ?></h2>
        <?php if (!empty($product['image_path'])): ?>
            <div class="product-image-container">
                <img src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($product['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($product['title']); ?>">
            </div>
        <?php else: ?>
            <div class="product-no-image-detail"><span>Pas d'image disponible</span></div>
        <?php endif; ?>
        <p><strong>Description :</strong></p>
        <div class="product-description-box"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
        <p><strong>Prix :</strong> <?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</p>
        <?php if (isset($product['seller_username'])): ?>
            <p><strong>Vendu par :</strong> <?php echo htmlspecialchars($product['seller_username']); ?></p>
        <?php endif; ?>
        <p><small>Mis en vente le : <?php echo date('d/m/Y à H:i', strtotime($product['created_at'])); ?></small></p>
        <?php if (isUserLoggedIn() && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['user_id']): ?>
            <p class="owner-options">C'est votre annonce.<br>
                <a href="<?php echo INDEX_FILE_PATH; ?>?url=product_delete&id=<?php echo $product['id']; ?>" 
                   onclick="return confirm('Êtes-vous sûr ?');" class="button-delete">Supprimer</a>
            </p>
        <?php endif; ?>
        <p><a href="<?php echo INDEX_FILE_PATH; ?>?url=accueil" class="button-back">&laquo; Retour</a></p>
    </article>
<?php else: ?>
    <?php echo ""; ?>
    <p>Le produit que vous cherchez n'a pas été trouvé.</p>
    <p><a href="<?php echo INDEX_FILE_PATH; ?>?url=accueil">&laquo; Retour à la liste</a></p>
<?php endif; ?>