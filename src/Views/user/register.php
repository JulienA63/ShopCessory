<?php
// src/views/user/register.php
?>

<h2>Créer un compte</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=register_process" method="POST">
    <div>
        <label for="firstname">Prénom :</label>
        <input type="text" id="firstname" name="firstname" required>
    </div>
    <div>
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname" required>
    </div>
    <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <button type="submit">S'inscrire</button>
    </div>
</form>

<p>Déjà un compte ? <a href="<?php echo INDEX_FILE_PATH; ?>?url=login">Connectez-vous ici</a></p>