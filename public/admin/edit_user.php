<?php
require_once '../../config/db.php';
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET firstname=?, lastname=?, username=?, role=? WHERE id=?");
    $stmt->execute([$firstname, $lastname, $username, $role, $id]);

    header("Location: users.php");
    exit;
}
?>

<h1>Modifier un utilisateur</h1>
<form method="post">
    <label>Prénom : <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required></label><br>
    <label>Nom : <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required></label><br>
    <label>Nom d'utilisateur : <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></label><br>
    <label>Rôle :
        <select name="role">
            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>Utilisateur</option>
            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Administrateur</option>
        </select>
    </label><br>
    <button type="submit">Enregistrer</button>
</form>
<a href="users.php">Retour</a>
