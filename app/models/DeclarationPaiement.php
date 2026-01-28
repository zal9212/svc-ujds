<?php
/**
 * Classe DeclarationPaiement - Gestion des déclarations de paiement
 */

class DeclarationPaiement extends Model
{
    protected string $table = 'declarations_paiements';

    /**
     * Obtenir les déclarations en attente
     */
    public function getPending(): array
    {
        $sql = "SELECT d.*, m.designation, m.code 
                FROM {$this->table} d 
                JOIN membres m ON d.membre_id = m.id 
                WHERE d.statut = 'EN_ATTENTE' 
                ORDER BY d.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Obtenir l'historique d'un membre
     */
    public function getByMembre(int $membreId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE membre_id = ? 
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$membreId]);
    }

    /**
     * Obtenir une déclaration avec les infos du membre
     */
    public function getWithMembre(int $id): ?array
    {
        $sql = "SELECT d.*, m.designation, m.code, m.id as membre_id
                FROM {$this->table} d 
                JOIN membres m ON d.membre_id = m.id 
                WHERE d.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
}
?>
