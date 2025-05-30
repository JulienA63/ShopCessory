<?php
// src/controllers/HomeController.php

class HomeController {
    public function index() {
        $pageTitle = "Accueil - SHOPCESSORY";
        
        $products = [];
        $pdo = getPDOConnection();

        if ($pdo) {
            try {
                // Assure-toi que image_path est bien dans la liste des colonnes sélectionnées
                $sql = "SELECT id, user_id, title, description, price, image_path, created_at FROM products ORDER BY created_at DESC";
                
                $stmt = $pdo->query($sql);
                
                if ($stmt) {
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la récupération des produits : " . $e->getMessage());
            }
        } else {
            error_log("Échec de la connexion à la base de données dans HomeController.");
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