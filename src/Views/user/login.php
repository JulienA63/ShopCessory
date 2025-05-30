<?php
// src/views/user/login.php
// Vue pour le formulaire de connexion
?>

<h2>Connexion</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=login_process" method="POST"> <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <button type="submit">Se connecter</button>
    </div>
</form>

<p>Pas encore de compte ? <a href="<?php echo INDEX_FILE_PATH; ?>?url=inscription">Inscrivez-vous ici</a></p>
<p><a href="#">Mot de passe oublié ?</a></p>