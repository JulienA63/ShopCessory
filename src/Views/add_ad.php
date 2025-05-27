<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Ajouter une annonce</h2>
<form method="POST" action="../Controllers/add_ad_handler.php" enctype="multipart/form-data">
    <input type="text" name="title" required placeholder="Titre"><br>
    <textarea name="description" required placeholder="Description"></textarea><br>
    <input type="number" name="price" step="0.01" required placeholder="Prix"><br>
    <input type="file" name="image" accept="image/*" required><br>
    <select name="category_id" required>
        <option value="">-- Choisir une catégorie --</option>
        <option value="1">Montre</option>
        <option value="2">Bracelet</option>
        <option value="3">Lunettes</option>
        <option value="4">Sac</option>
        <option value="5">Autre</option>
    </select><br><br>
    <button type="submit">Publier</button>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>
