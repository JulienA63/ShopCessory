<?php include 'partials/header.php'; ?>
<h2>Inscription</h2>
<form method="POST" action="../Controllers/register_handler.php">
    <input type="text" name="username" required placeholder="Nom d'utilisateur"><br>
    <input type="password" name="password" required placeholder="Mot de passe"><br>
    <button type="submit">S'inscrire</button>
</form>
<?php include 'partials/footer.php'; ?>
