<?php
/**
 * Point d'entrée de l'application
 * Front Controller Pattern
 */

// Charger la configuration
require_once __DIR__ . '/../config/config.php';

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/',
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/services/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Charger le routeur
require_once __DIR__ . '/../config/routes.php';

// Dispatcher la requête
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);
