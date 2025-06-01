<?php
// src/views/user/login.php
// $formData est disponible pour repopuler le nom d'utilisateur si besoin
?>

<h2>Connexion</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=login_process" method="POST">
    <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" 
               value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <button type="submit" class="button-like">Se connecter</button>
    </div>
</form>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=forgot_password">Mot de passe oublié ?</a></p> {/* NOUVEAU LIEN */}
<p>Pas encore de compte ? <a href="<?php echo INDEX_FILE_PATH; ?>?url=inscription">Inscrivez-vous ici</a></p>