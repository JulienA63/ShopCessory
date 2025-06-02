<?php
// src/controllers/UserController.php

class UserController {

    /**
     * Affiche le formulaire d'inscription.
     */
    public function showRegistrationForm() {
        $pageTitle = "Inscription - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/register.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite les données soumises par le formulaire d'inscription.
     */
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

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm() {
        $pageTitle = "Connexion - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; unset($_SESSION['form_data']);
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/login.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite les données soumises par le formulaire de connexion.
     */
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

    /**
     * Gère la déconnexion de l'utilisateur.
     */
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

    /**
     * Affiche le tableau de bord de l'utilisateur.
     */
    public function showDashboard() {
        ensureUserIsLoggedIn();
        $pageTitle = "Mon Tableau de Bord - SHOPCESSORY";
        $contentView = APP_PATH . '/views/user/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe.
     */
    public function showForgotPasswordForm() {
        $pageTitle = "Mot de passe oublié - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);

        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/forgot_password_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la soumission du formulaire de demande de réinitialisation de mot de passe.
     */
    public function processForgotPasswordRequest() {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('error', 'Veuillez fournir une adresse e-mail valide.');
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        }

        $pdo = getPDOConnection();
        if (!$pdo) {
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $messageToUser = 'Si un compte avec cet e-mail existe, un lien de réinitialisation (simulé) a été préparé.';

            if ($user) {
                $token = bin2hex(random_bytes(32)); 
                $tokenHash = hash('sha256', $token); 
                $expiresAt = new DateTime();
                $expiresAt->add(new DateInterval('PT1H')); 
                $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');

                $stmtUpdate = $pdo->prepare("UPDATE users SET reset_token_hash = :token_hash, reset_token_expires_at = :expires_at WHERE id = :user_id");
                $stmtUpdate->bindParam(':token_hash', $tokenHash);
                $stmtUpdate->bindParam(':expires_at', $expiresAtFormatted);
                $stmtUpdate->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmtUpdate->execute();

                $resetLink = INDEX_FILE_PATH . '?url=reset_password_form&token=' . $token;
                $linkHtml = '<a href="' . htmlspecialchars($resetLink) . '" class="button-like" style="background-color: #17a2b8; display:inline-block; margin-top:10px;">Utiliser ce lien de réinitialisation</a>';
                set_flash_message('info', 'Pour tester : ' . $linkHtml);
            }
            
            set_flash_message('success', $messageToUser);
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;

        } catch (PDOException $e) {
            set_flash_message('error', 'Une erreur de base de données est survenue lors de la demande.');
            error_log("Erreur PDO (processForgotPasswordRequest) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        } catch (Exception $e) { 
            set_flash_message('error', 'Une erreur système est survenue lors de la demande.');
            error_log("Erreur Générale (processForgotPasswordRequest) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        }
    }

    /**
     * Affiche le formulaire pour saisir un nouveau mot de passe, après vérification du jeton.
     * @param string $token Le jeton de réinitialisation reçu de l'URL.
     */
    public function showResetPasswordForm($token) {
        $pageTitle = "Réinitialiser le mot de passe - SHOPCESSORY";
        
        if (empty($token)) {
            set_flash_message('error', 'Jeton de réinitialisation manquant ou invalide.');
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }

        $tokenHash = hash('sha256', $token);
        $pdo = getPDOConnection();

        if (!$pdo) {
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id, reset_token_expires_at FROM users WHERE reset_token_hash = :token_hash");
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['reset_token_expires_at']) && new DateTime() < new DateTime($user['reset_token_expires_at'])) {
                $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
                unset($_SESSION['form_data']);
                extract(['pageTitle' => $pageTitle, 'token' => $token, 'formData' => $formData]); 
                $contentView = APP_PATH . '/views/user/reset_password_form.php';
                require_once APP_PATH . '/views/layout.php';
            } else {
                set_flash_message('error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.');
                header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
                exit;
            }
        } catch (PDOException $e) {
            set_flash_message('error', 'Erreur de base de données lors de la vérification du jeton.');
            error_log("Erreur PDO (showResetPasswordForm) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        } catch (Exception $e) { 
             set_flash_message('error', 'Erreur système lors de la vérification de la date du jeton.');
            error_log("Erreur DateTime (showResetPasswordForm) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }
    }

    /**
     * Traite la soumission du nouveau mot de passe.
     */
    public function processResetPassword() {
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        $_SESSION['form_data'] = $_POST; 

        if (empty($token)) {
            set_flash_message('error', 'Jeton de réinitialisation manquant. Impossible de traiter la demande.');
            header('Location: ' . INDEX_FILE_PATH . '?url=login'); 
            exit;
        }
        if (empty($newPassword) || empty($confirmPassword)) {
            set_flash_message('error', 'Veuillez saisir et confirmer votre nouveau mot de passe.');
            header('Location: ' . INDEX_FILE_PATH . '?url=reset_password_form&token=' . urlencode($token));
            exit;
        }
        if (strlen($newPassword) < 6) {
            set_flash_message('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            header('Location: ' . INDEX_FILE_PATH . '?url=reset_password_form&token=' . urlencode($token));
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            set_flash_message('error', 'Les mots de passe ne correspondent pas.');
            header('Location: ' . INDEX_FILE_PATH . '?url=reset_password_form&token=' . urlencode($token));
            exit;
        }

        $tokenHash = hash('sha256', $token);
        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            header('Location: ' . INDEX_FILE_PATH . '?url=reset_password_form&token=' . urlencode($token));
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id, reset_token_expires_at FROM users WHERE reset_token_hash = :token_hash");
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['reset_token_expires_at']) && new DateTime() < new DateTime($user['reset_token_expires_at'])) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmtUpdate = $pdo->prepare("UPDATE users SET password = :password, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = :user_id");
                $stmtUpdate->bindParam(':password', $newHashedPassword);
                $stmtUpdate->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmtUpdate->execute();

                set_flash_message('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
                unset($_SESSION['form_data']);
                header('Location: ' . INDEX_FILE_PATH . '?url=login');
                exit;
            } else {
                set_flash_message('error', 'Lien de réinitialisation invalide ou expiré. Veuillez refaire une demande.');
                unset($_SESSION['form_data']); 
                header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
                exit;
            }
        } catch (PDOException $e) {
            set_flash_message('error', 'Erreur de base de données lors de la réinitialisation.');
            error_log("Erreur PDO (processResetPassword) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=reset_password_form&token=' . urlencode($token));
            exit;
        } catch (Exception $e) { 
            set_flash_message('error', 'Erreur système lors de la vérification du jeton.');
            error_log("Erreur DateTime (processResetPassword) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=login');
            exit;
        }
    }
}
?>