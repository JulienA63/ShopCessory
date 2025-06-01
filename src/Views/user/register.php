<?php
// src/views/user/register.php
// $formData est disponible pour repopulation
?>

<h2>Créer un compte</h2>

<form action="<?php echo INDEX_FILE_PATH; ?>?url=register_process" method="POST">
    <div>
        <label for="firstname">Prénom :</label>
        <input type="text" id="firstname" name="firstname" 
               value="<?php echo isset($formData['firstname']) ? htmlspecialchars($formData['firstname']) : ''; ?>" required>
    </div>
    <div>
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname" 
               value="<?php echo isset($formData['lastname']) ? htmlspecialchars($formData['lastname']) : ''; ?>" required>
    </div>
    <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" 
               value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>" required>
    </div>
    <div>
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" 
               value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <?php /* Pour des raisons de sécurité, on ne repopule pas le champ mot de passe. */ ?>
    </div>
    <div>
        <button type="submit" class="button-like">S'inscrire</button>
    </div>
</form>

<p>Déjà un compte ? <a href="<?php echo INDEX_FILE_PATH; ?>?url=login">Connectez-vous ici</a></p>