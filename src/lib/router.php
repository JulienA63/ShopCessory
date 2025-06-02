<?php
// src/lib/router.php

function handleRoute($url) {
    if ($url === '' || $url === 'accueil') {
        $homeController = new HomeController();
        $homeController->index();
    } elseif ($url === 'inscription') {
        $userController = new UserController();
        $userController->showRegistrationForm();
    } elseif ($url === 'register_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController = new UserController();
        $userController->processRegistration();
    } elseif ($url === 'login') {
        $userController = new UserController();
        $userController->showLoginForm();
    } elseif ($url === 'login_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController = new UserController();
        $userController->processLogin();
    } elseif ($url === 'logout') {
        $userController = new UserController();
        $userController->logout();
    } elseif ($url === 'dashboard') {
        $userController = new UserController();
        $userController->showDashboard();
    } elseif ($url === 'forgot_password') { 
        $userController = new UserController();
        $userController->showForgotPasswordForm();
    } elseif ($url === 'forgot_password_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController = new UserController();
        $userController->processForgotPasswordRequest();
    } elseif ($url === 'reset_password_form') { 
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $userController = new UserController();
        $userController->showResetPasswordForm($token);
    } elseif ($url === 'reset_password_process' && $_SERVER['REQUEST_METHOD'] === 'POST') { 
        $userController = new UserController();
        $userController->processResetPassword();
    } elseif ($url === 'profile_edit_form') { // Afficher le formulaire d'édition de profil
        $userController = new UserController();
        $userController->showProfileEditForm();
    } elseif ($url === 'profile_update_process' && $_SERVER['REQUEST_METHOD'] === 'POST') { // Traiter la mise à jour du profil
        $userController = new UserController();
        $userController->processProfileUpdate();
    } elseif ($url === 'product_add') {
        $productController = new ProductController();
        $productController->showAddForm();
    } elseif ($url === 'product_create_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $productController = new ProductController();
        $productController->processAddProduct();
    } elseif ($url === 'product_detail') {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productController = new ProductController();
        $productController->showProductDetail($productId);
    } elseif ($url === 'product_delete') {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productController = new ProductController();
        $productController->processProductDelete($productId);
    } elseif ($url === 'products_list_public') { 
        $productController = new ProductController();
        $productController->listAllPublicProducts();
    } elseif ($url === 'my_products') { 
        $productController = new ProductController();
        $productController->listMyProducts();
    } elseif ($url === 'admin_dashboard') { 
        $adminController = new AdminController(); 
        $adminController->dashboard();
    } elseif ($url === 'admin_users_list') {
        $adminController = new AdminController();
        $adminController->listUsers();
    } elseif ($url === 'admin_user_create_form') {
        $adminController = new AdminController();
        $adminController->showUserForm(); 
    } elseif ($url === 'admin_user_edit_form') {
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $adminController = new AdminController();
        $adminController->showUserForm($userId); 
    } elseif ($url === 'admin_user_process_form' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : null; 
        $adminController = new AdminController();
        $adminController->processUserForm($userId);
    } elseif ($url === 'admin_user_delete') { 
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $adminController = new AdminController();
        $adminController->deleteUser($userId);
    } elseif ($url === 'admin_products_list') {
        $adminController = new AdminController();
        $adminController->listAllProducts();
    } elseif ($url === 'admin_product_delete') {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $adminController = new AdminController();
        $adminController->deleteProductByAdmin($productId);
    } elseif ($url === 'admin_product_edit_form') {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $adminController = new AdminController();
        $adminController->showProductEditForm($productId);
    } elseif ($url === 'admin_product_edit_process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $adminController = new AdminController();
        $adminController->processProductEdit($productId);
    } else {
        http_response_code(404);
        echo "Erreur 404 : Page non trouvée (Route non définie pour l'URL : '" . htmlspecialchars($url) . "').";
    }
}
?>