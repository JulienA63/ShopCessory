<?php include __DIR__ . '/partials/header.php'; ?>
<h2>Liste des annonces</h2>
<?php foreach ($ads as $ad): ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
        <h3><?php echo htmlspecialchars($ad['title']); ?> - <?php echo htmlspecialchars($ad['price']); ?>€</h3>
        <p><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
        <?php if (!empty($ad['image'])): ?>
            <img src="../../assets/uploads/<?php echo htmlspecialchars($ad['image']); ?>" style="max-width: 200px;">
        <?php endif; ?>
        <p><strong>Catégorie ID :</strong> <?php echo $ad['category_id']; ?></p>
        <p><small>Publié le <?php echo $ad['created_at']; ?></small></p>
    </div>
<?php endforeach; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>
