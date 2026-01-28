<?php
/**
 * Configuration de l'application
 * Système de Gestion des Versements d'Association
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'svc_ujds');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Construction du DSN
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Configuration de l'application
define('APP_NAME', 'Système de Gestion des Versements');
define('APP_VERSION', '1.0.0');

// Détection dynamique de l'URL de base
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = $protocol . '://' . $host . rtrim($scriptName, '/');

// On s'assure que BASE_URL pointe vers le dossier public si on n'y est pas déjà
if (!str_ends_with($baseUrl, '/public')) {
    $baseUrl .= '/public';
}

define('BASE_URL', $baseUrl);
define('BASE_PATH', __DIR__ . '/..');

// Chemins
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('VIEW_PATH', APP_PATH . '/views');

// Configuration de session
define('SESSION_NAME', 'SVC_UJDS_SESSION');
define('SESSION_LIFETIME', 7200); // 2 heures

// Configuration de sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);

// Configuration des uploads
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXCEL_TYPES', ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

// Timezone
date_default_timezone_set('Africa/Kinshasa');

// Gestion des erreurs
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Démarrage de la session avec paramètres de sécurité renforcés
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    
    // Paramètres de sécurité pour le cookie de session
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' ? '' : ($_SERVER['HTTP_HOST'] ?? ''),
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Uniquement si HTTPS est activé
        'httponly' => true, // Empêche l'accès au cookie via JavaScript (anti-XSS)
        'samesite' => 'Lax' // Protection contre CSRF
    ]);

    session_start();
}

// Headers de sécurité globaux (uniquement si pas en mode CLI)
if (php_sapi_name() !== 'cli') {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}
// Optionnel: Content-Security-Policy (à ajuster selon les besoins en scripts externes)
// header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' cdn.tailwindcss.com;");
