<?php
// src/views/admin/product_form.php
// Variables attendues :
// $pageTitle
// $productToEdit (objet/array du produit pour l'édition, ou null/vide pour la création par admin)
// $formActionUrl (URL de soumission du formulaire)
// $errors (tableau optionnel des erreurs de validation)

$isEditMode = (isset($productToEdit) && !empty($productToEdit['id']));
$submitButtonText = $isEditMode ? "Mettre à jour l'annonce" : "Créer l'annonce"; // (Pour une future création par admin)
                                                                              // Pour l'instant, ce formulaire est utilisé seulement pour l'édition.
?>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<?php if (!empty($errors)): ?>
    <div class="message error-message" style="border: 1px solid red; padding: 10px; margin-bottom: 15px;">
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
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($isEditMode ? $productToEdit['title'] : ''); ?>" required>
    </div>
    <div>
        <label for="description">Description :</label>
        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($isEditMode ? $productToEdit['description'] : ''); ?></textarea>
    </div>
    <div>
        <label for="price">Prix (€) :</label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($isEditMode ? $productToEdit['price'] : ''); ?>" required>
    </div>
    
    <?php if ($isEditMode && !empty($productToEdit['image_path'])): ?>
        <div>
            <p>Image actuelle :</p>
            <img src="<?php echo PRODUCT_IMAGE_BASE_URL . htmlspecialchars($productToEdit['image_path']); ?>" alt="Image actuelle" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; border:1px solid #ddd;">
            <br>
            <input type="checkbox" name="delete_image" id="delete_image" value="1">
            <label for="delete_image" style="display:inline; font-weight:normal;">Supprimer l'image actuelle (si cochée, aucune nouvelle image ne sera prise en compte pour cet envoi, et l'ancienne sera supprimée).</label>
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

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_products_list">&laquo; Retour à la liste des annonces</a></p>