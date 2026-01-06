<?php
/**
 * Classe Security - Utilitaires de sécurité
 * Gestion CSRF, XSS, sanitization
 */

class Security
{
    /**
     * Générer un token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Valider un token CSRF
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || $token === null) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Nettoyer les données (protection XSS)
     */
    public static function sanitize(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Hacher un mot de passe
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Vérifier un mot de passe
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Valider un mot de passe
     */
    public static function validatePassword(string $password): bool
    {
        return strlen($password) >= PASSWORD_MIN_LENGTH;
    }

    /**
     * Générer un token aléatoire
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Nettoyer un nom de fichier
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Supprimer les caractères dangereux
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        // Limiter la longueur
        return substr($filename, 0, 255);
    }

    /**
     * Vérifier le type MIME d'un fichier
     */
    public static function validateFileType(string $filepath, array $allowedTypes): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes, true);
    }
}
