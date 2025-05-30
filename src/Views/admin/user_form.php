<?php
// src/views/admin/user_form.php
// Variables disponibles :
// $pageTitle
// $user (null pour création, objet/array utilisateur pour édition)
// $formActionUrl (URL vers laquelle le formulaire doit soumettre)
// $availableRoles (array des rôles possibles)
// $errors (array des erreurs de validation, si on les passe à la vue)

$isEditMode = ($user !== null && isset($user['id']));
?>

<h2><?php echo htmlspecialchars($pageTitle); ?></h2>

<form action="<?php echo htmlspecialchars($formActionUrl); ?>" method="POST">
    <div>
        <label for="firstname">Prénom :</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($isEditMode ? $user['firstname'] : ''); ?>" required>
    </div>
    <div>
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($isEditMode ? $user['lastname'] : ''); ?>" required>
    </div>
    <div>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($isEditMode ? $user['username'] : ''); ?>" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" <?php if (!$isEditMode) echo 'required'; ?>>
        <?php if ($isEditMode): ?>
            <small>Laissez vide pour ne pas changer le mot de passe actuel.</small>
        <?php endif; ?>
    </div>
    <div>
        <label for="role">Rôle :</label>
        <select id="role" name="role" required>
            <?php foreach ($availableRoles as $roleOption): ?>
                <option value="<?php echo htmlspecialchars($roleOption); ?>" <?php if ($isEditMode && $user['role'] === $roleOption) echo 'selected'; elseif (!$isEditMode && $roleOption === 'user') echo 'selected'; ?>>
                    <?php echo htmlspecialchars(ucfirst($roleOption)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit" class="button-like"><?php echo $isEditMode ? 'Mettre à jour l\'utilisateur' : 'Créer l\'utilisateur'; ?></button>
    </div>
</form>

<p style="margin-top: 20px;"><a href="<?php echo INDEX_FILE_PATH; ?>?url=admin_users_list">&laquo; Retour à la liste des utilisateurs</a></p>