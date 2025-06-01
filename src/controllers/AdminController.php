<?php
// src/controllers/AdminController.php

class AdminController {

    /**
     * Affiche le tableau de bord principal de l'administration.
     */
    public function dashboard() {
        ensureUserIsAdmin(); 

        $pageTitle = "Administration - SHOPCESSORY";
        $contentView = APP_PATH . '/views/admin/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Affiche la liste de tous les utilisateurs pour l'administration,
     * avec une option de recherche.
     */
    public function listUsers() {
        ensureUserIsAdmin();
        $users = [];
        $searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
        
        $pdo = getPDOConnection();

        if ($pdo) {
            try {
                $sql = "SELECT id, firstname, lastname, username, role, created_at FROM users";
                $params = []; 

                if (!empty($searchTerm)) {
                    $sql .= " WHERE username LIKE :searchTermUsername 
                              OR firstname LIKE :searchTermFirstname 
                              OR lastname LIKE :searchTermLastname";
                    // Si tu avais un champ email et que tu voulais chercher dedans aussi :
                    // $sql .= " OR email LIKE :searchTermEmail"; 
                    
                    $likeTerm = '%' . $searchTerm . '%';
                    $params[':searchTermUsername'] = $likeTerm;
                    $params[':searchTermFirstname'] = $likeTerm;
                    $params[':searchTermLastname'] = $likeTerm;
                    // Si tu avais un champ email :
                    // $params[':searchTermEmail'] = $likeTerm;
                }
                $sql .= " ORDER BY created_at DESC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params); 
                
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log("Admin listUsers Error: " . $e->getMessage());
                set_flash_message('error', 'Erreur lors de la récupération des utilisateurs : ' . $e->getMessage());
            }
        } else {
            error_log("Échec connexion BDD dans AdminController::listUsers.");
            set_flash_message('error', 'Erreur de connexion à la base de données lors de la recherche d\'utilisateurs.');
        }

        $pageTitle = "Gestion des Utilisateurs - Admin";
        extract(['users' => $users, 'pageTitle' => $pageTitle, 'searchTerm' => $searchTerm]); 
        
        $contentView = APP_PATH . '/views/admin/users_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Affiche le formulaire pour créer ou modifier un utilisateur.
     * @param int|null $userId L'ID de l'utilisateur à modifier, ou null pour créer.
     */
    public function showUserForm($userId = null) {
        ensureUserIsAdmin(); 
        $user = null; 
        $formActionUrl = INDEX_FILE_PATH . '?url=admin_user_process_form';
        $pageTitle = "Créer un utilisateur - Admin";
        $errors = []; 
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
        unset($_SESSION['form_data']);

        if ($userId) { 
            $userId = (int)$userId; 
            $pdo = getPDOConnection();
            if ($pdo) { 
                try { 
                    $stmt = $pdo->prepare("SELECT id, firstname, lastname, username, role FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $userId, PDO::PARAM_INT); 
                    $stmt->execute(); 
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) { 
                    error_log("Admin showUserForm Error: " . $e->getMessage()); 
                    $errors['db'] = "Erreur lors de la récupération de l'utilisateur.";
                } 
            } else {
                 $errors['db_conn'] = "Connexion BDD impossible.";
            }
            if (!$user && empty($errors)) { 
                $errors['not_found'] = "Utilisateur non trouvé pour modification (ID: " . $userId . ").";
            }
            if ($user) { 
                 $pageTitle = "Modifier : " . htmlspecialchars($user['username']); 
                 $formActionUrl .= '&id=' . $userId;
                 if(empty($formData)) $formData = $user; 
            } else { 
                $pageTitle = "Erreur - Utilisateur non trouvé";
            }
        }
        $availableRoles = ['user', 'admin'];
        extract(['user' => $user, 'pageTitle' => $pageTitle, 'formActionUrl' => $formActionUrl, 'availableRoles' => $availableRoles, 'errors' => $errors, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/admin/user_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la soumission du formulaire de création ou de modification d'utilisateur.
     * @param int|null $userId L'ID de l'utilisateur à modifier, ou null pour créer.
     */
    public function processUserForm($userId = null) {
        ensureUserIsAdmin(); 
        $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : ''; 
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : ''; 
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'user';

        if (empty($firstname)) { $errors['firstname'] = "Prénom requis."; } 
        if (empty($lastname)) { $errors['lastname'] = "Nom requis."; }
        if (empty($username)) { $errors['username'] = "Nom d'utilisateur requis."; } 
        if (empty($role) || !in_array($role, ['user', 'admin'])) { $errors['role'] = "Rôle invalide."; }
        
        if ($userId === null) { 
            if (empty($password)) { $errors['password'] = "Mot de passe requis pour un nouvel utilisateur."; } 
            elseif (strlen($password) < 6) { $errors['password_length'] = "Mot de passe de 6 caractères minimum."; }
        } elseif (!empty($password) && strlen($password) < 6) { 
             $errors['password_length'] = "Nouveau mot de passe de 6 caractères minimum.";
        }
        
        $pdo = getPDOConnection();
        if (!$pdo) { $errors['db_critical'] = "Erreur critique de connexion BDD."; error_log("Erreur critique BDD (processUserForm)."); }

        if (empty($errors['username']) && $pdo) {
            $sqlCheckUser = "SELECT id FROM users WHERE username = :username";
            $paramsCheckUser = [':username' => $username];
            if ($userId !== null) { 
                $sqlCheckUser .= " AND id != :id";
                $paramsCheckUser[':id'] = (int)$userId;
            }
            try {
                $stmtCheckUser = $pdo->prepare($sqlCheckUser);
                $stmtCheckUser->execute($paramsCheckUser);
                if ($stmtCheckUser->fetch()) {
                    $errors['username_taken'] = "Ce nom d'utilisateur est déjà pris.";
                }
            } catch (PDOException $e) {
                $errors['db_check_user'] = "Erreur lors de la vérification du nom d'utilisateur.";
                error_log("Admin processUserForm (check username) Error: " . $e->getMessage());
            }
        }
        
        if (!empty($errors)) { 
            foreach($errors as $error) { set_flash_message('error', $error); }
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH . '?url=admin_user_edit_form&id='.$userId : INDEX_FILE_PATH . '?url=admin_user_create_form';
            header('Location: ' . $redirectUrl); 
            exit;
        }
        
        try { 
            if ($userId === null) { 
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (firstname, lastname, username, password, role) VALUES (:firstname, :lastname, :username, :password, :role)";
                $stmt = $pdo->prepare($sql); 
                $stmt->bindParam(':password', $hashedPassword);
            } else { 
                $userId = (int)$userId;
                if (!empty($password)) { 
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, password = :password, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql); 
                    $stmt->bindParam(':password', $hashedPassword);
                } else { 
                    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql); 
                }
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT); 
            }
            $stmt->bindParam(':firstname', $firstname); 
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username); 
            $stmt->bindParam(':role', $role); 
            $stmt->execute();
            set_flash_message('success', 'Utilisateur ' . ($userId ? 'mis à jour' : 'créé') . ' avec succès.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit;
        } catch (PDOException $e) { 
            set_flash_message('error', ($e->getCode() == 23000) ? "Ce nom d'utilisateur est déjà pris (Erreur BDD)." : "Erreur base de données lors de l'opération.");
            error_log("Admin processUserForm (final DB op) Error: " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH . '?url=admin_user_edit_form&id='.$userId : INDEX_FILE_PATH . '?url=admin_user_create_form';
            header('Location: ' . $redirectUrl); 
            exit;
        }
    }

    /**
     * Supprime un utilisateur.
     */
    public function deleteUser($userId) {
        ensureUserIsAdmin(); 
        $userId = (int)$userId;
        if (empty($userId) || $userId <= 0) { 
            set_flash_message('error', 'ID utilisateur invalide pour la suppression.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit; 
        }
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) { 
            set_flash_message('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit; 
        }
        $pdo = getPDOConnection(); 
        if (!$pdo) { 
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            error_log("Admin deleteUser Error: DB connection failed.");
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit; 
        }
        try { 
            $sql = "DELETE FROM users WHERE id = :id"; 
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT); 
            $stmt->execute();
            if ($stmt->rowCount() > 0) { 
                set_flash_message('success', 'Utilisateur supprimé avec succès.');
            } else { 
                set_flash_message('error', 'Utilisateur non trouvé ou déjà supprimé.');
            } 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit;
        } catch (PDOException $e) { 
            set_flash_message('error', 'Erreur de base de données lors de la suppression de l\'utilisateur.');
            error_log("Admin deleteUser Error: " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit; 
        }
    }

    /**
     * Affiche la liste de tous les produits pour l'administration.
     */
    public function listAllProducts() {
        ensureUserIsAdmin(); 
        $products = []; 
        $pdo = getPDOConnection();
        if ($pdo) { 
            try { 
                $sql = "SELECT p.id, p.title, p.price, p.created_at, u.username AS seller_username, p.image_path 
                        FROM products p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC";
                $stmt = $pdo->query($sql); 
                if ($stmt) { $products = $stmt->fetchAll(PDO::FETCH_ASSOC); }
            } catch (PDOException $e) { 
                error_log("Admin listAllProducts Error: " . $e->getMessage()); 
                set_flash_message('error', 'Erreur lors de la récupération des annonces.');
            }
        } else { 
            error_log("Échec de la connexion à la base de données dans AdminController::listAllProducts."); 
            set_flash_message('error', 'Erreur de connexion à la base de données.');
        }
        $pageTitle = "Gestion de toutes les Annonces - Admin";
        extract(['products' => $products, 'pageTitle' => $pageTitle]);
        $contentView = APP_PATH . '/views/admin/products_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Supprime un produit par l'administrateur.
     */
    public function deleteProductByAdmin($productId) {
        ensureUserIsAdmin(); 
        $productId = (int)$productId;
        if (empty($productId) || $productId <= 0) { 
            set_flash_message('error', 'ID de produit invalide pour la suppression.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit; 
        }
        $pdo = getPDOConnection(); 
        if (!$pdo) { 
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            error_log("Erreur critique BDD (deleteProductByAdmin)."); 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit; 
        }
        try {
            $sqlSelect = "SELECT image_path FROM products WHERE id = :id";
            $stmtSelect = $pdo->prepare($sqlSelect); 
            $stmtSelect->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmtSelect->execute(); 
            $productImage = $stmtSelect->fetchColumn();

            $sqlDelete = "DELETE FROM products WHERE id = :id";
            $stmtDelete = $pdo->prepare($sqlDelete); 
            $stmtDelete->bindParam(':id', $productId, PDO::PARAM_INT);
            
            if ($stmtDelete->execute() && $stmtDelete->rowCount() > 0) {
                if (!empty($productImage)) {
                    $imageFileToDelete = PRODUCT_IMAGE_UPLOAD_DIR . $productImage;
                    if (file_exists($imageFileToDelete)) { 
                        if (!unlink($imageFileToDelete)) { 
                            error_log("Admin: Échec de la suppression du fichier image : " . $imageFileToDelete); 
                        }
                    }
                }
                set_flash_message('success', 'Annonce supprimée avec succès.');
            } else { 
                set_flash_message('error', 'Annonce non trouvée ou déjà supprimée.');
            }
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit;
        } catch (PDOException $e) { 
            set_flash_message('error', 'Erreur de base de données lors de la suppression de l\'annonce.');
            error_log("Admin deleteProductByAdmin Error: " . $e->getMessage());
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit; 
        }
    }

    /**
     * Affiche le formulaire pour modifier une annonce par l'admin.
     */
    public function showProductEditForm($productId) {
        ensureUserIsAdmin();
        $productId = (int)$productId;
        $productToEdit = null;
        $errors = []; 
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; 
        unset($_SESSION['form_data']);

        if (empty($productId) || $productId <= 0) {
            set_flash_message('error', 'ID de produit invalide pour la modification.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list');
            exit;
        }

        $pdo = getPDOConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, user_id, title, description, price, image_path FROM products WHERE id = :id");
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmt->execute();
                $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Admin showProductEditForm Error: " . $e->getMessage());
                $errors['db'] = "Erreur lors de la récupération du produit.";
            }
        } else {
            $errors['db_conn'] = "Impossible de se connecter à la base de données.";
            error_log("Échec de la connexion BDD dans AdminController::showProductEditForm.");
        }

        if (!$productToEdit && empty($errors)) {
             $errors['not_found'] = "Produit non trouvé pour la modification (ID: " . htmlspecialchars((string)$productId) . ").";
        }

        $pageTitle = "Modifier l'Annonce (Admin)";
        if ($productToEdit) {
            $pageTitle = "Modifier : " . htmlspecialchars($productToEdit['title']) . " (Admin)";
            if(empty($formData)) $formData = $productToEdit; 
        } elseif(!empty($errors)) { 
            $pageTitle = "Erreur - Modification Annonce";
        }
        
        $formActionUrl = INDEX_FILE_PATH . '?url=admin_product_edit_process&id=' . $productId;
        
        extract([
            'productToEdit' => $productToEdit, 
            'pageTitle'     => $pageTitle, 
            'formActionUrl' => $formActionUrl, 
            'errors'        => $errors,
            'formData'      => $formData // Pour repopulation
        ]);
        $contentView = APP_PATH . '/views/admin/product_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la soumission du formulaire de modification d'annonce par l'admin.
     */
    public function processProductEdit($productId) {
        ensureUserIsAdmin(); 
        $productId = (int)$productId; 
        $errors = [];

        if (empty($productId)) { 
            set_flash_message('error', 'ID produit invalide pour le traitement.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit; 
        }
        
        $pdo = getPDOConnection(); 
        if (!$pdo) { 
            set_flash_message('error', 'Erreur critique de connexion BDD.');
            error_log("Erreur critique BDD (processProductEdit)."); 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId);
            exit;
        }

        $stmtCurrent = $pdo->prepare("SELECT image_path FROM products WHERE id = :id");
        $stmtCurrent->bindParam(':id', $productId, PDO::PARAM_INT); 
        $stmtCurrent->execute();
        $currentProductData = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

        if (!$currentProductData) {
            set_flash_message('error', 'Produit non trouvé pour la mise à jour.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list'); 
            exit;
        }
        $currentImagePath = $currentProductData['image_path'];
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : ''; 
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : ''; 
        $delete_image_checkbox = isset($_POST['delete_image']) && $_POST['delete_image'] == '1';

        $newImageNameForDb = $currentImagePath; 

        if (empty($title)) { $errors['title'] = "Le titre de l'annonce est requis."; }
        if (empty($price)) { $errors['price'] = "Le prix est requis."; } 
        elseif (!is_numeric($price) || $price < 0) { $errors['price_invalid'] = "Le prix doit être un nombre positif."; }

        if ($delete_image_checkbox) {
            if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) {
                unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath);
            }
            $newImageNameForDb = null;
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['product_image']; $imageTmpName = $image['tmp_name']; $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)); $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExt, $allowedExtensions)) {
                if ($imageSize < 2000000) { // 2MB
                    if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) {
                        unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath);
                    }
                    $newImageNameForDb = uniqid('', true) . "." . $imageExt; 
                    $imageDestination = PRODUCT_IMAGE_UPLOAD_DIR . $newImageNameForDb;
                    if (!move_uploaded_file($imageTmpName, $imageDestination)) { 
                        $errors['image_upload'] = "Erreur lors du déplacement de la nouvelle image.";
                        $newImageNameForDb = $currentImagePath; 
                    }
                } else { $errors['image_size'] = "La nouvelle image est trop volumineuse (max 2MB)."; }
            } else { $errors['image_type'] = "Type de fichier non autorisé pour la nouvelle image."; }
        }
        
        if (!empty($errors)) { 
            foreach($errors as $error) { set_flash_message('error', $error); }
            $_SESSION['form_data'] = $_POST; 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId); 
            exit;
        }
        
        try {
            $sql = "UPDATE products SET title = :title, description = :description, price = :price, image_path = :image_path WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $title); $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price); $stmt->bindParam(':image_path', $newImageNameForDb);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT); $stmt->execute();

            set_flash_message('success', 'Annonce mise à jour avec succès.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list');
            exit;
        } catch (PDOException $e) { 
            error_log("Admin processProductEdit Error: " . $e->getMessage());
            set_flash_message('error', "Erreur de base de données lors de la mise à jour de l'annonce.");
            $_SESSION['form_data'] = $_POST; 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId); 
            exit;
        }
    }
}
?>