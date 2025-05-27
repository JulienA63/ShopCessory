<?php include 'partials/header.php'; ?>
<h2>Connexion</h2>
<form method="POST" action="../Controllers/login_handler.php">
    <input type="text" name="username" required placeholder="Nom d'utilisateur"><br>
    <input type="password" name="password" required placeholder="Mot de passe"><br>
    <button type="submit">Se connecter</button>
</form>
<?php include 'partials/footer.php'; ?>
