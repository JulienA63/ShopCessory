<?php
// src/views/product/add_form.php
echo "<h1>--- DEBUG: CONTENU DE add_form.php EST BIEN CHARGÉ ---</h1>"; 
?>

<h2>Vendre un nouvel article</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=product_create_process" method="POST" enctype="multipart/form-data">
    <div>
        <label for="title">Titre de l'annonce :</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="description">Description :</label>
        <textarea id="description" name="description" rows="5"></textarea>
    </div>
    <div>
        <label for="price">Prix (€) :</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required>
    </div>
    <div>
        <label for="product_image">Image du produit :</label>
        <input type="file" id="product_image" name="product_image" accept="image/jpeg, image/png, image/gif">
    </div>
    <div>
        <button type="submit">Mettre en vente</button>
    </div>
</form>