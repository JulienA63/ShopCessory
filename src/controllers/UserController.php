<?php
// src/controllers/UserController.php

class UserController {

    /**
     * Affiche le formulaire d'inscription.
     * Récupère les données de formulaire précédemment soumises (via session) en cas d'erreur pour repopulation.
     */
    public function showRegistrationForm() {
        $pageTitle = "Inscription - SHOPCESSORY";
        
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']); // Nettoyer la session après récupération

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
        $email = isset($_POST['email']) ? trim($_POST['email']) : ''; // Email récupéré
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
            $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) 
                    VALUES (:firstname, :lastname, :username, :email, :password, 'user')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':firstname', $firstname); 
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); 
            $stmt->bindParam(':email', $email); 
            $stmt->bindParam(':password', $hashedPassword);
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

    public function processForgotPasswordRequest() {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $_SESSION['form_data'] = $_POST; // Pour repopulation en cas d'erreur

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
            // 1. Vérifier si l'email existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 2. Générer un jeton sécurisé
                $token = bin2hex(random_bytes(32)); // Jeton de 64 caractères hexadécimaux
                $tokenHash = hash('sha256', $token); // Hasher le jeton avant de le stocker

                // 3. Définir une date d'expiration (par exemple, 1 heure à partir de maintenant)
                $expiresAt = new DateTime();
                $expiresAt->add(new DateInterval('PT1H')); // PT1H signifie Période de Temps de 1 Heure
                $expiresAtFormatted = $expiresAt->format('Y-m-d H:i:s');

                // 4. Stocker le hash du jeton et sa date d'expiration dans la table users
                $stmtUpdate = $pdo->prepare("UPDATE users SET reset_token_hash = :token_hash, reset_token_expires_at = :expires_at WHERE id = :user_id");
                $stmtUpdate->bindParam(':token_hash', $tokenHash);
                $stmtUpdate->bindParam(':expires_at', $expiresAtFormatted);
                $stmtUpdate->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmtUpdate->execute();

                // 5. (Simulation) Afficher le lien de réinitialisation que l'utilisateur recevrait par e-mail
                // Dans une vraie application, on enverrait un e-mail ici.
                // Le lien pointera vers une nouvelle route que nous créerons : reset_password_form
                $resetLink = INDEX_FILE_PATH . '?url=reset_password_form&token=' . $token;

                set_flash_message('success', 'Si un compte avec cet e-mail existe, un lien de réinitialisation vous a été (simulé) envoyé.');
                // Pour le débogage, affichons le lien (à enlever en production si on envoyait vraiment des emails)
                set_flash_message('info', 'Pour tester, utilisez ce lien (normalement envoyé par e-mail) : <a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a>');
                
                unset($_SESSION['form_data']); // Nettoyer les données du formulaire en cas de succès
                header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password'); // Rediriger vers la même page pour afficher le message
                exit;

            } else {
                // Email non trouvé, mais on affiche le même message pour des raisons de sécurité
                // (pour ne pas révéler quels emails sont enregistrés ou non)
                set_flash_message('success', 'Si un compte avec cet e-mail existe, un lien de réinitialisation vous a été (simulé) envoyé.');
                unset($_SESSION['form_data']);
                header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
                exit;
            }

        } catch (PDOException $e) {
            set_flash_message('error', 'Une erreur de base de données est survenue. Veuillez réessayer.');
            error_log("Erreur PDO (processForgotPasswordRequest) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        } catch (Exception $e) { // Pour random_bytes ou DateTime
            set_flash_message('error', 'Une erreur système est survenue. Veuillez réessayer.');
            error_log("Erreur Générale (processForgotPasswordRequest) : " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=forgot_password');
            exit;
        }
    }

    // public function processForgotPasswordRequest() { /* Prochaine étape pour la réinitialisation */ }
    // public function showResetPasswordForm($token) { /* Prochaine étape pour la réinitialisation */ }
    // public function processResetPassword($token) { /* Prochaine étape pour la réinitialisation */ }
}
?>