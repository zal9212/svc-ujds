<?php
/**
 * Service d'audit - Traçabilité des actions
 * Enregistre toutes les actions importantes dans la base de données
 */

class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /**
     * Logger une action
     */
    public function log(string $action, string $tableName, int $recordId, ?string $details = null): void
    {
        $utilisateurId = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO historique (utilisateur_id, action, table_name, record_id, details) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $utilisateurId,
                $action,
                $tableName,
                $recordId,
                $details
            ]);
        } catch (Exception $e) {
            // Log silencieusement l'erreur sans interrompre l'application
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    /**
     * Logger une création
     */
    public function logCreate(string $tableName, int $recordId, array $data = []): void
    {
        $details = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        $this->log('CREATE', $tableName, $recordId, $details);
    }

    /**
     * Logger une mise à jour
     */
    public function logUpdate(string $tableName, int $recordId, array $changes = []): void
    {
        $details = !empty($changes) ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null;
        $this->log('UPDATE', $tableName, $recordId, $details);
    }

    /**
     * Logger une suppression
     */
    public function logDelete(string $tableName, int $recordId, array $data = []): void
    {
        $details = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        $this->log('DELETE', $tableName, $recordId, $details);
    }

    /**
     * Logger une connexion
     */
    public function logLogin(int $userId, bool $success = true): void
    {
        $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
        $details = json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], JSON_UNESCAPED_UNICODE);
        
        $this->log($action, 'utilisateurs', $userId, $details);
    }

    /**
     * Logger une déconnexion
     */
    public function logLogout(int $userId): void
    {
        $this->log('LOGOUT', 'utilisateurs', $userId);
    }

    /**
     * Obtenir l'historique récent
     */
    public function getRecentHistory(int $limit = 50): array
    {
        $sql = "SELECT h.*, u.username 
                FROM historique h 
                LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id 
                ORDER BY h.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Obtenir l'historique par utilisateur
     */
    public function getHistoryByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM historique 
                WHERE utilisateur_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Obtenir l'historique par table
     */
    public function getHistoryByTable(string $tableName, int $recordId): array
    {
        $sql = "SELECT h.*, u.username 
                FROM historique h 
                LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id 
                WHERE h.table_name = ? AND h.record_id = ? 
                ORDER BY h.created_at DESC";
        
        return $this->db->fetchAll($sql, [$tableName, $recordId]);
    }

    /**
     * Nettoyer l'historique ancien
     */
    public function cleanOldHistory(int $daysToKeep = 365): int
    {
        $sql = "DELETE FROM historique 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->query($sql, [$daysToKeep]);
        return $stmt->rowCount();
    }
}
