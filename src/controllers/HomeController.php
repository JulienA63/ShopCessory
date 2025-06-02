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
                        ORDER BY p.created_at DESC 
                        LIMIT 10"; // Limiter le nombre de produits sur l'accueil par exemple
                $stmt = $pdo->query($sql);
                if ($stmt) {
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                error_log("Erreur de récupération des produits pour l'accueil: " . $e->getMessage());
                set_flash_message('error', 'Impossible de charger les articles récents pour le moment.');
            }
        } else {
            error_log("Échec de la connexion à la base de données dans HomeController.");
            set_flash_message('error', 'Erreur de connexion à la base de données.');
        }
        
        extract(['pageTitle' => $pageTitle, 'products' => $products]);
        $contentView = APP_PATH . '/views/home.php';
        require_once APP_PATH . '/views/layout.php';
    }
}
?>