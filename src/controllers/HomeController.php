<?php
// src/controllers/HomeController.php

class HomeController {
    public function index() {
        $pageTitle = "Accueil - SHOPCESSORY";
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
                error_log("Erreur lors de la récupération des produits : " . $e->getMessage());
                set_flash_message('error', 'Impossible de charger les produits pour le moment.');
            }
        } else {
            error_log("Échec de la connexion à la base de données dans HomeController.");
            set_flash_message('error', 'Erreur de connexion à la base de données.');
        }

        $dataToView = [
            'pageTitle' => $pageTitle,
            'products' => $products
        ];
        extract($dataToView);

        $contentView = APP_PATH . '/views/home.php';
        require_once APP_PATH . '/views/layout.php';
    }
}
?>