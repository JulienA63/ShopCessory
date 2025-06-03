<?php
// src/views/product/my_products_list.php
// Les variables $pageTitle et $myProducts sont disponibles.
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
                    <p class="product-description">
                        <?php 
                        $description = htmlspecialchars($product['description']);
                        if (strlen($description) > 80) { echo substr($description, 0, 80) . '...'; } 
                        else { echo $description; }
                        ?>
                    </p>
                </div>
                <div class="product-footer">
                    <p class="price"><strong>Prix :</strong> <?php echo htmlspecialchars(number_format($product['price'], 2, ',', ' ')); ?> €</p>
                    <p class="date-info"><small>Ajouté le : <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></small></p>
                    <?php // Le commentaire parasite a été enlevé d'ici. ?>
                    <?php // Le lien pour modifier par l'utilisateur n'est pas encore implémenté fonctionnellement.
                          // Si tu souhaites ajouter un lien de modification qui mène à la même page que l'admin pour l'instant (en supposant que l'admin peut modifier toutes les annonces) :
                          // Ou mieux, créer une route et une logique spécifique pour que l'utilisateur modifie sa propre annonce.
                          // Pour l'instant, on le laisse commenté pour éviter toute confusion.
                    ?>
                    <a href="<?php //echo INDEX_FILE_PATH; ?>?url=product_edit_user&id=<?php //echo $product['id']; ?>" class="button-like">Modifier</a>
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

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=dashboard" class="button-back">&laquo; Retour à Mon Compte</a></p>