<?php
// src/lib/auth.php

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function ensureUserIsLoggedIn() {
    if (!isUserLoggedIn()) {
        header('Location: ' . INDEX_FILE_PATH . '?url=login&require_login=true');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur actuellement connecté est un administrateur.
 * @return bool True si l'utilisateur est un admin, False sinon.
 */
function isAdmin() {
    return (isUserLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

/**
 * S'assure que l'utilisateur est un administrateur.
 * Si l'utilisateur n'est pas un admin, il est redirigé vers la page d'accueil
 * avec un message d'erreur (ou une page d'accès refusé).
 */
function ensureUserIsAdmin() {
    if (!isAdmin()) {
        // Rediriger vers la page d'accueil si pas admin
        // On pourrait aussi afficher un message d'erreur plus spécifique ou une page "403 Accès Interdit"
        header('Location: ' . INDEX_FILE_PATH . '?url=accueil&error=admin_required');
        exit;
    }
}
?>