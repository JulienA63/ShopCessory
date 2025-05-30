<?php
// src/controllers/ProductController.php

class ProductController {

    /**
     * Affiche le formulaire pour ajouter un nouveau produit.
     */
    public function showAddForm() {
        echo "--- DEBUG: EXÉCUTION DE ProductController::showAddForm() ---<br>";
        // Pour un test encore plus poussé, décommente la ligne exit; ci-dessous.
        // Si tu vois CE message et RIEN d'autre (pas de layout), alors le problème vient après cette méthode.
        // Si tu vois ce message ET le layout avec le mauvais contenu, le problème est plus subtil.
        // exit; 

        ensureUserIsLoggedIn(); 

        $pageTitle = "Vendre un article - SHOPCESSORY";
        $contentView = APP_PATH . '/views/product/add_form.php'; // Chemin vers le formulaire d'ajout
        
        // Message de débogage pour vérifier le chemin de la vue
        echo "--- DEBUG: \$contentView dans showAddForm() est réglé sur : " . htmlspecialchars($contentView) . " ---<br>";

        require_once APP_PATH . '/views/layout.php';
        // Normalement, aucun 'exit;' ici pour que le layout s'affiche.
    }

    /**
     * Traite la soumission du formulaire d'ajout de produit.
     */
    public function processAddProduct() {
        // ... (Contenu de processAddProduct comme fourni précédemment - pas de changement ici pour ce bug)
        // (Assure-toi que cette méthode se termine par un `exit;` si elle fait des `echo` directs)
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
        if (empty($userId)) { $errors['auth'] = "Utilisateur non identifié."; }

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['product_image']; $imageTmpName = $image['tmp_name']; $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExt, $allowedExtensions)) {
                if ($imageSize < 2000000) {
                    $imageNameForDb = uniqid('', true) . "." . $imageExt;
                    $uploadDir = PRODUCT_IMAGE_UPLOAD_DIR; $imageDestination = $uploadDir . $imageNameForDb;
                    if (!is_dir($uploadDir)) { $errors['image_dir_missing'] = "Erreur serveur: Dossier d'upload (" . htmlspecialchars($uploadDir) . ") manquant."; }
                    elseif (!is_writable($uploadDir)) { $errors['image_dir_unwritable'] = "Erreur serveur: Dossier d'upload (" . htmlspecialchars($uploadDir) . ") non accessible en écriture."; }
                    elseif (!move_uploaded_file($imageTmpName, $imageDestination)) { 
                        $errors['image_upload'] = "Erreur lors du déplacement du fichier image.";
                        $lastError = error_get_last();
                        if ($lastError && strpos($lastError['message'], 'move_uploaded_file') !== false) {
                            $errors['image_upload_php_error'] = "Détail PHP : " . htmlspecialchars($lastError['message']);
                        }
                    }
                } else { $errors['image_size'] = "L'image est trop volumineuse (max 2MB)."; }
            } else { $errors['image_type'] = "Type de fichier non autorisé (jpg, jpeg, png, gif)."; }
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['image_generic'] = "Erreur d'upload image (Code PHP: " . $_FILES['product_image']['error'] . ").";
        }

        if (!empty($errors)) {
            echo "<h1>Erreurs lors de l'ajout :</h1><ul>"; 
            foreach ($errors as $key => $error) { echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($error) . "</li>"; } 
            echo "</ul>";
            echo '<p><a href="'.INDEX_FILE_PATH.'?url=product_add">Retour au formulaire</a></p>';
        } else {
            $pdo = getPDOConnection();
            if (!$pdo) { echo "Erreur critique de connexion à la base de données."; error_log("Erreur DB (processAddProduct)"); exit; }
            try {
                $sql = "INSERT INTO products (user_id, title, description, price, image_path) VALUES (:user_id, :title, :description, :price, :image_path)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $userId); $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description); $stmt->bindParam(':price', $price);
                $stmt->bindParam(':image_path', $imageNameForDb); $stmt->execute();
                echo "<h1>Produit ajouté avec succès !</h1>";
                echo "<p>\"" . htmlspecialchars($title) . "\" a été mis en vente.</p>";
                if ($imageNameForDb) { echo "<p>Image sauvegardée : " . htmlspecialchars($imageNameForDb) . "</p>"; }
                echo '<p><a href="'.INDEX_FILE_PATH.'?url=accueil">Accueil</a> ou <a href="'.INDEX_FILE_PATH.'?url=product_add">Ajouter un autre</a></p>';
            } catch (PDOException $e) {
                echo "<h1>Erreur lors de l'ajout du produit</h1>";
                echo "<p>Une erreur de base de données est survenue. Veuillez réessayer plus tard.</p>";
                error_log("Erreur PDO (processAddProduct): " . $e->getMessage());
                echo '<p><a href="'.INDEX_FILE_PATH.'?url=product_add">Retour au formulaire</a></p>';
            }
        }
        exit;
    }

    /**
     * Affiche la page de détail d'un produit spécifique.
     */
    public function showProductDetail($productId) {
        echo "--- DEBUG: EXÉCUTION DE ProductController::showProductDetail() avec Product ID: " . htmlspecialchars((string)$productId) . " ---<br>";
        // exit;

        if (empty($productId) || !filter_var($productId, FILTER_VALIDATE_INT) || $productId <= 0) {
            http_response_code(404); 
            $pageTitle = "Erreur - Produit Invalide";
            $errorMessage = "L'identifiant du produit demandé n'est pas valide.";
            $contentView = APP_PATH . '/views/error/generic_error.php'; // Vue d'erreur générique à créer
            extract(['pageTitle' => $pageTitle, 'errorMessage' => $errorMessage]);
            require_once APP_PATH . '/views/layout.php';
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
                error_log("Erreur lors de la récupération du produit ID $productId : " . $e->getMessage());
            }
        } else {
            error_log("Échec de la connexion à la base de données dans ProductController::showProductDetail.");
        }

        if ($product) {
            $pageTitle = htmlspecialchars($product['title']) . " - SHOPCESSORY";
            $contentView = APP_PATH . '/views/product/detail.php';
            extract(['product' => $product, 'pageTitle' => $pageTitle]); 
        } else {
            http_response_code(404);
            $pageTitle = "Produit non trouvé - SHOPCESSORY";
            // Le fichier detail.php gérera l'affichage si $product est null
            $contentView = APP_PATH . '/views/product/detail.php'; 
            extract(['product' => null, 'pageTitle' => $pageTitle]);
        }
        require_once APP_PATH . '/views/layout.php';
        // Pas besoin de exit; ici car le layout est la dernière chose chargée.
    }

    /**
     * Traite la suppression d'un produit.
     */
    public function processProductDelete($productId) {
        // ... (Contenu de processProductDelete comme fourni précédemment)
        ensureUserIsLoggedIn();
        if (empty($productId) || !filter_var($productId, FILTER_VALIDATE_INT) || $productId <= 0) { /* ... error ... */ exit; }
        $pdo = getPDOConnection();
        if (!$pdo) { /* ... error ... */ exit; }
        try {
            $sqlSelect = "SELECT user_id, image_path FROM products WHERE id = :id";
            $stmtSelect = $pdo->prepare($sqlSelect); $stmtSelect->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmtSelect->execute(); $product = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if (!$product) { echo "<h1>Erreur</h1><p>Produit non trouvé.</p>"; }
            elseif ($product['user_id'] != $_SESSION['user_id']) { echo "<h1>Accès non autorisé</h1>"; }
            else {
                $sqlDelete = "DELETE FROM products WHERE id = :id AND user_id = :user_id";
                $stmtDelete = $pdo->prepare($sqlDelete);
                $stmtDelete->bindParam(':id', $productId, PDO::PARAM_INT); $stmtDelete->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                if ($stmtDelete->execute()) {
                    if (!empty($product['image_path'])) {
                        $imageFileToDelete = PRODUCT_IMAGE_UPLOAD_DIR . $product['image_path'];
                        if (file_exists($imageFileToDelete)) { if (!unlink($imageFileToDelete)) { error_log("Échec suppression image: " . $imageFileToDelete); } }
                    }
                    echo "<h1>Annonce supprimée</h1>";
                } else { echo "<h1>Erreur suppression</h1>"; }
            }
        } catch (PDOException $e) { echo "<h1>Erreur Technique</h1>"; error_log("PDO Error (processProductDelete): " . $e->getMessage()); }
        echo '<p><a href="'.INDEX_FILE_PATH.'?url=accueil">Retour</a></p>';
        exit;
    }
}
?>