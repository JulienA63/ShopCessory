<?php

class AdminController {

    // --- Sécurité : accès admin obligatoire ---
    public function __construct() {
        ensureUserIsAdmin();
    }

    // --- DASHBOARD ADMIN ---
    public function dashboard() {
        $pageTitle = "Tableau de Bord Admin - SHOPCESSORY";
        $contentView = APP_PATH . '/views/admin/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    // =====================================================
    //           GESTION DES UTILISATEURS
    // =====================================================

    // 1. Lister les utilisateurs
    public function listUsers() {
        $pageTitle = "Gestion des Utilisateurs - Admin";
        $searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
        $users = [];
        $pdo = getPDOConnection();

        if ($pdo) {
            try {
                $sql = "SELECT id, firstname, lastname, username, email, role, created_at FROM users";
                $params = [];
                if (!empty($searchTerm)) {
                    $sql .= " WHERE username LIKE :searchTermUsername 
                              OR firstname LIKE :searchTermFirstname 
                              OR lastname LIKE :searchTermLastname
                              OR email LIKE :searchTermEmail"; 
                    $likeTerm = '%' . $searchTerm . '%';
                    $params[':searchTermUsername'] = $likeTerm;
                    $params[':searchTermFirstname'] = $likeTerm;
                    $params[':searchTermLastname'] = $likeTerm;
                    $params[':searchTermEmail'] = $likeTerm; 
                }
                $sql .= " ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                set_flash_message('error', "Erreur lors de la récupération des utilisateurs.");
                error_log("Admin listUsers Error: " . $e->getMessage());
            }
        } else {
            set_flash_message('error', 'Erreur de connexion BDD.');
            error_log("Échec connexion BDD (AdminController::listUsers).");
        }
        extract(['pageTitle' => $pageTitle, 'users' => $users, 'searchTerm' => $searchTerm]);
        $contentView = APP_PATH . '/views/admin/users_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    // 2. Afficher le formulaire de création ou d'édition utilisateur
    public function showUserForm($userId = null) {
        $pageTitle = $userId ? "Modifier Utilisateur" : "Créer Utilisateur";
        $userFromDb = null; 
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);
        $errors = [];

        if ($userId) {
            $userId = (int)$userId;
            $pdo = getPDOConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT id, firstname, lastname, username, email, role FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $userId, PDO::PARAM_INT); 
                    $stmt->execute();
                    $userFromDb = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) { 
                    error_log("Admin showUserForm Fetch Error: " . $e->getMessage()); 
                    $errors['db'] = "Erreur BDD."; 
                }
            } else { 
                $errors['db_conn'] = "Connexion BDD impossible."; 
            }
            if (!$userFromDb && empty($errors)) { 
                set_flash_message('error', "Utilisateur non trouvé."); 
                header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); exit; 
            }
            if ($userFromDb) { 
                $pageTitle = "Modifier: " . htmlspecialchars($userFromDb['username']); 
                if(empty($formData)) $formData = $userFromDb; 
            }
            else { $pageTitle = "Erreur - Utilisateur non trouvé"; }
        }
        $availableRoles = ['user', 'admin'];
        extract(['pageTitle' => $pageTitle, 'user' => $userFromDb, 'formData' => $formData, 'availableRoles' => $availableRoles, 'errors' => $errors]);
        $contentView = APP_PATH . '/views/admin/user_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    // 3. Traiter la soumission du formulaire (création ou édition)
    public function processUserForm($userId = null) {
        $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'user';

        // Validations essentielles
        if (empty($firstname)) { $errors[] = "Prénom requis."; }
        if (empty($lastname)) { $errors[] = "Nom requis."; }
        if (empty($username)) { $errors[] = "Nom d'utilisateur requis."; }
        if (empty($email)) { $errors[] = "E-mail requis."; } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Format e-mail invalide."; }
        if (!$userId && empty($password)) { $errors[] = "Mot de passe requis (création)."; }
        if (!empty($password) && strlen($password) < 6) { $errors[] = "Mot de passe (min 6 car.).";}
        if (!in_array($role, ['user', 'admin'])) { $errors[] = "Rôle invalide."; }

        $pdo = getPDOConnection();
        if (!$pdo) { $errors[] = "Erreur connexion BDD."; }
        else {
            // Unicité username et email
            $sqlCheck = "SELECT id FROM users WHERE (username = :username OR email = :email)" . ($userId ? " AND id != :id_user" : "");
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->bindParam(':username', $username);
            $stmtCheck->bindParam(':email', $email);
            if ($userId) $stmtCheck->bindParam(':id_user', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) { $errors[] = "Nom d'utilisateur ou e-mail déjà utilisé par un autre compte."; }
        }

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH.'?url=admin_user_edit_form&id='.$userId : INDEX_FILE_PATH.'?url=admin_user_create_form';
            header('Location: ' . $redirectUrl); exit;
        }

        try {
            if ($userId) { // Update
                $userId = (int)$userId;
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET firstname=:fn, lastname=:ln, username=:un, email=:em, password=:pw, role=:r WHERE id=:id";
                    $stmt = $pdo->prepare($sql); $stmt->bindParam(':pw', $hashedPassword);
                } else {
                    $sql = "UPDATE users SET firstname=:fn, lastname=:ln, username=:un, email=:em, role=:r WHERE id=:id";
                    $stmt = $pdo->prepare($sql);
                }
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            } else { // Create
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES (:fn, :ln, :un, :em, :pw, :r)";
                $stmt = $pdo->prepare($sql); $stmt->bindParam(':pw', $hashedPassword);
            }
            $stmt->bindParam(':fn', $firstname); $stmt->bindParam(':ln', $lastname);
            $stmt->bindParam(':un', $username); $stmt->bindParam(':em', $email); $stmt->bindParam(':r', $role);
            $stmt->execute();
            set_flash_message('success', "Utilisateur ".($userId ? "mis à jour" : "créé").".");
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur BDD: ".$e->getMessage());
            error_log("Admin User Save Error: " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH.'?url=admin_user_edit_form&id='.$userId : INDEX_FILE_PATH.'?url=admin_user_create_form';
            header('Location: ' . $redirectUrl); exit;
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); exit;
    }

    // 4. Supprimer un utilisateur
    public function deleteUser($userId) {
        $userId = (int)$userId;
        if ($userId <= 0 || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId)) {
            set_flash_message('error', $userId <= 0 ? "ID invalide." : "Suppression de soi-même interdite.");
            header('Location: '.INDEX_FILE_PATH.'?url=admin_users_list'); exit;
        }
        $pdo = getPDOConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT); 
            $stmt->execute();
            set_flash_message('success', $stmt->rowCount() ? "Utilisateur supprimé." : "Utilisateur non trouvé.");
        } catch (PDOException $e) { 
            set_flash_message('error', "Erreur BDD."); 
            error_log("Admin Delete User Error: ".$e->getMessage()); 
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); exit;
    }

    // =====================================================
    //           GESTION DES PRODUITS (Annonces)
    // =====================================================

    // 1. Lister les annonces (produits)
    public function listAllProducts() {
        $pageTitle = "Gestion Annonces - Admin";
        $products = []; $pdo = getPDOConnection();
        if ($pdo) { 
            try {
                $sql = "SELECT p.id, p.title, p.price, u.username AS seller_username, p.image_path FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
                $stmt = $pdo->query($sql); 
                if ($stmt) { $products = $stmt->fetchAll(PDO::FETCH_ASSOC); }
            } catch (PDOException $e) { 
                error_log("Admin listAllProducts Error: " . $e->getMessage()); 
                set_flash_message('error', 'Erreur récupération annonces.');
            }
        } else { 
            set_flash_message('error', 'Erreur connexion BDD.'); 
        }
        extract(['pageTitle' => $pageTitle, 'products' => $products]);
        $contentView = APP_PATH . '/views/admin/products_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    // 2. Formulaire édition annonce
    public function showProductEditForm($productId) {
        $pageTitle = "Modifier Annonce - Admin";
        $productToEdit = null; $errors = [];
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
        unset($_SESSION['form_data']);
        $productId = (int)$productId;
        if ($productId <= 0) { 
            set_flash_message('error', 'ID produit invalide.'); 
            header('Location: '.INDEX_FILE_PATH.'?url=admin_products_list'); exit;
        }
        
        $pdo = getPDOConnection();
        if ($pdo) { 
            try {
                $stmt = $pdo->prepare("SELECT id, title, description, price, image_path FROM products WHERE id = :id");
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT); 
                $stmt->execute();
                $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { 
                error_log("Admin showProductEditForm Error: ".$e->getMessage()); 
                $errors['db'] = "Erreur BDD.";
            }
        } else { $errors['db_conn'] = "Connexion BDD impossible.";}
        if (!$productToEdit && empty($errors)) { 
            $errors['not_found'] = "Produit non trouvé (ID: ".$productId.")."; 
        }

        if ($productToEdit) { 
            $pageTitle = "Modifier: " . htmlspecialchars($productToEdit['title']); 
            if(empty($formData)) $formData = $productToEdit; 
        }
        elseif(!empty($errors)) { $pageTitle = "Erreur Modification Annonce"; }
        
        $formActionUrl = INDEX_FILE_PATH . '?url=admin_product_edit_process&id=' . $productId;
        extract(['productToEdit' => $productToEdit, 'formData' => $formData, 'pageTitle' => $pageTitle, 'formActionUrl' => $formActionUrl, 'errors' => $errors]);
        $contentView = APP_PATH . '/views/admin/product_form.php'; // Vue pour le formulaire d'édition admin
        require_once APP_PATH . '/views/layout.php';
    }

    // 3. Traiter la soumission du formulaire d'édition annonce
    public function processProductEdit($productId) {
        $productId = (int)$productId; $errors = [];
        if ($productId <= 0) { 
            set_flash_message('error', 'ID produit invalide.'); 
            header('Location: '.INDEX_FILE_PATH.'?url=admin_products_list'); exit;
        }
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';
        $delete_image_checkbox = isset($_POST['delete_image']) && $_POST['delete_image'] == '1';

        if (empty($title)) { $errors[] = "Titre requis."; }
        if (empty($price)) { $errors[] = "Prix requis."; } 
        elseif (!is_numeric($price) || $price < 0) { $errors[] = "Prix invalide."; }

        $pdo = getPDOConnection();
        if (!$pdo) { $errors[] = "Erreur connexion BDD."; }
        
        $currentImagePath = null;
        if ($pdo) { // Récupérer l'image actuelle pour la gestion
            $stmtCurrent = $pdo->prepare("SELECT image_path FROM products WHERE id = :id");
            $stmtCurrent->bindParam(':id', $productId, PDO::PARAM_INT); 
            $stmtCurrent->execute();
            $currentProductData = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
            if (!$currentProductData) { $errors[] = "Produit original non trouvé pour la mise à jour."; }
            else { $currentImagePath = $currentProductData['image_path']; }
        }
        
        $newImageNameForDb = $currentImagePath; // Conserver l'ancienne par défaut

        if ($delete_image_checkbox) { // Si l'admin veut supprimer l'image
            if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) {
                if(!unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) $errors[] = "Erreur suppression ancienne image.";
            }
            $newImageNameForDb = null;
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) { // Si une nouvelle image est uploadée
            $image = $_FILES['product_image']; $imageTmpName = $image['tmp_name']; $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)); $allowedExts = ['jpg','jpeg','png','gif'];
            if (in_array($imageExt, $allowedExts)) { 
                if ($imageSize < 2000000) {
                    if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) { 
                        unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath); 
                    } // Supprimer l'ancienne
                    $newImageNameForDb = uniqid('', true) . "." . $imageExt;
                    if (!move_uploaded_file($imageTmpName, PRODUCT_IMAGE_UPLOAD_DIR . $newImageNameForDb)) {
                        $errors[] = "Erreur upload nouvelle image."; 
                        $newImageNameForDb = $currentImagePath; // Remettre l'ancienne si échec
                    }
                } else { $errors[] = "Nouvelle image trop volumineuse."; } 
            } else { $errors[] = "Type fichier nouvelle image invalide."; }
        }
        // Si $errors est toujours vide après la gestion d'image, on continue.

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId); exit;
        }

        try {
            $sql = "UPDATE products SET title = :title, description = :description, price = :price, image_path = :image_path WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $title); $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price); $stmt->bindParam(':image_path', $newImageNameForDb);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            set_flash_message('success', "Annonce mise à jour avec succès.");
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur BDD lors de la mise à jour de l'annonce.");
            error_log("Admin Product Edit Error: " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId); exit;
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); exit;
    }

    // 4. Supprimer une annonce
    public function deleteProductByAdmin($productId) {
        $productId = (int)$productId;
        if ($productId <= 0) { 
            set_flash_message('error', "ID produit invalide."); 
            header('Location: '.INDEX_FILE_PATH.'?url=admin_products_list'); exit; 
        }
        
        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', "Erreur connexion BDD."); 
            header('Location: '.INDEX_FILE_PATH.'?url=admin_products_list'); exit; 
        }
        try {
            $stmtSelect = $pdo->prepare("SELECT image_path FROM products WHERE id = :id");
            $stmtSelect->bindParam(':id', $productId, PDO::PARAM_INT); 
            $stmtSelect->execute();
            $imagePath = $stmtSelect->fetchColumn();

            $stmtDelete = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmtDelete->bindParam(':id', $productId, PDO::PARAM_INT);
            if ($stmtDelete->execute() && $stmtDelete->rowCount() > 0) {
                if (!empty($imagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $imagePath)) {
                    if(!unlink(PRODUCT_IMAGE_UPLOAD_DIR . $imagePath)) error_log("Erreur suppression image admin: ".$imagePath);
                }
                set_flash_message('success', "Annonce supprimée.");
            } else { 
                set_flash_message('error', "Annonce non trouvée ou déjà supprimée."); 
            }
        } catch (PDOException $e) { 
            set_flash_message('error', "Erreur BDD suppression annonce."); 
            error_log("Admin Delete Product Error: ".$e->getMessage()); 
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); exit;
    }

}

?>
