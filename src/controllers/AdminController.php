<?php
// src/controllers/AdminController.php

class AdminController {

    public function dashboard() {
        ensureUserIsAdmin();
        $pageTitle = "Administration - SHOPCESSORY";
        $contentView = APP_PATH . '/views/admin/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function listUsers() {
        ensureUserIsAdmin();
        $users = [];
        $pdo = getPDOConnection();
        if ($pdo) {
            try {
                $sql = "SELECT id, firstname, lastname, username, role, created_at FROM users ORDER BY created_at DESC";
                $stmt = $pdo->query($sql);
                if ($stmt) { $users = $stmt->fetchAll(PDO::FETCH_ASSOC); }
            } catch (PDOException $e) { error_log("Admin listUsers Error: " . $e->getMessage()); }
        }
        $pageTitle = "Gestion des Utilisateurs - Admin";
        extract(['users' => $users, 'pageTitle' => $pageTitle]);
        $contentView = APP_PATH . '/views/admin/users_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function showUserForm($userId = null) {
        ensureUserIsAdmin(); $user = null; $formActionUrl = INDEX_FILE_PATH . '?url=admin_user_process_form';
        $pageTitle = "Créer un utilisateur - Admin";
        if ($userId) { $userId = (int)$userId; $pdo = getPDOConnection();
            if ($pdo) { try { $stmt = $pdo->prepare("SELECT id, firstname, lastname, username, role FROM users WHERE id = :id");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT); $stmt->execute(); $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) { error_log("Admin showUserForm Error: " . $e->getMessage()); } }
            if (!$user) { echo "Utilisateur non trouvé."; header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list&error=notfound'); exit; }
            $pageTitle = "Modifier : " . htmlspecialchars($user['username']); $formActionUrl .= '&id=' . $userId;
        }
        $availableRoles = ['user', 'admin'];
        extract(['user' => $user, 'pageTitle' => $pageTitle, 'formActionUrl' => $formActionUrl, 'availableRoles' => $availableRoles]);
        $contentView = APP_PATH . '/views/admin/user_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    public function processUserForm($userId = null) {
        ensureUserIsAdmin(); $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : ''; $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : ''; $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'user';
        if (empty($firstname)) { $errors['firstname'] = "Prénom requis."; } if (empty($lastname)) { $errors['lastname'] = "Nom requis."; }
        if (empty($username)) { $errors['username'] = "Nom d'utilisateur requis."; } if (empty($role) || !in_array($role, ['user', 'admin'])) { $errors['role'] = "Rôle invalide."; }
        if ($userId === null) { if (empty($password)) { $errors['password'] = "Mot de passe requis."; } elseif (strlen($password) < 6) { $errors['password_length'] = "Mdp 6 caractères min."; }
        } elseif (!empty($password) && strlen($password) < 6) { $errors['password_length'] = "Nouveau mdp 6 caractères min."; }
        if (!empty($errors)) { 
            $pageTitle = $userId ? "Modifier l'utilisateur" : "Créer un utilisateur";
            $formActionUrl = INDEX_FILE_PATH . '?url=admin_user_process_form' . ($userId ? '&id='.$userId : '');
            $availableRoles = ['user', 'admin']; $user = $_POST; if ($userId) $user['id'] = $userId;
            extract(['user' => $user, 'pageTitle' => $pageTitle, 'formActionUrl' => $formActionUrl, 'availableRoles' => $availableRoles, 'errors' => $errors]);
            $contentView = APP_PATH . '/views/admin/user_form.php'; require_once APP_PATH . '/views/layout.php'; exit;
        }
        $pdo = getPDOConnection(); if (!$pdo) { /* ... error ... */ exit; }
        try { 
            if ($userId === null) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (firstname, lastname, username, password, role) VALUES (:firstname, :lastname, :username, :password, :role)";
                $stmt = $pdo->prepare($sql); $stmt->bindParam(':password', $hashedPassword);
            } else { $userId = (int)$userId;
                if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, password = :password, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql); $stmt->bindParam(':password', $hashedPassword);
                } else { $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql); }
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT); }
            $stmt->bindParam(':firstname', $firstname); $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); $stmt->bindParam(':role', $role); $stmt->execute();
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list&status=' . ($userId ? 'updated' : 'created')); exit;
        } catch (PDOException $e) { 
            $pageTitle = $userId ? "Modifier l'utilisateur" : "Créer un utilisateur";
            $formActionUrl = INDEX_FILE_PATH . '?url=admin_user_process_form' . ($userId ? '&id='.$userId : '');
            $availableRoles = ['user', 'admin']; $user = $_POST; if ($userId) $user['id'] = $userId;
            $errors['db_error'] = ($e->getCode() == 23000) ? "Ce nom d'utilisateur est déjà pris." : "Erreur base de données.";
            extract(['user' => $user, 'pageTitle' => $pageTitle, 'formActionUrl' => $formActionUrl, 'availableRoles' => $availableRoles, 'errors' => $errors]);
            $contentView = APP_PATH . '/views/admin/user_form.php'; require_once APP_PATH . '/views/layout.php'; exit;
        }
    }

    public function deleteUser($userId) { /* ... (code existant) ... */ }


    public function listAllProducts() {
        echo "DEBUG: Point 1 - Début de listAllProducts<br>"; //exit; // Décommente pour tester ce point

        ensureUserIsAdmin();
        echo "DEBUG: Point 2 - Après ensureUserIsAdmin<br>"; //exit; // Décommente pour tester ce point
        
        $products = [];
        $pdo = getPDOConnection();
        echo "DEBUG: Point 3 - Après getPDOConnection. PDO est " . ($pdo ? "connecté" : "NON connecté") . "<br>"; //exit;

        if ($pdo) {
            echo "DEBUG: Point 4 - Dans le bloc if(\$pdo)<br>"; //exit;
            try {
                $sql = "SELECT p.id, p.title, p.price, p.created_at, u.username AS seller_username, p.image_path 
                        FROM products p
                        JOIN users u ON p.user_id = u.id
                        ORDER BY p.created_at DESC";
                echo "DEBUG: Point 5 - Avant pdo->query()<br>"; //exit;
                $stmt = $pdo->query($sql);
                echo "DEBUG: Point 6 - Après pdo->query(). Statement est " . ($stmt ? "OK" : "FALSE/Erreur") . "<br>"; //exit;
                
                if ($stmt) {
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "DEBUG: Point 7 - Après fetchAll(). Nombre de produits: " . count($products) . "<br>"; //exit;
                } else {
                     echo "DEBUG: Point 7.1 - La requête a échoué, \$stmt est false.<br>"; //exit;
                }
            } catch (PDOException $e) {
                echo "DEBUG: ERREUR PDO - " . $e->getMessage() . "<br>"; //exit;
                error_log("Admin listAllProducts Error: " . $e->getMessage());
            }
        } else {
            echo "DEBUG: Point 4.1 - Connexion PDO échouée.<br>"; //exit;
            error_log("Échec de la connexion à la base de données dans AdminController::listAllProducts.");
        }

        $pageTitle = "Gestion de toutes les Annonces - Admin";
        echo "DEBUG: Point 8 - Avant extract(). pageTitle: " . htmlspecialchars($pageTitle) . "<br>"; //exit;
        
        $dataToView = ['products' => $products, 'pageTitle' => $pageTitle];
        extract($dataToView);
        echo "DEBUG: Point 9 - Après extract().<br>"; //exit;

        $contentView = APP_PATH . '/views/admin/products_list.php';
        echo "DEBUG: Point 10 - \$contentView est: " . htmlspecialchars($contentView) . ". Existence: " . (file_exists($contentView) ? "Oui" : "NON") . "<br>"; //exit;
        
        require_once APP_PATH . '/views/layout.php';
        echo "DEBUG: Point 11 - Après require_once layout.php (ne devrait pas s'afficher si le layout s'affiche correctement)<br>";
        exit; // Ajout d'un exit final ici pour s'assurer que rien d'autre n'est exécuté par erreur après cette méthode.
    }

    public function deleteProductByAdmin($productId) { /* ... (code existant) ... */ }
    public function showProductEditForm($productId) { /* ... (code existant) ... */ }
    public function processProductEdit($productId) { /* ... (code existant) ... */ }
}
?>