<?php
require_once '../../config/db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produit introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $image = trim($_POST['image_url']);
    $price = floatval($_POST['price']);

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, image_url=?, price=? WHERE id=?");
    $stmt->execute([$name, $desc, $image, $price, $id]);

    header("Location: dashboard.php");
    exit;
}
?>

<h1>Modifier le produit</h1>
<form method="post">
    <label>Nom : <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required></label><br>
    <label>Description : <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea></label><br>
    <label>URL image : <input type="text" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>"></label><br>
    <label>Prix (€) : <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required></label><br>
    <button type="submit">Enregistrer</button>
</form>
<a href="dashboard.php">Retour</a>
