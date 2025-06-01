<?php
// src/lib/auth.php

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function ensureUserIsLoggedIn() {
    if (!isUserLoggedIn()) {
        set_flash_message('error', 'Veuillez vous connecter pour accéder à cette page.');
        header('Location: ' . INDEX_FILE_PATH . '?url=login');
        exit;
    }
}

function isAdmin() {
    return (isUserLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

function ensureUserIsAdmin() {
    if (!isAdmin()) {
        set_flash_message('error', 'Accès refusé. Vous devez être administrateur pour accéder à cette page.');
        header('Location: ' . INDEX_FILE_PATH . '?url=accueil');
        exit;
    }
}
?>