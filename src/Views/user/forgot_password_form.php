<?php
// src/views/user/forgot_password_form.php
// $formData est disponible pour repopulation
?>

<h2>Mot de passe oublié</h2>
<p>Veuillez entrer votre adresse e-mail. Si un compte est associé à cet e-mail, nous vous enverrons (simulerons l'envoi) un lien pour réinitialiser votre mot de passe.</p>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=forgot_password_request" method="POST">
    <div>
        <label for="email">Votre adresse e-mail :</label>
        <input type="email" id="email" name="email" 
               value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>" 
               required style="width: 300px;">
    </div>
    <div>
        <button type="submit" class="button-like">Envoyer la demande</button>
    </div>
</form>

<p><a href="<?php echo INDEX_FILE_PATH; ?>?url=login">Retour à la connexion</a></p>