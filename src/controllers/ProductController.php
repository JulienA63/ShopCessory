<?php
// src/controllers/ProductController.php

class ProductController {

    /**
     * Affiche le formulaire pour ajouter un nouveau produit.
     */
    public function showAddForm() {
        ensureUserIsLoggedIn();
        $pageTitle = "Vendre un article - SHOPCESSORY";
        $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
        unset($_SESSION['form_data']);
        extract(['pageTitle' => $pageTitle, 'formData' => $formData]);
        $contentView = APP_PATH . '/views/product/add_form.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la soumission du formulaire d'ajout de produit.
     */
    public function processAddProduct() {
        ensureUserIsLoggedIn();
        $errors = [];
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? trim($_POST['price']) : '';
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $imageNameForDb = null;

        if (empty($title)) { $errors['title'] = "Le titre est requis."; }
        if (empty($price)) { $errors['price'] = "Le prix est requis."; } 
        elseif (!is_numeric($price) || $price < 0) { $errors['price_invalid'] = "Le prix doit être un nombre positif."; }
        if (empty($userId)) { $errors['auth'] = "Utilisateur non identifié. Veuillez vous reconnecter."; }

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['product_image']; $imageTmpName = $image['tmp_name']; $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExt, $allowedExtensions)) {
                if ($imageSize < 2000000) { // 2MB
                    $imageNameForDb = uniqid('', true) . "." . $imageExt;
                    $uploadDir = PRODUCT_IMAGE_UPLOAD_DIR; $imageDestination = $uploadDir . $imageNameForDb;
                    if (!is_dir($uploadDir)) { $errors['image_dir_missing'] = "Erreur serveur: Dossier d'upload manquant."; error_log("Dossier d'upload manquant: " . $uploadDir); }
                    elseif (!is_writable($uploadDir)) { $errors['image_dir_unwritable'] = "Erreur serveur: Dossier d'upload non accessible."; error_log("Dossier d'upload non accessible: " . $uploadDir); }
                    elseif (!move_uploaded_file($imageTmpName, $imageDestination)) { $errors['image_upload'] = "Erreur lors de l'enregistrement de l'image."; $imageNameForDb = null; }
                } else { $errors['image_size'] = "L'image est trop volumineuse (max 2MB)."; }
            } else { $errors['image_type'] = "Type de fichier non autorisé (jpg, jpeg, png, gif)."; }
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['image_generic'] = "Erreur d'upload image (Code PHP: " . $_FILES['product_image']['error'] . ").";
        }

        if (!empty($errors)) {
            foreach($errors as $errorMsg) { set_flash_message('error', $errorMsg); }
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=product_add');
            exit;
        }
        
        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', "Erreur critique : Impossible de se connecter à la base de données.");
            error_log("Erreur critique de connexion DB dans processAddProduct."); 
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=product_add');
            exit; 
        }
        try {
            $sql = "INSERT INTO products (user_id, title, description, price, image_path) VALUES (:user_id, :title, :description, :price, :image_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId); $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description); $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_path', $imageNameForDb); $stmt->execute();
            $newProductId = $pdo->lastInsertId();
            set_flash_message('success', "Produit \"" . htmlspecialchars($title) . "\" ajouté avec succès !");
            header('Location: ' . INDEX_FILE_PATH . '?url=product_detail&id=' . $newProductId); 
            exit;
        } catch (PDOException $e) {
            set_flash_message('error', "Une erreur de base de données est survenue lors de l'ajout du produit.");
            error_log("Erreur PDO lors de l'ajout de produit (processAddProduct) : " . $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . INDEX_FILE_PATH . '?url=product_add');
            exit;
        }
    }

    /**
     * Affiche la page de détail d'un produit spécifique.
     */
    public function showProductDetail($productId) {
        if (empty($productId) || !filter_var($productId, FILTER_VALIDATE_INT) || $productId <= 0) {
            set_flash_message('error', 'ID de produit non valide.');
            header('Location: ' . INDEX_FILE_PATH . '?url=accueil');
            exit;
        }
        $product = null; 
        $pdo = getPDOConnection();
        if ($pdo) {
            try {
                $sql = "SELECT p.*, u.username AS seller_username 
                        FROM products p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) { 
                error_log("Erreur récup produit ID $productId : " . $e->getMessage());
                set_flash_message('error', 'Erreur lors de la récupération des détails du produit.');
            }
        } else { 
            error_log("Échec connexion BDD (showProductDetail).");
            set_flash_message('error', 'Erreur de connexion à la base de données.');
        }

        if (!$product) {
            if(!isset($_SESSION['flash_messages'])) { 
                 set_flash_message('error', 'Produit non trouvé.');
            }
            header('Location: ' . INDEX_FILE_PATH . '?url=accueil');
            exit;
        }
        
        $pageTitle = htmlspecialchars($product['title']) . " - SHOPCESSORY";
        extract(['product' => $product, 'pageTitle' => $pageTitle]); 
        $contentView = APP_PATH . '/views/product/detail.php';
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Traite la suppression d'un produit par son propriétaire.
     */
    public function processProductDelete($productId) {
        ensureUserIsLoggedIn();
        $productId = (int)$productId;
        if (empty($productId) || $productId <= 0) { 
            set_flash_message('error', 'ID de produit invalide pour la suppression.');
            header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : INDEX_FILE_PATH . '?url=dashboard'));
            exit;
        }
        $pdo = getPDOConnection();
        if (!$pdo) { 
            set_flash_message('error', 'Erreur de connexion à la base de données.');
            header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : INDEX_FILE_PATH . '?url=dashboard'));
            exit;
        }
        try {
            $sqlSelect = "SELECT user_id, image_path FROM products WHERE id = :id";
            $stmtSelect = $pdo->prepare($sqlSelect);
            $stmtSelect->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmtSelect->execute();
            $product = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                set_flash_message('error', 'Produit non trouvé pour la suppression.');
            } elseif ($product['user_id'] != $_SESSION['user_id']) {
                // Seul un admin peut supprimer un produit qui ne lui appartient pas via AdminController.
                // Ici, on vérifie que l'utilisateur est bien le propriétaire.
                set_flash_message('error', 'Accès non autorisé. Vous ne pouvez pas supprimer cette annonce.');
            } else {
                $sqlDelete = "DELETE FROM products WHERE id = :id AND user_id = :user_id";
                $stmtDelete = $pdo->prepare($sqlDelete);
                $stmtDelete->bindParam(':id', $productId, PDO::PARAM_INT);
                $stmtDelete->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                
                if ($stmtDelete->execute() && $stmtDelete->rowCount() > 0) {
                    if (!empty($product['image_path'])) {
                        $imageFileToDelete = PRODUCT_IMAGE_UPLOAD_DIR . $product['image_path'];
                        if (file_exists($imageFileToDelete)) { 
                            if (!unlink($imageFileToDelete)) { error_log("Échec suppression image : " . $imageFileToDelete); }
                        }
                    }
                    set_flash_message('success', 'Annonce supprimée avec succès.');
                } else {
                    set_flash_message('error', 'La suppression de l\'annonce a échoué.');
                }
            }
        } catch (PDOException $e) { 
            set_flash_message('error', 'Erreur technique lors de la suppression.');
            error_log("Erreur PDO (processProductDelete) : " . $e->getMessage());
        }
        header('Location: ' . INDEX_FILE_PATH . '?url=my_products'); // Rediriger vers la liste des annonces de l'utilisateur
        exit;
    }

    /**
     * Affiche la liste de tous les produits pour le public.
     */
    public function listAllPublicProducts() {
        $pageTitle = "Tous nos produits - SHOPCESSORY";
        $products = [];
        $pdo = getPDOConnection();

        if ($pdo) {
            try {
                $sql = "SELECT p.id, p.user_id, p.title, p.description, p.price, p.image_path, p.created_at, u.username AS seller_username 
                        FROM products p
                        JOIN users u ON p.user_id = u.id
                        ORDER BY p.created_at DESC";
                $stmt = $pdo->query($sql);
                if ($stmt) {
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                error_log("Erreur listAllPublicProducts: " . $e->getMessage());
                set_flash_message('error', 'Impossible de charger les produits.');
            }
        } else {
            set_flash_message('error', 'Erreur de connexion BDD.');
        }
        extract(['pageTitle' => $pageTitle, 'products' => $products]);
        $contentView = APP_PATH . '/views/product/products_list_public.php'; // Vue dédiée
        require_once APP_PATH . '/views/layout.php';
    }

    /**
     * Affiche la liste des produits postés par l'utilisateur connecté ("Mes Annonces").
     */
    public function listMyProducts() {
        ensureUserIsLoggedIn(); 

        $pageTitle = "Mes Annonces - SHOPCESSORY";
        $myProducts = [];
        $userId = $_SESSION['user_id'];
        $pdo = getPDOConnection();

        if ($pdo) {
            try {
                $sql = "SELECT id, title, description, price, image_path, created_at 
                        FROM products 
                        WHERE user_id = :user_id 
                        ORDER BY created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $myProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erreur listMyProducts pour user $userId: " . $e->getMessage());
                set_flash_message('error', 'Impossible de charger vos annonces.');
            }
        } else {
            set_flash_message('error', 'Erreur de connexion BDD.');
        }
        extract(['pageTitle' => $pageTitle, 'myProducts' => $myProducts]);
        $contentView = APP_PATH . '/views/product/my_products_list.php'; // Vue dédiée
        require_once APP_PATH . '/views/layout.php';
    }
}
?>