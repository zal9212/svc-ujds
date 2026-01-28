<?php
/**
 * Classe Controller - Base pour tous les contrôleurs
 * Fournit les méthodes communes aux contrôleurs
 */

abstract class Controller
{
    /**
     * Rendre une vue
     */
    protected function render(string $view, array $data = [], ?string $layout = 'layout/main'): void
    {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);

        // Démarrer la mise en mémoire tampon
        ob_start();

        // Inclure la vue
        $viewPath = VIEW_PATH . '/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Vue non trouvée: $viewPath");
        }

        // Récupérer le contenu de la vue
        $content = ob_get_clean();

        // Inclure le layout si spécifié
        if ($layout) {
            $layoutPath = VIEW_PATH . '/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Retourner une réponse JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Rediriger vers une URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Obtenir une valeur POST
     */
    protected function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtenir une valeur GET
     */
    protected function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Valider le token CSRF
     */
    protected function validateCsrf(): bool
    {
        $token = $this->post(CSRF_TOKEN_NAME);
        return Security::validateCsrfToken($token);
    }

    /**
     * Définir un message flash
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obtenir et supprimer un message flash
     */
    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Vérifier si la requête est AJAX
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Session expirée. Veuillez vous reconnecter.'], 401);
            }
            $this->setFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect(BASE_URL . '/login');
        }
    }

    /**
     * Vérifier le rôle de l'utilisateur
     */
    protected function requireRole(array $roles): void
    {
        $this->requireAuth();
        
        $userRole = $_SESSION['user_role'] ?? null;
        if (!in_array($userRole, $roles, true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Permissions insuffisantes.'], 403);
            }
            $this->setFlash('error', 'Vous n\'avez pas les permissions nécessaires.');
            $this->redirect(BASE_URL . '/dashboard');
        }
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    protected function requireAdmin(): void
    {
        $this->requireRole(['admin']);
    }

    /**
     * Obtenir l'utilisateur connecté
     */
    protected function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['user_role'] ?? ''
        ];
    }

    /**
     * Nettoyer les données d'entrée
     */
    protected function sanitize(string $data): string
    {
        return Security::sanitize($data);
    }
}
