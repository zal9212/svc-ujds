<?php
/**
 * Routeur de l'application
 * Gestion des routes et dispatch des requêtes
 */

class Router
{
    private array $routes = [];

    /**
     * Ajouter une route
     */
    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Dispatcher la requête
     */
    public function dispatch(string $uri, string $method): void
    {
        // Nettoyer l'URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Retirer le chemin de base (svc-ujds/public)
        $uri = preg_replace('#^/svc-ujds/public/?#', '', $uri);
        $uri = trim($uri, '/');

        // Chercher la route correspondante
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
                $controllerName = $route['controller'];
                $action = $route['action'];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $action)) {
                        $controller->$action();
                        return;
                    }
                }
            }
        }

        // Route non trouvée
        http_response_code(404);
        echo "404 - Page non trouvée";
    }

    /**
     * Vérifier si le chemin correspond
     */
    private function matchPath(string $pattern, string $uri): bool
    {
        // Pour simplifier, on utilise une correspondance exacte
        // On peut améliorer avec des paramètres dynamiques si nécessaire
        return $pattern === $uri || ($pattern === '' && $uri === '');
    }
}

// Définir les routes
$router = new Router();

// Routes publiques
$router->add('GET', '', 'AuthController', 'login');
$router->add('GET', 'login', 'AuthController', 'login');
$router->add('POST', 'login', 'AuthController', 'authenticate');
$router->add('GET', 'logout', 'AuthController', 'logout');
$router->add('GET', 'auth/changePassword', 'AuthController', 'changePassword');
$router->add('POST', 'auth/updatePasswordPost', 'AuthController', 'updatePasswordPost');

// Dashboard
$router->add('GET', 'dashboard', 'DashboardController', 'index');

// Membres
$router->add('GET', 'membres', 'MembreController', 'index');
$router->add('GET', 'membres/show', 'MembreController', 'show');
$router->add('GET', 'membres/create', 'MembreController', 'create');
$router->add('POST', 'membres/store', 'MembreController', 'store');
$router->add('GET', 'membres/edit', 'MembreController', 'edit');
$router->add('POST', 'membres/update', 'MembreController', 'update');
$router->add('POST', 'membres/delete', 'MembreController', 'delete');
$router->add('POST', 'membres/change-statut', 'MembreController', 'changeStatut');
$router->add('POST', 'membres/createAccount', 'MembreController', 'createAccount');
$router->add('POST', 'membres/update_financial_config', 'MembreController', 'update_financial_config');
$router->add('POST', 'membres/check_late_months_conflicts', 'MembreController', 'check_late_months_conflicts');

// Versements
$router->add('GET', 'versements', 'VersementController', 'index');
$router->add('GET', 'versements/create', 'VersementController', 'create');
$router->add('POST', 'versements/store', 'VersementController', 'store');
$router->add('POST', 'versements/mark-paid', 'VersementController', 'markPaid');
$router->add('POST', 'versements/mark-partial', 'VersementController', 'markPartial');
$router->add('POST', 'versements/cancel', 'VersementController', 'cancel');
$router->add('POST', 'versements/delete', 'VersementController', 'delete');
$router->add('POST', 'versements/delete-all-retards', 'VersementController', 'deleteAllRetards');
$router->add('GET', 'versements/edit', 'VersementController', 'edit');
$router->add('POST', 'versements/update', 'VersementController', 'update');

// Avances
$router->add('GET', 'avances/create', 'AvanceController', 'create');
$router->add('POST', 'avances/store', 'AvanceController', 'store');
$router->add('GET', 'avances/edit', 'AvanceController', 'edit');
$router->add('POST', 'avances/update', 'AvanceController', 'update');
$router->add('POST', 'avances/delete', 'AvanceController', 'delete');

// Import/Export
$router->add('GET', 'import', 'ImportController', 'index');
$router->add('GET', 'import/template', 'ImportController', 'template');
$router->add('POST', 'import/upload', 'ImportController', 'upload');
$router->add('GET', 'export/excel', 'ExportController', 'excel');
$router->add('GET', 'export/pdf', 'ExportController', 'pdf');

// Déclarations de Paiement
$router->add('GET', 'declarations', 'DeclarationController', 'index');
$router->add('GET', 'declarations/submit', 'DeclarationController', 'submit');
$router->add('POST', 'declarations/store', 'DeclarationController', 'store');
$router->add('GET', 'declarations/show', 'DeclarationController', 'show');
$router->add('POST', 'declarations/message', 'DeclarationController', 'sendMessage');
$router->add('GET', 'declarations/admin', 'DeclarationController', 'adminList');
$router->add('POST', 'declarations/validate', 'DeclarationController', 'validate');
$router->add('POST', 'declarations/reject', 'DeclarationController', 'reject');

// Support / Chat Global
$router->add('GET', 'support', 'SupportController', 'index');
$router->add('GET', 'support/admin', 'SupportController', 'adminList');
$router->add('GET', 'support/view', 'SupportController', 'view');
$router->add('POST', 'support/send', 'SupportController', 'send');

return $router;
