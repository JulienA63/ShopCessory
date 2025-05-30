<?php
// src/lib/autoloader.php

function registerAppAutoloader() {
    spl_autoload_register(function ($className) {
        // On cherche d'abord dans les contrôleurs
        $file = APP_PATH . '/controllers/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }

        // Puis dans les modèles (pour plus tard)
        $file = APP_PATH . '/models/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }

        // Puis dans lib (pour d'autres fonctions/classes utilitaires si besoin)
        $file = APP_PATH . '/lib/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    });
}
?>