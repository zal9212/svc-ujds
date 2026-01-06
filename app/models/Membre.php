<?php
/**
 * Classe Membre - Gestion des membres
 * Implémente toute la logique métier pour les calculs
 */

class Membre extends Model
{
    protected string $table = 'membres';

    private ?array $versements = null;
    private ?array $avances = null;

    /**
     * Obtenir un membre avec ses relations (filtrées par année optionnelle)
     */
    public function getWithRelations(int $id, ?int $annee = null): ?array
    {
        $membre = $this->find($id);
        if (!$membre) {
            return null;
        }

        // Charger les versements et avances
        $versementModel = new Versement();
        $avanceModel = new Avance();

        // POUR LE CALCUL FINANCIER: On a besoin de TOUS les versements (passé, présent, futur)
        // sinon on ne connait pas les dettes des années précédentes
        $allVersements = $versementModel->getByMembre($id); // Pas de filtre d'année ici
        $membre['avances'] = $avanceModel->getByMembre($id); // Global

        // Créer une structure temporaire pour le calcul
        $membreForCalculation = $membre;
        $membreForCalculation['versements'] = $allVersements;

        // Récupérer la situation financière complète sur la base de TOUT l'historique
        $situation = $this->getSituationFinanciere($membreForCalculation);
        
        // Fusionner avec les données du membre
        $membre = array_merge($membre, $situation);

        // POUR L'AFFICHAGE: On filtre les versements selon l'année demandée + les virtuels
        
        // 1. Récupérer les versements réels mais mis à jour par la réconciliation (statuts, montants...)
        // L'array $situation['reconciled'] contient les états mis à jour, mais il est indexé par ID.
        // Il faut reconstruire la liste $membre['versements'] pour l'affichage de l'année demandée.
        
        $displayVersements = [];
        foreach ($allVersements as $v) {
            // Filtrer par année si demandée
            if ($annee && (int)$v['annee'] !== (int)$annee) {
                continue;
            }

            // Mettre à jour avec les infos réconciliées si dispo
            if (isset($situation['reconciled'][$v['id']])) {
                $rec = $situation['reconciled'][$v['id']];
                $v['statut'] = $rec['display_statut'];
                $v['montant'] = $rec['display_montant']; // Montant cumulé (reçu + avance)
            }
            $displayVersements[] = $v;
        }
        
        $membre['versements'] = $displayVersements;

        // Intégrer les versements virtuels à la liste principale pour l'affichage
        if (!empty($situation['virtual_versements'])) {
            $virtuals = $situation['virtual_versements'];
            
            // Si une année spécifique est demandée, filtrer les virtuels
            if ($annee) {
                $virtuals = array_filter($virtuals, function($v) use ($annee) {
                    return (int)$v['annee'] === (int)$annee;
                });
            }

            $membre['versements'] = array_merge($membre['versements'], $virtuals);
            
            // Re-trier pour avoir les futurs en premier (Année DESC, Mois DESC)
            usort($membre['versements'], function($a, $b) {
                if ($a['annee'] != $b['annee']) {
                    return $b['annee'] <=> $a['annee']; // DESC
                }
                
                $moisOrder = [
                    'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
                    'juillet' => 7, 'août' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12
                ];
                
                return ($moisOrder[strtolower($b['mois'])] ?? 0) <=> ($moisOrder[strtolower($a['mois'])] ?? 0); // DESC
            });
        }

        return $membre;
    }

    /**
     * Vérifier si le membre est en voyage (VG)
     */
    public function estVG(array $membre): bool
    {
        return $membre['statut'] === 'VG';
    }

    public function getSituationFinanciere(array $membre): array
    {
        $montantMensuel = (float) ($membre['montant_mensuel'] ?? 0);
        $avancesPool = $this->totalAvanceByType($membre, 'AVANCE');
        $anticipationsPool = $this->totalAvanceByType($membre, 'ANTICIPATION');
        $versements = $membre['versements'] ?? [];

        // 1. Initialisation et tri
        $versements = $this->_sortVersements($versements);
        $reconciled = $this->_initReconciliation($versements, $montantMensuel);
        $virtualVersements = [];

        // 2. PASS 1: Priorité aux mois avec AMENDES (Utilise AVANCE)
        if ($avancesPool > 0) {
            $this->_applyAvancesToAmendes($versements, $reconciled, $avancesPool);
        }

        // 3. PASS 2: Allocation Chronologique (Dettes Réelles - Utilise AVANCE)
        if ($avancesPool > 0) {
            $this->_applyAvancesToDebts($membre, $versements, $reconciled, $avancesPool, $montantMensuel);
        }

        // 4. PASS 3: Anticipations (Mois Futurs - Utilise ANTICIPATION)
        if ($anticipationsPool > 0) {
            $virtualVersements = $this->_applyAnticipationsToFuture($membre, $versements, $reconciled, $anticipationsPool, $montantMensuel);
        }

        // 5. PASS 4: Finalisation des calculs
        return $this->_calculateFinalSummary($membre, $reconciled, $virtualVersements);
    }

    /**
     * Trie les versements par ordre chronologique
     */
    private function _sortVersements(array $versements): array
    {
        usort($versements, function($a, $b) {
            if ($a['annee'] != $b['annee']) {
                return $a['annee'] <=> $b['annee'];
            }
            $moisOrder = [
                'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
                'juillet' => 7, 'août' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12
            ];
            return ($moisOrder[strtolower($a['mois'])] ?? 0) <=> ($moisOrder[strtolower($b['mois'])] ?? 0);
        });
        return $versements;
    }

    /**
     * Initialise la map de réconciliation pour chaque versement réel
     */
    private function _initReconciliation(array $versements, float $montantMensuel): array
    {
        $reconciled = [];
        foreach ($versements as $v) {
            if ($v['statut'] === 'ANNULE') continue;

            $vId = $v['id'];
            $isAmende = !empty($v['has_amende']) || $v['statut'] === 'AMENDE';
            $dejaPaye = max(0, (float)$v['montant']);
            
            $pPaid = min($dejaPaye, $montantMensuel);
            $aPaid = max(0, $dejaPaye - $montantMensuel);

            $reconciled[$vId] = [
                'original_statut' => $v['statut'],
                'display_statut' => $v['statut'],
                'display_montant' => $dejaPaye,
                'applied_advance' => 0,
                'applied_principal' => 0,
                'applied_amende' => 0,
                'paid_principal' => $pPaid,
                'paid_amende' => $aPaid,
                'is_amende' => $isAmende,
                'principal_du' => $montantMensuel,
                'amende_due' => $isAmende ? 2000 : 0,
                'due_total' => $isAmende ? ($montantMensuel + 2000) : $montantMensuel
            ];
        }
        return $reconciled;
    }

    /**
     * PASS 1: Utilise le pool d'AVANCE pour payer les amendes en priorité
     */
    private function _applyAvancesToAmendes(array $versements, array &$reconciled, float &$avancesPool): void
    {
        foreach ($versements as $v) {
            $vId = $v['id'];
            if (!isset($reconciled[$vId]) || !$reconciled[$vId]['is_amende'] || $reconciled[$vId]['display_statut'] === 'PAYE') {
                continue;
            }

            $reste = $reconciled[$vId]['due_total'] - $reconciled[$vId]['display_montant'];
            if ($reste > 0 && $avancesPool > 0) {
                $allocation = min($avancesPool, $reste);
                $avancesPool -= $allocation;
                
                $principalRestant = $reconciled[$vId]['principal_du'] - $reconciled[$vId]['paid_principal'];
                $amendeRestante = $reconciled[$vId]['amende_due'] - $reconciled[$vId]['paid_amende'];

                $allocPrincipal = min($allocation, $principalRestant);
                $allocAmende = min($allocation - $allocPrincipal, $amendeRestante);

                $reconciled[$vId]['display_montant'] += $allocation;
                $reconciled[$vId]['applied_advance'] += $allocation;
                $reconciled[$vId]['applied_principal'] += $allocPrincipal;
                $reconciled[$vId]['applied_amende'] += $allocAmende;
                $reconciled[$vId]['paid_principal'] += $allocPrincipal;
                $reconciled[$vId]['paid_amende'] += $allocAmende;
                
                $reconciled[$vId]['display_statut'] = $reconciled[$vId]['display_montant'] >= $reconciled[$vId]['due_total'] ? 'PAYE (AVANCE)' : 'PARTIEL (AVANCE)';
            }
        }
    }

    /**
     * PASS 2: Allocation chronologique des AVANCES sur les dettes réelles
     */
    private function _applyAvancesToDebts(array $membre, array $versements, array &$reconciled, float &$avancesPool, float $montantMensuel): void
    {
        $moisList = ['janvier'=>1,'février'=>2,'mars'=>3,'avril'=>4,'mai'=>5,'juin'=>6,'juillet'=>7,'août'=>8,'septembre'=>9,'octobre'=>10,'novembre'=>11,'décembre'=>12];
        $firstUnpaidRealDate = null;
        $existingMap = [];

        foreach ($versements as $v) {
            if ($v['statut'] === 'ANNULE') continue;
            $mIndex = $moisList[strtolower($v['mois'])] ?? 0;
            $key = $v['annee'] . '-' . $mIndex;
            $existingMap[$key] = $v;

            if (isset($reconciled[$v['id']]) && !in_array($reconciled[$v['id']]['display_statut'], ['PAYE', 'PAYE (AVANCE)']) && !$reconciled[$v['id']]['is_amende']) {
                $dDate = strtotime($v['annee'] . '-' . $mIndex . '-01');
                if ($firstUnpaidRealDate === null || $dDate < $firstUnpaidRealDate) $firstUnpaidRealDate = $dDate;
            }
        }

        $startTs = $firstUnpaidRealDate !== null ? min(time(), $firstUnpaidRealDate) : time();
        $scanYear = (int)date('Y', $startTs);
        $scanMonth = (int)date('n', $startTs);
        $iter = 0;

        while ($avancesPool > 0 && $iter++ < 120) {
            $key = $scanYear . '-' . $scanMonth;
            if (isset($existingMap[$key])) {
                $vId = $existingMap[$key]['id'];
                if (isset($reconciled[$vId]) && !$reconciled[$vId]['is_amende']) {
                    $reste = $reconciled[$vId]['due_total'] - $reconciled[$vId]['display_montant'];
                    if ($reste > 0) {
                        $allocation = min($avancesPool, $reste);
                        $avancesPool -= $allocation;
                        $reconciled[$vId]['display_montant'] += $allocation;
                        $reconciled[$vId]['applied_advance'] += $allocation;
                        $reconciled[$vId]['applied_principal'] += $allocation;
                        $reconciled[$vId]['paid_principal'] += $allocation;
                        $reconciled[$vId]['display_statut'] = $reconciled[$vId]['display_montant'] >= $reconciled[$vId]['due_total'] ? 'PAYE (AVANCE)' : 'PARTIEL (AVANCE)';
                    }
                }
            }
            if (++$scanMonth > 12) { $scanMonth = 1; $scanYear++; }
        }
    }

    /**
     * PASS 3: Création de mois futurs virtuels en utilisant le pool d'ANTICIPATION
     */
    private function _applyAnticipationsToFuture(array $membre, array $versements, array &$reconciled, float &$anticipationsPool, float $montantMensuel): array
    {
        $moisList = ['janvier'=>1,'février'=>2,'mars'=>3,'avril'=>4,'mai'=>5,'juin'=>6,'juillet'=>7,'août'=>8,'septembre'=>9,'octobre'=>10,'novembre'=>11,'décembre'=>12];
        $moisInvers = array_flip($moisList);
        $virtualVersements = [];

        $existingMap = [];
        foreach ($versements as $v) {
            if ($v['statut'] !== 'ANNULE') $existingMap[$v['annee'] . '-' . ($moisList[strtolower($v['mois'])] ?? 0)] = $v;
        }

        $scanYear = (int)date('Y');
        $scanMonth = (int)date('n');
        $lastDate = date('Y-m-d');
        if (!empty($membre['avances'])) {
            $dates = array_column(array_filter($membre['avances'], fn($a) => ($a['type'] ?? 'AVANCE') === 'ANTICIPATION'), 'date_avance');
            if ($dates) $lastDate = max($dates);
        }

        $iter = 0;
        while ($anticipationsPool > 0 && $iter++ < 120) {
            $key = $scanYear . '-' . $scanMonth;
            if (!isset($existingMap[$key])) {
                $allocation = min($anticipationsPool, $montantMensuel);
                $anticipationsPool -= $allocation;
                $moisNom = $moisInvers[$scanMonth] ?? 'janvier';
                $virtualId = "virt_{$scanYear}_{$scanMonth}";
                $statut = ($allocation >= $montantMensuel) ? 'ANTICIPATION' : 'ANTICIPATION (PARTIEL)';

                $virtualVersements[] = [
                    'id' => $virtualId, 'membre_id' => $membre['id'] ?? 0, 'mois' => $moisNom, 'annee' => $scanYear,
                    'montant' => $allocation, 'statut' => $statut, 'date_paiement' => $lastDate, 'has_amende' => 0, 'is_virtual' => true
                ];

                $reconciled[$virtualId] = [
                    'original_statut' => 'VIRTUAL', 'display_statut' => $statut, 'display_montant' => $allocation,
                    'applied_advance' => $allocation, 'applied_principal' => $allocation, 'applied_amende' => 0,
                    'paid_principal' => $allocation, 'paid_amende' => 0, 'is_amende' => false,
                    'principal_du' => $montantMensuel, 'amende_due' => 0, 'due_total' => $montantMensuel
                ];
            }
            if (++$scanMonth > 12) { $scanMonth = 1; $scanYear++; }
        }
        return $virtualVersements;
    }

    /**
     * PASS 4: Finalisation - Calcul des totaux, dettes et status
     */
    private function _calculateFinalSummary(array $membre, array $reconciled, array $virtualVersements): array
    {
        $totalPrincipalDu = 0;
        $totalAmendeDue = 0;
        $moisRetardRelatif = 0;
        $moisRetardBrut = 0;

        foreach ($reconciled as $id => $data) {
            // Ignorer les mois EN_ATTENTE sans amende ni paiement
            if ($data['display_statut'] === 'EN_ATTENTE' && !$data['is_amende'] && $data['display_montant'] == 0) continue;

            $totalPrincipalDu += $data['principal_du'];
            $totalAmendeDue += $data['amende_due'];

            if (strpos((string)$id, 'virt_') !== 0) {
                if (!in_array($data['display_statut'], ['PAYE', 'PAYE (AVANCE)', 'ANTICIPATION', 'AMENDE']) && $data['due_total'] > $data['display_montant']) {
                    $moisRetardRelatif++;
                }
                if (isset($data['original_statut']) && $data['original_statut'] === 'EN_ATTENTE') $moisRetardBrut++;
            }
        }

        $totalDirect = $this->totalVerse($membre);
        $totalAvance = $this->totalAvance($membre);
        $montantDu = ($totalPrincipalDu + $totalAmendeDue) - ($totalDirect + $totalAvance);

        return [
            'mois_retard' => $moisRetardRelatif,
            'mois_retard_brut' => $moisRetardBrut,
            'amende' => $totalAmendeDue,
            'principal_du' => $totalPrincipalDu,
            'total_verse' => $totalDirect + $totalAvance,
            'paiements_directs' => $totalDirect,
            'total_avance' => $totalAvance,
            'montant_du' => max(0, $montantDu),
            'statut_financier' => $montantDu <= 0 ? 'À JOUR' : 'EN RETARD',
            'reconciled' => $reconciled,
            'virtual_versements' => $virtualVersements
        ];
    }

    /**
     * Calculer le nombre de mois en retard
     */
    public function calculerMoisRetard(array $membre): int
    {
        // Si VG, aucun retard
        if ($this->estVG($membre)) {
            return 0;
        }

        $versements = $membre['versements'] ?? [];
        $retard = 0;

        foreach ($versements as $v) {
            if ($v['statut'] === 'ANNULE') continue;

            $isAmende = !empty($v['has_amende']) || $v['statut'] === 'AMENDE';
            
            // Ignorer les mois EN_ATTENTE s'il n'y a pas d'amende
            if ($v['statut'] === 'EN_ATTENTE' && !$isAmende) continue;

            // Si c'est pas payé (PAYE ou PAYE (AVANCE)), alors c'est un retard
            if (!in_array($v['statut'], ['PAYE', 'PAYE (AVANCE)'])) {
                $retard++;
            }
        }

        return $retard;
    }

    /**
     * Calculer l'amende
     */
    public function calculerAmende(array $membre): float
    {
        // Si VG, aucune amende
        if ($this->estVG($membre)) {
            return 0;
        }

        // Amende = 2000 FCFA pour chaque mois marqué "has_amende"
        $versements = $membre['versements'] ?? [];
        $nombreAmendes = 0;
        
        foreach ($versements as $v) {
            if (!empty($v['has_amende'])) {
                $nombreAmendes++;
            }
        }
        
        return $nombreAmendes * 2000;
    }

    /**
     * Calculer le total versé (Paiements Directs via la grille)
     */
    public function totalVerse(array $membre): float
    {
        $versements = $membre['versements'] ?? [];
        $total = 0;

        foreach ($versements as $versement) {
            if (in_array($versement['statut'], ['PAYE', 'PARTIEL'], true)) {
                $total += (float) $versement['montant'];
            }
        }

        return $total;
    }

    /**
     * Calculer le total des avances (tous types)
     */
    public function totalAvance(array $membre): float
    {
        $avances = $membre['avances'] ?? [];
        $total = 0;

        foreach ($avances as $avance) {
            $total += (float) $avance['montant'];
        }

        return $total;
    }

    /**
     * Calculer le total des avances par type
     */
    public function totalAvanceByType(array $membre, string $type): float
    {
        $avances = $membre['avances'] ?? [];
        $total = 0;

        foreach ($avances as $avance) {
            if (($avance['type'] ?? 'AVANCE') === $type) {
                $total += (float) $avance['montant'];
            }
        }

        return $total;
    }

    /**
     * Méthode legacy
     */
    public function montantDu(array $membre): float
    {
        $situation = $this->getSituationFinanciere($membre);
        return $situation['montant_du'];
    }

    /**
     * Obtenir le prochain numéro de membre disponible
     */
    public function getNextNumero(): int
    {
        $sql = "SELECT MAX(numero) as max_num FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        return ((int) ($result['max_num'] ?? 0)) + 1;
    }

    /**
     * Créer un membre
     */
    public function createMembre(array $data): int
    {
        $membreData = [
            'numero' => $data['numero'],
            'code' => $data['code'],
            'telephone' => $data['telephone'] ?? null,
            'titre' => $data['titre'] ?? null,
            'designation' => $data['designation'],
            'misside' => $data['misside'] ?? null,
            'montant_mensuel' => $data['montant_mensuel'] ?? 0,
            'statut' => $data['statut'] ?? 'ACTIF',
            'user_id' => $data['user_id'] ?? null
        ];

        return $this->create($membreData);
    }

    /**
     * Mettre à jour un membre
     */
    public function updateMembre(int $id, array $data): bool
    {
        $updateData = [];

        $allowedFields = ['numero', 'code', 'telephone', 'titre', 'designation', 'misside', 'montant_mensuel', 'statut', 'user_id'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        return $this->update($id, $updateData);
    }

    /**
     * Suspendre un membre
     */
    public function suspendre(int $id): bool
    {
        return $this->update($id, ['statut' => 'SUSPENDU']);
    }

    /**
     * Activer un membre
     */
    public function activer(int $id): bool
    {
        return $this->update($id, ['statut' => 'ACTIF']);
    }

    /**
     * Marquer comme VG (voyage)
     */
    public function marquerVG(int $id): bool
    {
        return $this->update($id, ['statut' => 'VG']);
    }

    /**
     * Rechercher des membres
     */
    public function search(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE designation LIKE ? OR code LIKE ? OR telephone LIKE ?
                ORDER BY designation";
        $searchTerm = "%$query%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }

    /**
     * Obtenir les membres par statut
     */
    public function getByStatut(string $statut): array
    {
        return $this->findAll(['statut' => $statut], 'designation ASC');
    }

    /**
     * Obtenir tous les membres avec calculs (filtrés par année optionnelle)
     */
    public function getAllWithCalculations(?int $annee = null): array
    {
        $membres = $this->findAll([], 'designation ASC');
        $versementModel = new Versement();
        $avanceModel = new Avance();

        foreach ($membres as &$membre) {
            $membre['versements'] = $versementModel->getByMembre($membre['id'], $annee);
            $membre['avances'] = $avanceModel->getByMembre($membre['id'], $annee);
            
            $situation = $this->getSituationFinanciere($membre);
            $membre = array_merge($membre, $situation);
        }

        return $membres;
    }

    /**
     * Supprimer un membre et ses données associées
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            // Supprimer les versements
            $this->db->query("DELETE FROM versements WHERE membre_id = ?", [$id]);
            
            // Supprimer les avances
            $this->db->query("DELETE FROM avances WHERE membre_id = ?", [$id]);
            
            // Supprimer le membre
            $result = parent::delete($id);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Trouver un membre par son code
     */
    public function findByCode(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$code]);
    }

    /**
     * Vérifier si le code membre existe
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE code = ?";
        $params = [$code];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result !== null;
    }

    /**
     * Trouver un membre par son user_id (pour la redirection dashboard)
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$userId]);
    }

    /**
     * Obtenir la liste des années où le membre a des activités (versements ou avances)
     */
    public function getAvailableYears(int $id): array
    {
        // Années des versements existants
        $sqlV = "SELECT DISTINCT annee FROM versements WHERE membre_id = ?";
        $resV = $this->db->fetchAll($sqlV, [$id]);
        $yearsV = array_column($resV, 'annee');

        // Années des avances
        $sqlA = "SELECT DISTINCT YEAR(date_avance) as annee FROM avances WHERE membre_id = ?";
        $resA = $this->db->fetchAll($sqlA, [$id]);
        $yearsA = array_column($resA, 'annee');
        
        // Années des versements VIRTUELS (Anticipation)
        // On doit calculer la situation financière pour les trouver
        // Utiliser getWithRelations pour cohérence, ou charger manuellement
        $membreData = $this->getWithRelations($id); // Charge tout: versements, avances et calcule situation
        
        if ($membreData && !empty($membreData['virtual_versements'])) {
             foreach ($membreData['virtual_versements'] as $virt) {
                $yearsV[] = $virt['annee'];
            }
        }

        // Fusionner, dédoublonner et trier
        $years = array_unique(array_merge($yearsV, $yearsA));
        
        if (empty($years)) {
            $years = [(int)date('Y')];
        } 
        
        sort($years);

        return $years;
    }

    /**
     * Obtenir la liste de toutes les années où il y a eu une activité dans le système
     */
    public function getGlobalAvailableYears(): array
    {
        // Années des versements
        $sqlV = "SELECT DISTINCT annee FROM versements";
        $resV = $this->db->fetchAll($sqlV);
        $yearsV = array_column($resV, 'annee');

        // Années des avances
        $sqlA = "SELECT DISTINCT YEAR(date_avance) as annee FROM avances";
        $resA = $this->db->fetchAll($sqlA);
        $yearsA = array_column($resA, 'annee');

        // Fusionner, dédoublonner et trier
        $years = array_unique(array_merge($yearsV, $yearsA));
        
        if (empty($years)) {
            $years = [(int)date('Y')];
        } else {
            rsort($years); // Décroissant pour le dashboard
        }

        return $years;
    }
}
