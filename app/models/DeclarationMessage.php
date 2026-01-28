<?php
/**
 * Classe DeclarationMessage - Gestion des messages du chat de déclaration
 */

class DeclarationMessage extends Model
{
    protected string $table = 'declaration_messages';

    /**
     * Obtenir les messages d'une déclaration
     */
    public function getByDeclaration(int $declarationId): array
    {
        $sql = "SELECT dm.*, u.username as sender_name, u.role as sender_role 
                FROM {$this->table} dm 
                JOIN utilisateurs u ON dm.sender_id = u.id 
                WHERE dm.declaration_id = ? 
                ORDER BY dm.created_at ASC";
        return $this->db->fetchAll($sql, [$declarationId]);
    }
}
?>
