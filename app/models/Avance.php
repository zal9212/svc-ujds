<?php
/**
 * Classe Avance - Gestion des avances et anticipations
 * Représente une avance (pour dettes) ou anticipation (pour mois futurs)
 */

class Avance extends Model
{
    protected string $table = 'avances';

    /**
     * Obtenir les avances d'un membre (filtrées par année et/ou type optionnels)
     */
    public function getByMembre(int $membreId, ?int $annee = null, ?string $type = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE membre_id = ?";
        $params = [$membreId];

        if ($annee) {
            $sql .= " AND YEAR(date_avance) = ?";
            $params[] = $annee;
        }

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY date_avance DESC";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtenir les avances d'un membre par type
     */
    public function getByMembreAndType(int $membreId, string $type): array
    {
        return $this->getByMembre($membreId, null, $type);
    }

    /**
     * Créer une avance ou anticipation
     */
    public function createAvance(int $membreId, float $montant, string $motif, string $dateAvance = null, string $type = 'AVANCE'): int
    {
        $data = [
            'membre_id' => $membreId,
            'montant' => $montant,
            'motif' => $motif,
            'date_avance' => $dateAvance ?? date('Y-m-d'),
            'type' => $type
        ];
        return $this->create($data);
    }

    /**
     * Obtenir le total des avances d'un membre (tous types)
     */
    public function getTotalAvance(int $membreId): float
    {
        $sql = "SELECT SUM(montant) as total FROM {$this->table} WHERE membre_id = ?";
        $result = $this->db->fetchOne($sql, [$membreId]);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Obtenir le total par type
     */
    public function getTotalByType(int $membreId, string $type): float
    {
        $sql = "SELECT SUM(montant) as total FROM {$this->table} WHERE membre_id = ? AND type = ?";
        $result = $this->db->fetchOne($sql, [$membreId, $type]);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Mettre à jour une avance
     */
    public function updateAvance(int $id, float $montant, string $motif = null): bool
    {
        $data = ['montant' => $montant];
        if ($motif) {
            $data['motif'] = $motif;
        }
        return $this->update($id, $data);
    }

    /**
     * Supprimer une avance
     */
    public function deleteAvance(int $id): bool
    {
        return $this->delete($id);
    }
}
