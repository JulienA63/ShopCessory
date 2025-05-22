<?php

session_start();
require_once '../config/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
        $errors[] = 'Veuillez remplir tous les champs.';
    } else {

        //Verif si le username est deja pris
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()){
            $errors[] = 'Ce nom d\'utilisateur est deja pris.';
        } else{

            //hachage du mdp
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            //Enregistrement dans la db
            $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstname, $lastname, $username, $hashedPassword]);
            
            //Redirection vers la page de login
            header('Location: login.php');
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>

    <?php foreach ($errors as $error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Prénom : <input type="text" name="firstname" required></label><br>
        <label>Nom : <input type="text" name="lastname" required></label><br>
        <label>Nom d'utilisateur : <input type="text" name="username" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">S'inscrire</button>
    </form>

    <p><a href="login.php">Déjà inscrit ? Se connecter</a></p>


    <?php include '../src/View/footer.php'; ?>
</body>
</html>