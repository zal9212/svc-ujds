<?php
/**
 * Modèle SupportMessage
 * Gère les messages de discussion générale entre membres et administrateurs
 */

class SupportMessage extends Model
{
    protected string $table = 'support_messages';

    /**
     * Récupérer la conversation complète d'un membre
     */
    public function getByMembre(int $membreId): array
    {
        $sql = "SELECT sm.*, u.username as sender_name, u.role as sender_role 
                FROM {$this->table} sm 
                JOIN utilisateurs u ON sm.sender_id = u.id 
                WHERE sm.membre_id = ? 
                ORDER BY sm.created_at ASC";
        return $this->db->fetchAll($sql, [$membreId]);
    }

    /**
     * Récupérer les derniers messages de chaque membre (pour la liste admin)
     */
    public function getLatestConversations(): array
    {
        $sql = "SELECT sm.*, m.designation, m.code, u.username as last_sender 
                FROM {$this->table} sm 
                JOIN membres m ON sm.membre_id = m.id 
                JOIN utilisateurs u ON sm.sender_id = u.id 
                WHERE sm.id IN (
                    SELECT MAX(id) FROM {$this->table} GROUP BY membre_id
                ) 
                ORDER BY sm.created_at DESC";
        return $this->db->fetchAll($sql);
    }
}
?>
