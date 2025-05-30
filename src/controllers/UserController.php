<?php
// src/controllers/UserController.php

class UserController {

    public function showRegistrationForm() {
        $pageTitle = "Inscription - SHOPCESSORY";
        $contentView = APP_PATH . '/views/user/register.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processRegistration() {
        // ... (Code existant pour l'inscription)
        // Assurez-vous que cette méthode se termine par un exit; si elle affiche du contenu directement
        $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($firstname)) { $errors['firstname'] = "Le prénom est requis."; }
        if (empty($lastname)) { $errors['lastname'] = "Le nom est requis."; }
        if (empty($username)) { $errors['username'] = "Le nom d'utilisateur est requis."; }
        if (empty($password)) { $errors['password'] = "Le mot de passe est requis."; }
        elseif (strlen($password) < 6) { $errors['password_length'] = "Le mot de passe doit contenir au moins 6 caractères."; }

        if (!empty($errors)) {
            echo "<h1>Erreurs de validation :</h1><ul>"; foreach ($errors as $error) { echo "<li>" . htmlspecialchars($error) . "</li>"; } echo "</ul>";
            echo '<p><a href="' . INDEX_FILE_PATH . '?url=inscription">Retour au formulaire</a></p>';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $pdo = getPDOConnection();
            if (!$pdo) { echo "Erreur critique BDD (processRegistration)."; error_log("Erreur critique BDD (processRegistration)."); exit; }
            try {
                $sql = "INSERT INTO users (firstname, lastname, username, password) VALUES (:firstname, :lastname, :username, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':firstname', $firstname); $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':username', $username); $stmt->bindParam(':password', $hashedPassword);
                $stmt->execute();
                echo "<h1>Inscription réussie !</h1><p>Bienvenue, " . htmlspecialchars($firstname) . " !</p>";
                echo '<p><a href="' . INDEX_FILE_PATH . '?url=login">Se connecter</a></p>';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { echo "<h1>Erreur d'inscription</h1><p>Nom d'utilisateur ('" . htmlspecialchars($username) . "') déjà pris.</p>"; }
                else { echo "<h1>Erreur d'inscription</h1><p>Erreur BDD.</p>"; error_log("Erreur PDO (processRegistration) : " . $e->getMessage()); }
                echo '<p><a href="' . INDEX_FILE_PATH . '?url=inscription">Retour</a></p>';
            }
        }
        exit;
    }

    public function showLoginForm() {
        $pageTitle = "Connexion - SHOPCESSORY";
        $contentView = APP_PATH . '/views/user/login.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processLogin() {
        $errors = [];
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($username)) { $errors['username'] = "Nom d'utilisateur requis."; }
        if (empty($password)) { $errors['password'] = "Mot de passe requis."; }

        if (!empty($errors)) {
            echo "<h1>Erreurs :</h1><ul>"; foreach ($errors as $e) { echo "<li>".htmlspecialchars($e)."</li>"; } echo "</ul>";
            echo '<p><a href="'.INDEX_FILE_PATH.'?url=login">Retour</a></p>';
            exit;
        }

        $pdo = getPDOConnection();
        if (!$pdo) { echo "Erreur critique BDD (processLogin)."; error_log("Erreur critique BDD (processLogin)."); exit; }
        try {
            // Récupérer le rôle en plus des autres informations
            $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role']; // Stocker le rôle de l'utilisateur en session

                // Rediriger vers le dashboard admin si admin, sinon dashboard normal ou accueil
                if ($user['role'] === 'admin') {
                    header('Location: ' . INDEX_FILE_PATH . '?url=admin_dashboard&login_status=success');
                } else {
                    header('Location: ' . INDEX_FILE_PATH . '?url=dashboard&login_status=success');
                }
                exit;
            } else {
                echo "<h1>Erreur de connexion</h1><p>Nom d'utilisateur ou mot de passe incorrect.</p>";
                echo '<p><a href="'.INDEX_FILE_PATH.'?url=login">Retour</a></p>';
            }
        } catch (PDOException $e) {
            echo "<h1>Erreur Technique</h1><p>Erreur BDD.</p>";
            error_log("Erreur PDO (processLogin) : " . $e->getMessage());
            echo '<p><a href="'.INDEX_FILE_PATH.'?url=login">Retour</a></p>';
        }
        exit;
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header('Location: ' . INDEX_FILE_PATH . '?url=accueil&logout_status=success');
        exit;
    }

    public function showDashboard() {
        ensureUserIsLoggedIn();
        $pageTitle = "Mon Tableau de Bord - SHOPCESSORY";
        $contentView = APP_PATH . '/views/user/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }
}
?>