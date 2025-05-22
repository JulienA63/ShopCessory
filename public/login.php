<?php

session_start();
require_once '../config/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Tous les champs sont obligatoires.";
    } else {
        // Vérifie si l’utilisateur existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $errors[] = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php foreach ($errors as $error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Nom d'utilisateur : <input type="text" name="username" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>

    <p><a href="register.php">Pas encore inscrit ? Crée un compte</a></p>

    <?php include '../src/View/footer.php'; ?>

</body>
</html>