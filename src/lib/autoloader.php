<?php
// src/lib/autoloader.php

function registerAppAutoloader() {
    spl_autoload_register(function ($className) {
        $paths = [
            APP_PATH . '/controllers/',
            APP_PATH . '/models/', // Pour quand tu auras des modèles
            APP_PATH . '/lib/'     // Pour d'autres classes utilitaires
        ];

        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    });
}
?>