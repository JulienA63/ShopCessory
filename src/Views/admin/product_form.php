<?php
// src/views/admin/product_form.php
$isEditMode = (isset($productToEdit) && !empty($productToEdit['id']));
$submitButtonText = $isEditMode ? "Mettre à jour l'annonce" : "Créer l'annonce";
?>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<?php if (!empty($errors)): ?>
    <div class="message error-message"> {/* Classe pour styler le conteneur d'erreurs */}
        <p><strong>Veuillez corriger les erreurs suivantes :</strong></p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($formActionUrl); ?>" method="POST" enctype="multipart/form-data">
    <div>
        <label for="title">Titre de l'annonce :</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars(isset($formData['title']) ? $formData['title'] : ($isEditMode && isset($productToEdit['title']) ? $productToEdit['title'] : '')); ?>" required>
    </div>
    <div>
        <label for="description">Description :</label>
        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars(isset($formData['description']) ? $formData['description'] : ($isEditMode && isset($productToEdit['description']) ? $productToEdit['description'] : '')); ?></textarea>
    </div>
    <div>
        <label for="price">Prix (€) :</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars(isset($formData['price']) ? $formData['price'] : ($isEditMode && isset($productToEdit['price']) ? $productToEdit['price'] : '')); ?>" required>
    </div>
    
    <?php if ($isEditMode && !empty($productToEdit['image_path'])): ?>
        <div>
            <p>Image actuelle :</p>
            <img class="current-product-image-admin" src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($productToEdit['image_path']); ?>" alt="Image actuelle">
            <br>
            <input type="checkbox" name="delete_image" id="delete_image" value="1">
            <label for="delete_image" class="inline-label">Supprimer l'image actuelle.</label>
        </div>
    <?php endif; ?>
    
    <div>
        <label for="product_image"><?php echo $isEditMode && !empty($productToEdit['image_path']) ? 'Changer l\'image' : 'Ajouter une image'; ?> :</label>
        <input type="file" id="product_image" name="product_image" accept="image/jpeg, image/png, image/gif">
        <?php if ($isEditMode && !empty($productToEdit['image_path'])): ?>
            <small>Laissez vide pour conserver l'image actuelle (sauf si "Supprimer l'image actuelle" est coché).</small>
        <?php endif; ?>
    </div>
    
    <div>
        <button type="submit" class="button-like"><?php echo $submitButtonText; ?></button>
    </div>
</form>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_products_list" class="button-back button-like">&laquo; Retour à la liste des annonces</a></p>