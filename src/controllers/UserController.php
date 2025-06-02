<?php
// src/controllers/UserController.php

class UserController {

    /**
     * Affiche le formulaire d'inscription.
     * Récupère les données de formulaire précédemment soumises (via session) en cas d'erreur pour repopulation.
     */
    public function showRegistrationForm() {
        $pageTitle = "Inscription - SHOPCESSORY";
        
        // Récupérer les données du formulaire depuis la session si elles existent
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']); // Nettoyer la session après récupération

        // Les messages d'erreur flash (pour la validation par exemple) seront affichés par le layout
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);

        $contentView = APP_PATH . '/views/user/register.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite les données soumises par le formulaire d'inscription.
     * Utilise les flash messages pour tous les retours et redirige.
     */
    public function processRegistration() {
        $errors = [];
        // Récupérer les données POST
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : ''; 
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Validation des données
        if (empty($firstname)) { $errors['firstname'] = "Le prénom est requis."; }
        if (empty($lastname)) { $errors['lastname'] = "Le nom est requis."; }
        if (empty($username)) { $errors['username'] = "Le nom d'utilisateur est requis."; }
        
        if (empty($email)) { 
            $errors['email'] = "L'adresse e-mail est requise.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email_format'] = "Le format de l'adresse e-mail est invalide.";
        }

        if (empty($password)) { 
            $errors['password'] = "Le mot de passe est requis."; 
        } elseif (strlen($password) < 6) { 
            $errors['password_length'] = "Le mot de passe doit contenir au moins 6 caractères."; 
        }
        
        if (!empty($errors)) {
            foreach($errors as $errorMessage) { 
                set_flash_message('error', $errorMessage);
            }
            $_SESSION['form_data'] = $_POST; 
            header('Location: ' . INDEX_FILE_PATH . '?url=inscription'); 
            exit;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $pdo = getPDOConnection();

        if (!$pdo) { 
            set_flash_message('error', "Erreur critique : Impossible de se connecter à la base de données.");
            error_log("Erreur critique BDD (processRegistration)."); 
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=inscription');
            exit;
        }

        try {
            // Vérifier l'unicité du nom d'utilisateur
            $stmtCheckUser = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmtCheckUser->bindParam(':username', $username);
            $stmtCheckUser->execute();
            if ($stmtCheckUser->fetch()) {
                set_flash_message('error', "Ce nom d'utilisateur ('" . htmlspecialchars($username) . "') est déjà pris.");
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . INDEX_FILE_PATH . '?url=inscription');
                exit;
            }

            // Vérifier l'unicité de l'e-mail
            $stmtCheckEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmtCheckEmail->bindParam(':email', $email);
            $stmtCheckEmail->execute();
            if ($stmtCheckEmail->fetch()) {
                set_flash_message('error', "Cette adresse e-mail ('" . htmlspecialchars($email) . "') est déjà utilisée.");
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . INDEX_FILE_PATH . '?url=inscription');
                exit;
            }

            // Insérer le nouvel utilisateur avec l'email
            // Assure-toi que ta table 'users' a bien une colonne 'email' et 'role'
            $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) 
                    VALUES (:firstname, :lastname, :username, :email, :password, 'user')"; // 'role' par défaut 'user'
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':firstname', $firstname); 
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); 
            $stmt->bindParam(':email', $email); 
            $stmt->bindParam(':password', $hashedPassword);
            // $stmt->bindParam(':role', 'user'); // Si le DEFAULT 'user' n'est pas défini dans la BDD pour la colonne role
            $stmt->execute();

            set_flash_message('success', "Inscription réussie ! Bienvenue, " . htmlspecialchars($firstname) . ". Vous pouvez maintenant vous connecter.");
            header('Location: ' . INDEX_FILE_PATH . '?url=login'); 
            exit;

        } catch (PDOException $e) {
            set_flash_message('error', "Une erreur de base de données est survenue lors de la création de votre compte.");
            error_log("Erreur PDO (processRegistration) : " . $e->getMessage()); 
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=inscription');
            exit;
        }
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm() {
        $pageTitle = "Connexion - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);
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
                $_SESSION['form_data'] = $_POST; // Pour repopuler le champ username
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
                $expiresAt->add(new DateInterval('PT1H')); // Jeton valide pour 1 heure
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
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password'); // Redirige vers la même page pour afficher les messages
            exit;

        } catch (PDOException $e) {
            set_flash_message('error', 'Une erreur de base de données est survenue lors de la demande.');
            error_log("Erreur PDO (processForgotPasswordRequest) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        } catch (Exception $e) { // Pour random_bytes ou DateTime
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
                // Jeton valide et non expiré
                $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
                unset($_SESSION['form_data']);
                extract(['pageTitle' => $pageTitle, 'token' => $token, 'formData' => $formData]); // Passer le token original à la vue
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
        
        $_SESSION['form_data'] = $_POST; // Pour repopulation en cas d'erreur sur cette page

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

    /**
     * Affiche le formulaire d'édition du profil de l'utilisateur connecté.
     */
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
                header('Location: ' . INDEX_FILE_PATH . '?url=dashboard');
                exit;
            }
        } else {
            set_flash_message('error', "Erreur de connexion à la base de données.");
            header('Location: ' . INDEX_FILE_PATH . '?url=dashboard');
            exit;
        }

        if (!$currentUserData) {
            set_flash_message('error', "Impossible de charger les informations de votre profil.");
            header('Location: ' . INDEX_FILE_PATH . '?url=dashboard');
            exit;
        }
        
        if (empty($formData)) {
            $formData = $currentUserData;
        }
        
        extract(['pageTitle' => $pageTitle, 'currentUser' => $currentUserData, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/user/profile_edit_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la mise à jour du profil de l'utilisateur connecté.
     */
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
        if (!$pdo) {
            set_flash_message('error', "Erreur critique de connexion BDD.");
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form'); exit;
        }

        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :current_user_id");
            $stmtCheck->bindParam(':username', $username);
            $stmtCheck->bindParam(':current_user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) { $errors['username_exists'] = "Ce nom d'utilisateur est déjà pris."; }

            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :current_user_id");
            $stmtCheck->bindParam(':email', $email);
            $stmtCheck->bindParam(':current_user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) { $errors['email_exists'] = "Cet e-mail est déjà utilisé par un autre compte."; }
        } catch (PDOException $e) {
            $errors['db_check'] = "Erreur lors de la vérification des données.";
            error_log("ProcessProfileUpdate DB Check Error: " . $e->getMessage());
        }
        
        $updatePasswordSqlPart = "";
        $newHashedPassword = null;
        if (!empty($newPassword) || !empty($confirmPassword) || !empty($currentPassword)) {
            if (empty($currentPassword)) { $errors['current_password_required'] = "Mot de passe actuel requis pour changer.";} 
            else {
                $stmtUser = $pdo->prepare("SELECT password FROM users WHERE id = :id");
                $stmtUser->bindParam(':id', $userId, PDO::PARAM_INT); $stmtUser->execute();
                $userDb = $stmtUser->fetch(PDO::FETCH_ASSOC);
                if (!$userDb || !password_verify($currentPassword, $userDb['password'])) {
                    $errors['current_password_invalid'] = "Mot de passe actuel incorrect.";
                }
            }
            if (empty($newPassword)) { $errors['new_password_required'] = "Nouveau mot de passe requis."; }
            elseif (strlen($newPassword) < 6) { $errors['new_password_length'] = "Nouveau mdp (min 6 car.)."; }
            if ($newPassword !== $confirmPassword) { $errors['password_mismatch'] = "Les nouveaux mots de passe ne correspondent pas."; }
            
            if (empty($errors['current_password_required']) && empty($errors['current_password_invalid']) && 
                empty($errors['new_password_required']) && empty($errors['new_password_length']) && empty($errors['password_mismatch'])) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordSqlPart = ", password = :password";
            }
        }

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form');
            exit;
        }

        try {
            $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, email = :email" . $updatePasswordSqlPart . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':firstname', $firstname); $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); $stmt->bindParam(':email', $email);
            if ($newHashedPassword) {
                $stmt->bindParam(':password', $newHashedPassword);
            }
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if (isset($_SESSION['username']) && $_SESSION['username'] !== $username) {
                $_SESSION['username'] = $username; // Mettre à jour le nom d'utilisateur en session
            }
            set_flash_message('success', "Votre profil a été mis à jour avec succès.");
            unset($_SESSION['form_data']);
            header('Location: ' . INDEX_FILE_PATH . '?url=dashboard');
            exit;
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur BDD lors de la mise à jour du profil.");
            error_log("Erreur PDO (processProfileUpdate) : " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=profile_edit_form');
            exit;
        }
    }
}
?>