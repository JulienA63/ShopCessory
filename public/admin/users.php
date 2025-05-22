<?php
require_once '../../config/db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>

<h1>Gestion des utilisateurs</h1>
<a href="add_user.php">Ajouter un utilisateur</a>
<ul>
<?php foreach ($users as $user): ?>
    <li>
        <?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)
        <a href="edit_user.php?id=<?php echo $user['id']; ?>">Modifier</a>
        <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
    </li>
<?php endforeach; ?>
</ul>
<a href="dashboard.php">Retour produits</a>
