<?php

class UserController {

    public function showRegistrationForm() {
        $pageTitle = "Inscription - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/register.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processRegistration() {
        $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($firstname)) { $errors['firstname'] = "Le prénom est requis."; }
        if (empty($lastname)) { $errors['lastname'] = "Le nom est requis."; }
        if (empty($username)) { $errors['username'] = "Le nom d'utilisateur est requis."; }
        if (empty($email)) { $errors['email'] = "L'adresse e-mail est requise.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email_format'] = "Le format de l'adresse e-mail est invalide.";}
        if (empty($password)) { $errors['password'] = "Le mot de passe est requis."; } 
        elseif (strlen($password) < 6) { $errors['password_length'] = "Le mot de passe doit contenir au moins 6 caractères."; }

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', "Erreur critique BDD."); $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); exit;
        }
        try {
            $stmtCheckUser = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmtCheckUser->bindParam(':username', $username); $stmtCheckUser->execute();
            if ($stmtCheckUser->fetch()) {
                set_flash_message('error', "Nom d'utilisateur ('" . htmlspecialchars($username) . "') déjà pris.");
                $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); exit;
            }
            $stmtCheckEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmtCheckEmail->bindParam(':email', $email); $stmtCheckEmail->execute();
            if ($stmtCheckEmail->fetch()) {
                set_flash_message('error', "Adresse e-mail ('" . htmlspecialchars($email) . "') déjà utilisée.");
                $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); exit;
            }
            $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES (:firstname, :lastname, :username, :email, :password, 'user')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':firstname', $firstname); $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); $stmt->bindParam(':email', $email); 
            $stmt->bindParam(':password', $hashedPassword); $stmt->execute();
            set_flash_message('success', "Inscription réussie ! Vous pouvez vous connecter.");
            header('Location: ' . INDEX_FILE_PATH . '?url=login'); exit;
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur BDD lors de l'inscription.");
            error_log("PDO Error (processRegistration) : " . $e->getMessage()); 
            $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); exit;
        }
    }

    public function showLoginForm() {
        $pageTitle = "Connexion - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; unset($_SESSION['form_data']);
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/login.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processLogin() {
        $errors = [];
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($username)) { $errors[] = "Le nom d'utilisateur est requis."; }
        if (empty($password)) { $errors[] = "Le mot de passe est requis."; }

        if (!empty($errors)) {
            foreach($errors as $error) { set_flash_message('error', $error); }
            $_SESSION['form_data'] = $_POST; 
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }

        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', "Erreur critique de connexion BDD.");
            error_log("Erreur critique BDD (processLogin)."); 
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit; 
        }
        try {
            $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                set_flash_message('success', 'Connexion réussie ! Bienvenue, ' . htmlspecialchars($user['username']) . '.');
                if ($user['role'] === 'admin') {
                    header('Location: ' . INDEX_FILE_PATH . '?url=admin_dashboard');
                } else {
                    header('Location: ' . INDEX_FILE_PATH . '?url=dashboard');
                }
                exit;
            } else {
                set_flash_message('error', 'Nom d\'utilisateur ou mot de passe incorrect.');
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . INDEX_FILE_PATH . '?url=login');
                exit;
            }
        } catch (PDOException $e) {
            set_flash_message('error', 'Une erreur technique est survenue lors de la connexion.');
            error_log("Erreur PDO (processLogin) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        set_flash_message('success', 'Vous avez été déconnecté avec succès.');
        header('Location: ' . INDEX_FILE_PATH . '?url=accueil');
        exit;
    }

    public function showDashboard() {
        ensureUserIsLoggedIn();
        $pageTitle = "Mon Tableau de Bord - SHOPCESSORY";
        $contentView = APP_PATH . '/views/user/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function showProfileEditForm() {
        ensureUserIsLoggedIn();
        $pageTitle = "Modifier mon Profil - SHOPCESSORY";
        $userId = $_SESSION['user_id'];
        $currentUserData = null;
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);

        $pdo = getPDOConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, firstname, lastname, username, email FROM users WHERE id = :id");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $currentUserData = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                set_flash_message('error', "Erreur lors de la récupération de vos informations de profil.");
                error_log("ShowProfileEditForm Error: " . $e->getMessage());
                header('Location: ' . INDEX_FILE_PATH . '?url=dashboard'); exit;
            }
        } else {
            set_flash_message('error', "Erreur de connexion à la base de données.");
            header('Location: ' . INDEX_FILE_PATH . '?url=dashboard'); exit;
        }

        if (!$currentUserData) {
            set_flash_message('error', "Impossible de charger les informations de votre profil.");
            header('Location: ' . INDEX_FILE_PATH . '?url=dashboard'); exit;
        }
        
        if (empty($formData)) { $formData = $currentUserData; }
        
        extract(['pageTitle' => $pageTitle, 'currentUser' => $currentUserData, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/profile_edit_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processProfileUpdate() {
        ensureUserIsLoggedIn();
        $userId = $_SESSION['user_id'];
        $errors = [];

        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($firstname)) { $errors['firstname'] = "Le prénom est requis."; }
        if (empty($lastname)) { $errors['lastname'] = "Le nom est requis."; }
        if (empty($username)) { $errors['username'] = "Le nom d'utilisateur est requis."; }
        if (empty($email)) { $errors['email'] = "L'e-mail est requis."; } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email_format'] = "Format d'e-mail invalide."; }

        $pdo = getPDOConnection();
        if (!$pdo) { set_flash_message('error', "Erreur critique BDD."); $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form'); exit;}

        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :current_user_id");
            $stmtCheck->bindParam(':username', $username); $stmtCheck->bindParam(':current_user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) { $errors['username_exists'] = "Ce nom d'utilisateur est déjà pris."; }

            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :current_user_id");
            $stmtCheck->bindParam(':email', $email); $stmtCheck->bindParam(':current_user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) { $errors['email_exists'] = "Cet e-mail est déjà utilisé par un autre compte."; }
        } catch (PDOException $e) { $errors['db_check'] = "Erreur vérification données."; error_log("ProcessProfileUpdate DB Check Error: " . $e->getMessage()); }
        
        $updatePasswordSqlPart = ""; $newHashedPassword = null;
        if (!empty($newPassword) || !empty($confirmPassword) || !empty($currentPassword)) {
            if (empty($currentPassword)) { $errors['current_password_required'] = "Mot de passe actuel requis pour changer.";} 
            else {
                $stmtUser = $pdo->prepare("SELECT password FROM users WHERE id = :id");
                $stmtUser->bindParam(':id', $userId, PDO::PARAM_INT); $stmtUser->execute(); $userDb = $stmtUser->fetch(PDO::FETCH_ASSOC);
                if (!$userDb || !password_verify($currentPassword, $userDb['password'])) { $errors['current_password_invalid'] = "Mot de passe actuel incorrect."; }
            }
            if (empty($newPassword)) { $errors['new_password_required'] = "Nouveau mot de passe requis."; }
            elseif (strlen($newPassword) < 6) { $errors['new_password_length'] = "Nouveau mdp (min 6 car.)."; }
            if ($newPassword !== $confirmPassword) { $errors['password_mismatch'] = "Les nouveaux mots de passe ne correspondent pas."; }
            if (empty($errors['current_password_required']) && empty($errors['current_password_invalid']) && empty($errors['new_password_required']) && empty($errors['new_password_length']) && empty($errors['password_mismatch'])) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); $updatePasswordSqlPart = ", password = :password";
            }
        }

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form'); exit;
        }

        try {
            $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, email = :email" . $updatePasswordSqlPart . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':firstname', $firstname); $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); $stmt->bindParam(':email', $email);
            if ($newHashedPassword) { $stmt->bindParam(':password', $newHashedPassword); }
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT); $stmt->execute();
            if (isset($_SESSION['username']) && $_SESSION['username'] !== $username) { $_SESSION['username'] = $username; }
            set_flash_message('success', "Votre profil a été mis à jour avec succès.");
            unset($_SESSION['form_data']); header('Location: ' . INDEX_FILE_PATH . '?url=dashboard'); exit;
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur BDD lors de la mise à jour du profil.");
            error_log("Erreur PDO (processProfileUpdate) : " . $e->getMessage());
            $_SESSION['form_data'] = $_POST; header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form'); exit;
        }
    }
}
?>