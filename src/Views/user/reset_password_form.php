<?php
// src/views/user/reset_password_form.php
// $pageTitle et $token (le jeton original de l'URL) sont disponibles.
// $formData pour la repopulation en cas d'erreur.
?>

<h2>Réinitialiser votre mot de passe</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=reset_password_process" method="POST">
    <input type="hidden" name="token" value="<?php echo isset($token) ? htmlspecialchars($token) : ''; ?>">

    <div>
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password" required>
    </div>
    <div>
        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <div>
        <button type="submit" class="button-like">Réinitialiser le mot de passe</button>
    </div>
</form>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=login">Retour à la connexion</a></p>