<?php
/**
 * Classe Versement - Gestion des versements
 * Représente un paiement mensuel d'un membre
 */

class Versement extends Model
{
    protected string $table = 'versements';

    /**
     * Obtenir les versements d'un membre (filtrés par année optionnelle)
     */
    public function getByMembre(int $membreId, ?int $annee = null): array
    {
        $sql = "SELECT v.*, m.designation, m.code 
                FROM {$this->table} v 
                JOIN membres m ON v.membre_id = m.id 
                WHERE v.membre_id = ?";
        $params = [$membreId];

        if ($annee) {
            $sql .= " AND v.annee = ?";
            $params[] = $annee;
        }

        $sql .= " ORDER BY v.annee DESC, 
                FIELD(v.mois, 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
                'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre')";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Obtenir les versements par statut
     */
    public function getByStatut(string $statut): array
    {
        return $this->findAll(['statut' => $statut]);
    }

    /**
     * Marquer comme payé
     */
    public function marquerPaye(int $id, float $montant): bool
    {
        return $this->update($id, [
            'montant' => $montant,
            'statut' => 'PAYE',
            'date_paiement' => date('Y-m-d')
        ]);
    }

    /**
     * Marquer comme partiel
     */
    public function marquerPartiel(int $id, float $montant): bool
    {
        return $this->update($id, [
            'montant' => $montant,
            'statut' => 'PARTIEL',
            'date_paiement' => date('Y-m-d')
        ]);
    }

    /**
     * Annuler un versement
     */
    public function annuler(int $id): bool
    {
        return $this->update($id, [
            'statut' => 'ANNULE',
            'montant' => 0,
            'date_paiement' => null
        ]);
    }

    /**
     * Créer un versement
     */
    public function createVersement(int $membreId, string $mois, int $annee, float $montant = 0, string $statut = 'EN_ATTENTE', int $hasAmende = 0): int
    {
        $data = [
            'membre_id' => $membreId,
            'mois' => $mois,
            'annee' => $annee,
            'montant' => $montant,
            'statut' => $statut,
            'has_amende' => $hasAmende,
            'date_paiement' => $statut === 'PAYE' || $statut === 'PARTIEL' ? date('Y-m-d') : null
        ];
        return $this->create($data);
    }

    /**
     * Vérifier si un versement existe
     */
    public function versementExists(int $membreId, string $mois, int $annee): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE membre_id = ? AND mois = ? AND annee = ? LIMIT 1";
        $result = $this->db->fetchOne($sql, [$membreId, $mois, $annee]);
        return $result !== null;
    }

    /**
     * Vérifier si un mois est déjà payé
     */
    public function isMonthPaid(int $membreId, string $mois, int $annee): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE membre_id = ? AND mois = ? AND annee = ? AND statut = 'PAYE' LIMIT 1";
        $result = $this->db->fetchOne($sql, [$membreId, $mois, $annee]);
        return $result !== null;
    }

    /**
     * Obtenir le total versé par un membre
     */
    public function getTotalVerse(int $membreId): float
    {
        $sql = "SELECT SUM(montant) as total FROM {$this->table} 
                WHERE membre_id = ? AND statut IN ('PAYE', 'PARTIEL')";
        $result = $this->db->fetchOne($sql, [$membreId]);
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Créer des versements en masse pour les retards
     * @param int $membreId ID du membre
     * @param int $moisRetard Nombre de mois de retard
     * @param string $dateFinal Date finale de la période de retard (YYYY-MM-DD)
     * @return int Nombre de versements créés
     */
    public function createBulkUnpaidVersements(int $membreId, int $moisRetard, string $dateFinal): int
    {
        if ($moisRetard <= 0 || empty($dateFinal)) {
            return 0;
        }

        // Récupérer le montant mensuel du membre
        $sql = "SELECT montant_mensuel FROM membres WHERE id = ?";
        $membre = $this->db->fetchOne($sql, [$membreId]);
        if (!$membre) {
            throw new Exception("Membre non trouvé");
        }
        $montantMensuel = (float) $membre['montant_mensuel'];

        // Liste des mois en français
        $moisList = [
            'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
        ];

        // Parser la date finale
        $timestamp = strtotime($dateFinal);
        if (!$timestamp) {
            return 0;
        }
        
        $totalMonths = $moisRetard;
        $endYear = (int) date('Y', $timestamp);
        $endMonth = (int) date('n', $timestamp); // 1-12

        // Créer les versements (Logique Smart Fill Backwards)
        $count = 0;
        $currentYear = $endYear;
        $currentMonth = $endMonth;
        $maxIterations = 120; // Sécurité (10 ans max)
        $iterations = 0;

        try {
            $this->db->beginTransaction();

            // On remonte dans le temps à partir de la date de fin
            while ($count < $totalMonths && $iterations++ < $maxIterations) {
                $moisNom = $moisList[$currentMonth - 1];

                // Vérifier si le versement n'existe pas déjà
                if (!$this->versementExists($membreId, $moisNom, $currentYear)) {
                    // Créer le versement avec montant 0, statut EN_ATTENTE et has_amende = 1
                    $this->createVersement($membreId, $moisNom, $currentYear, 0, 'EN_ATTENTE', 1);
                    $count++;
                }

                // Passer au mois précédent
                $currentMonth--;
                if ($currentMonth < 1) {
                    $currentMonth = 12;
                    $currentYear--;
                }
            }

            $this->db->commit();
            return $count;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Compter les mois en retard
     */
    public function countMoisRetard(int $membreId): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE membre_id = ? AND statut = 'EN_ATTENTE'";
        $result = $this->db->fetchOne($sql, [$membreId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Supprimer tous les retards en attente d'un membre
     */
    public function deleteAllCurrentRetards(int $membreId): int
    {
        // 1. Supprimer les mois EN_ATTENTE ou AMENDE
        $sqlDelete = "DELETE FROM {$this->table} WHERE membre_id = ? AND statut IN ('EN_ATTENTE', 'AMENDE')";
        $stmt = $this->db->query($sqlDelete, [$membreId]);
        $count = $stmt->rowCount();
        
        // 2. Enlever les amendes sur les autres mois (PAYE, PARTIEL) pour ce membre
        $sqlUpdate = "UPDATE {$this->table} SET has_amende = 0 WHERE membre_id = ?";
        $this->db->query($sqlUpdate, [$membreId]);
        
        return $count;
    }

    /**
     * Récupérer la date du dernier retard (pour pré-remplir le formulaire)
     */
    public function getLastRetardDate(int $membreId): ?string
    {
        // On cherche le versement EN_ATTENTE le plus récent (année DESC, mois DESC logic handled by ordering)
        // Mais comme mois est string, on va trier par annee DESC, id DESC comme proxy si insert order is chronological,
        // ou mieux: on va faire une logique plus robuste si possible.
        // Simplification: prendre le dernier ID inséré avec statut EN_ATTENTE
        
        $sql = "SELECT mois, annee FROM {$this->table} 
                WHERE membre_id = ? AND statut = 'EN_ATTENTE' 
                ORDER BY annee DESC, id DESC LIMIT 1";
                
        $versement = $this->db->fetchOne($sql, [$membreId]);
        
        if (!$versement) {
            return null;
        }

        // Convertir mois/année en date fin de mois
        $moisFr = [
            'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
            'juillet' => 7, 'août' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12
        ];
        
        $moisNum = $moisFr[strtolower($versement['mois'])] ?? 12;
        $annee = (int) $versement['annee'];
        
        // Retourner la date du dernier jour du mois
        return date('Y-m-t', strtotime("$annee-$moisNum-01"));
    }
}
