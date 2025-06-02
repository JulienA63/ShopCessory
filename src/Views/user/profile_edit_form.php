<?php
// src/views/user/profile_edit_form.php
// Variables attendues : $pageTitle, $currentUser (données actuelles de l'utilisateur loggué), $formData (pour repopulation)
?>
<h2>Modifier mon profil</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=profile_update_process" method="POST">
    <div>
        <label for="firstname">Prénom :</label>
        <input type="text" id="firstname" name="firstname" 
               value="<?php echo htmlspecialchars(isset($formData['firstname']) ? $formData['firstname'] : ($currentUser['firstname'] ?? '')); ?>" required>
    </div>
    <div>
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname" 
               value="<?php echo htmlspecialchars(isset($formData['lastname']) ? $formData['lastname'] : ($currentUser['lastname'] ?? '')); ?>" required>
    </div>
    <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" 
               value="<?php echo htmlspecialchars(isset($formData['username']) ? $formData['username'] : ($currentUser['username'] ?? '')); ?>" required>
    </div>
    <div>
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" 
               value="<?php echo htmlspecialchars(isset($formData['email']) ? $formData['email'] : ($currentUser['email'] ?? '')); ?>" required>
    </div>
    <hr>
    <p>Pour changer votre mot de passe, veuillez remplir les champs ci-dessous. Sinon, laissez-les vides.</p>
    <div>
        <label for="current_password">Mot de passe actuel (requis UNIQUEMENT si vous changez de mot de passe) :</label>
        <input type="password" id="current_password" name="current_password">
    </div>
    <div>
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password">
    </div>
    <div>
        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password">
    </div>
    <div>
        <button type="submit" class="button-like">Mettre à jour mon profil</button>
    </div>
</form>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=dashboard" class="button-back">&laquo; Retour à Mon Compte</a></p>