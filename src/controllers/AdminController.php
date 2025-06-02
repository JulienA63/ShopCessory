<?php
// src/controllers/AdminController.php

class AdminController {

    /**
     * Constructeur pour s'assurer que toutes les actions de ce contrôleur
     * sont uniquement accessibles par un administrateur.
     */
    public function __construct() {
        ensureUserIsAdmin(); // Protège toutes les méthodes de ce contrôleur
    }

    /**
     * Affiche le tableau de bord principal de l'administration.
     */
    public function dashboard() {
        // ensureUserIsAdmin(); // Plus besoin ici si c'est dans le constructeur

        $pageTitle = "Tableau de Bord Admin - SHOPCESSORY";
        $contentView = APP_PATH . '/views/admin/dashboard.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Affiche la liste de tous les utilisateurs pour l'administration,
     * avec une option de recherche.
     */
    public function listUsers() {
        // ensureUserIsAdmin(); // Protégé par le constructeur
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
             set_flash_message('error', 'Erreur de connexion à la base de données.');
             error_log("Échec connexion BDD (AdminController::listUsers).");
        }
        
        extract(['pageTitle' => $pageTitle, 'users' => $users, 'searchTerm' => $searchTerm]);
        $contentView = APP_PATH . '/views/admin/users_list.php';
        require_once APP_PATH . '/views/layout.php';
    }
    
    /**
     * Affiche le formulaire pour créer ou modifier un utilisateur.
     * @param int|null $userId L'ID de l'utilisateur à modifier, ou null pour créer.
     */
    public function showUserForm($userId = null) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
        $pageTitle = $userId ? "Modifier Utilisateur - Admin" : "Créer Utilisateur - Admin";
        $userFromDb = null; // Utilisateur de la BDD pour l'édition
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : []; // Données pour repopulation
        unset($_SESSION['form_data']);
        $errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : []; // Erreurs spécifiques au champ (si on les implémente comme ça)
        unset($_SESSION['form_errors']);

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
                    error_log("Admin showUserForm (fetch user) Error: " . $e->getMessage());
                    set_flash_message('error', "Erreur BDD lors de la récupération de l'utilisateur.");
                }
            } else {
                 set_flash_message('error', "Connexion BDD impossible.");
            }

            if (!$userFromDb) {
                set_flash_message('error', "Utilisateur ID " . $userId . " non trouvé pour modification.");
                header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list');
                exit;
            }
            // Si pas de données de formulaire en session (pas de redirection après erreur de validation), utiliser les données de la BDD
            if (empty($formData) && $userFromDb) {
                $formData = $userFromDb;
            }
            if($userFromDb) { // S'assurer que le titre reflète l'édition si l'utilisateur est trouvé
                 $pageTitle = "Modifier : " . htmlspecialchars($userFromDb['username']);
            }
        }
        
        $formActionUrl = INDEX_FILE_PATH . '?url=admin_user_process_form' . ($userId ? '&id=' . $userId : '');
        $availableRoles = ['user', 'admin'];

        // 'user' est passé pour le contexte d'édition (savoir si on est en édition ou création dans la vue),
        // 'formData' est utilisé pour remplir les champs du formulaire.
        extract(['pageTitle' => $pageTitle, 'user' => $userFromDb, 'formData' => $formData, 'availableRoles' => $availableRoles, 'formActionUrl' => $formActionUrl, 'errors' => $errors]);
        $contentView = APP_PATH . '/views/admin/user_form.php';
        require_once APP_PATH . '/views/layout.php';
    }
    
    /**
     * Traite la soumission du formulaire de création ou de modification d'utilisateur par l'admin.
     */
    public function processUserForm($userId = null) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
        $errors = [];
        $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : 'user';

        // Validation des données
        if (empty($firstname)) { $errors['firstname'] = "Le prénom est requis."; }
        if (empty($lastname)) { $errors['lastname'] = "Le nom est requis."; }
        if (empty($username)) { $errors['username'] = "Le nom d'utilisateur est requis."; }
        if (empty($email)) { $errors['email'] = "L'e-mail est requis."; } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email_format'] = "Format d'e-mail invalide."; }
        if ($userId === null && empty($password)) { // Mot de passe requis seulement en création
            $errors['password'] = "Le mot de passe est requis pour un nouvel utilisateur."; 
        }
        if (!empty($password) && strlen($password) < 6) { 
            $errors['password_length'] = "Le mot de passe doit contenir au moins 6 caractères.";
        }
        if (!in_array($role, ['user', 'admin'])) { $errors['role'] = "Rôle invalide."; }

        $pdo = getPDOConnection();
        if (!$pdo) { 
            $errors['db_critical'] = "Erreur critique de connexion à la base de données."; 
            error_log("Erreur critique BDD (Admin processUserForm).");
        } else {
            // Vérifier unicité username
            if (empty($errors['username'])) {
                $sqlCheckUser = "SELECT id FROM users WHERE username = :username";
                $paramsCheckUser = [':username' => $username];
                if ($userId !== null) { 
                    $sqlCheckUser .= " AND id != :id";
                    $paramsCheckUser[':id'] = (int)$userId;
                }
                try {
                    $stmtCheckUser = $pdo->prepare($sqlCheckUser);
                    $stmtCheckUser->execute($paramsCheckUser);
                    if ($stmtCheckUser->fetch()) { $errors['username_exists'] = "Ce nom d'utilisateur est déjà pris."; }
                } catch (PDOException $e) { $errors['db_check'] = "Erreur vérification username."; error_log("Admin UserForm (check username) Error: " . $e->getMessage());}
            }
            // Vérifier unicité email
            if (empty($errors['email']) && empty($errors['email_format'])) {
                $sqlCheckEmail = "SELECT id FROM users WHERE email = :email";
                $paramsCheckEmail = [':email' => $email];
                if ($userId !== null) {
                    $sqlCheckEmail .= " AND id != :id";
                    $paramsCheckEmail[':id'] = (int)$userId;
                }
                try {
                    $stmtCheckEmail = $pdo->prepare($sqlCheckEmail);
                    $stmtCheckEmail->execute($paramsCheckEmail);
                    if ($stmtCheckEmail->fetch()) { $errors['email_exists'] = "Cet e-mail est déjà utilisé."; }
                } catch (PDOException $e) { $errors['db_check'] = "Erreur vérification e-mail."; error_log("Admin UserForm (check email) Error: " . $e->getMessage());}
            }
        }
        
        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH . '?url=admin_user_edit_form&id=' . $userId : INDEX_FILE_PATH . '?url=admin_user_create_form';
            header('Location: ' . $redirectUrl); 
            exit;
        }
        
        // Si pas d'erreurs, procéder à l'insertion ou à la mise à jour
        try {
            if ($userId === null) { // Création
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES (:firstname, :lastname, :username, :email, :password, :role)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':password', $hashedPassword);
            } else { // Modification
                $userId = (int)$userId;
                if (!empty($password)) { // Mettre à jour le mot de passe seulement s'il est fourni
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, email = :email, password = :password, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':password', $hashedPassword);
                } else { // Ne pas mettre à jour le mot de passe
                    $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, username = :username, email = :email, role = :role WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                }
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            }
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            set_flash_message('success', 'Utilisateur ' . ($userId ? 'mis à jour' : 'créé') . ' avec succès.');
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list');
            exit;
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur de base de données lors de la sauvegarde de l'utilisateur.");
            error_log("Admin processUserForm (DB save) Error: " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $redirectUrl = $userId ? INDEX_FILE_PATH . '?url=admin_user_edit_form&id=' . $userId : INDEX_FILE_PATH . '?url=admin_user_create_form';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Supprime un utilisateur.
     */
    public function deleteUser($userId) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
        $userId = (int)$userId;
        if ($userId <= 0) { 
            set_flash_message('error', "ID utilisateur invalide pour la suppression.");
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list'); 
            exit; 
        }
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) { 
            set_flash_message('error', "Vous ne pouvez pas supprimer votre propre compte administrateur.");
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
            // ON DELETE CASCADE dans la table products s'occupe des annonces liées
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                set_flash_message('success', "Utilisateur (ID: $userId) supprimé avec succès.");
            } else {
                set_flash_message('error', "Utilisateur (ID: $userId) non trouvé ou déjà supprimé.");
            }
        } catch (PDOException $e) {
            set_flash_message('error', "Erreur de base de données lors de la suppression de l'utilisateur.");
            error_log("Admin deleteUser Error for ID $userId: " . $e->getMessage());
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_users_list');
        exit;
    }
    
    /**
     * Affiche la liste de tous les produits pour l'administration.
     */
    public function listAllProducts() {
        // ensureUserIsAdmin(); // Protégé par le constructeur
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
            error_log("Échec connexion BDD (AdminController::listAllProducts).");
            set_flash_message('error', 'Erreur de connexion à la base de données.');
        }
        $pageTitle = "Gestion de Toutes les Annonces - Admin";
        extract(['products' => $products, 'pageTitle' => $pageTitle]);
        $contentView = APP_PATH . '/views/admin/products_list.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Supprime un produit par l'administrateur.
     */
    public function deleteProductByAdmin($productId) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
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
            // Récupérer le chemin de l'image avant de supprimer pour pouvoir effacer le fichier
            $sqlSelect = "SELECT image_path FROM products WHERE id = :id";
            $stmtSelect = $pdo->prepare($sqlSelect);
            $stmtSelect->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmtSelect->execute();
            $productImage = $stmtSelect->fetchColumn();

            // Supprimer l'enregistrement du produit
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
                set_flash_message('success', 'Annonce (ID: ' . $productId . ') supprimée avec succès.');
            } else {
                set_flash_message('error', 'Annonce (ID: ' . $productId . ') non trouvée ou déjà supprimée.');
            }
        } catch (PDOException $e) {
            set_flash_message('error', 'Erreur de base de données lors de la suppression de l\'annonce.');
            error_log("Admin deleteProductByAdmin Error for ID $productId: " . $e->getMessage());
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=admin_products_list');
        exit;
    }

    /**
     * Affiche le formulaire pour modifier une annonce par l'admin.
     */
    public function showProductEditForm($productId) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
        $productId = (int)$productId;
        $productToEdit = null; // Renommé pour clarté, sera utilisé pour pré-remplir le formulaire
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
            if(empty($formData)) { // Si pas de données de formulaire en session (pas d'erreur précédente), utiliser les données BDD
                $formData = $productToEdit;
            }
        } elseif(!empty($errors)) { 
            $pageTitle = "Erreur - Modification Annonce";
        }
        
        $formActionUrl = INDEX_FILE_PATH . '?url=admin_product_edit_process&id=' . $productId;
        
        extract([
            'productToEdit' => $productToEdit, // L'objet produit original de la BDD (ou null)
            'formData'      => $formData,      // Les données pour remplir le formulaire (peut être POST ou BDD)
            'pageTitle'     => $pageTitle, 
            'formActionUrl' => $formActionUrl, 
            'errors'        => $errors
        ]);
        $contentView = APP_PATH . '/views/admin/product_form.php'; // Vue dédiée
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la soumission du formulaire de modification d'annonce par l'admin.
     */
    public function processProductEdit($productId) {
        // ensureUserIsAdmin(); // Protégé par le constructeur
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
            $_SESSION['form_data'] = $_POST; // Sauvegarder les données pour repopulation
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId);
            exit;
        }

        // Récupérer l'image actuelle pour la gestion de suppression/remplacement
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

        $newImageNameForDb = $currentImagePath; // Par défaut, on garde l'ancienne image

        // Validation
        if (empty($title)) { $errors['title'] = "Le titre de l'annonce est requis."; }
        if (empty($price)) { $errors['price'] = "Le prix est requis."; } 
        elseif (!is_numeric($price) || $price < 0) { $errors['price_invalid'] = "Le prix doit être un nombre positif."; }

        // Gestion de la nouvelle image
        if ($delete_image_checkbox) {
            if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) {
                unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath);
            }
            $newImageNameForDb = null; // L'image est supprimée
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['product_image']; $imageTmpName = $image['tmp_name']; $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)); 
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExt, $allowedExtensions)) {
                if ($imageSize < 2000000) { // 2MB
                    // Supprimer l'ancienne image physique si elle existe
                    if (!empty($currentImagePath) && file_exists(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath)) {
                        unlink(PRODUCT_IMAGE_UPLOAD_DIR . $currentImagePath);
                    }
                    $newImageNameForDb = uniqid('', true) . "." . $imageExt; 
                    $imageDestination = PRODUCT_IMAGE_UPLOAD_DIR . $newImageNameForDb;
                    if (!move_uploaded_file($imageTmpName, $imageDestination)) { 
                        $errors['image_upload'] = "Erreur lors du déplacement de la nouvelle image.";
                        $newImageNameForDb = $currentImagePath; // En cas d'échec d'upload, on remet l'ancienne (si delete_image n'était pas coché)
                    }
                } else { $errors['image_size'] = "La nouvelle image est trop volumineuse (max 2MB)."; }
            } else { $errors['image_type'] = "Type de fichier non autorisé pour la nouvelle image."; }
        }
        
        if (!empty($errors)) { 
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST; 
            header('Location: ' . INDEX_FILE_PATH . '?url=admin_product_edit_form&id=' . $productId); 
            exit;
        }
        
        // Mise à jour en base de données
        try {
            $sql = "UPDATE products SET title = :title, description = :description, price = :price, image_path = :image_path WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':title', $title); 
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price); 
            $stmt->bindParam(':image_path', $newImageNameForDb); 
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT); 
            $stmt->execute();

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