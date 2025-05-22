<?php
session_start();

// Rediriger si non connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Initialiser le panier
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Modifier la quantité
if (isset($_POST['update_quantity'])) {
    $id = $_POST['update_id'];
    $qty = max(1, intval($_POST['quantity']));
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] = $qty;
        $_SESSION['message'] = "Quantité mise à jour pour " . $_SESSION['cart'][$id]['name'];
    }
    header("Location: cart.php");
    exit;
}

// Ajouter un produit
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['price'];

    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = [
            'name' => $name,
            'price' => $price,
            'quantity' => 1
        ];
    } else {
        $_SESSION['cart'][$product_id]['quantity']++;
    }

    $_SESSION['message'] = "Produit ajouté au panier : $name";
    header("Location: cart.php");
    exit;
}

// Supprimer un produit
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    $_SESSION['message'] = "Produit retiré du panier.";
    header("Location: cart.php");
    exit;
}

// Vider le panier
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $_SESSION['message'] = "Le panier a été vidé.";
    header("Location: cart.php");
    exit;
}
?>

<h1>Votre panier</h1>

<?php
if (isset($_SESSION['message'])) {
    echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['message']) . "</p>";
    unset($_SESSION['message']);
}
?>

<?php if (empty($_SESSION['cart'])): ?>
    <p>Votre panier est vide.</p>
<?php else: ?>
    <ul>
        <?php
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $item):
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
        ?>
        <li>
            <form method="post" action="cart.php" style="display: inline;">
                <input type="hidden" name="update_id" value="<?php echo $id; ?>">
                <?php echo htmlspecialchars($item['name']); ?>
                × <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1">
                × <?php echo number_format($item['price'], 2); ?> €
                = <?php echo number_format($subtotal, 2); ?> €
                <button type="submit" name="update_quantity">Modifier</button>
            </form>
            <a href="?remove=<?php echo $id; ?>">🗑 Supprimer</a>
        </li>
        <?php endforeach; ?>
    </ul>

    <p><strong>Total : <?php echo number_format($total, 2); ?> €</strong></p>
    <a href="?clear=true">🧹 Vider le panier</a>
<?php endif; ?>

<p><a href="products.php">⬅ Retour aux produits</a></p>
<p><a href="index.php">🏠 Retour à l'accueil</a></p>
