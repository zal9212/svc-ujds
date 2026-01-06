<?php
/**
 * Contrôleur des versements
 * Gestion des paiements mensuels
 */

class VersementController extends Controller
{
    private Versement $versementModel;
    private Membre $membreModel;
    private Database $db;

    public function __construct()
    {
        $this->versementModel = new Versement();
        $this->membreModel = new Membre();
        $this->db = Database::get();
    }

    /**
     * Liste des versements
     */
    public function index(): void
    {
        $this->requireAuth();

        $membreId = $this->get('membre_id');
        $statut = $this->get('statut');

        if ($membreId) {
            $membre = $this->membreModel->find((int) $membreId);
            
            if (!$membre) {
                $this->setFlash('error', 'Membre non trouvé.');
                $this->redirect(BASE_URL . '/versements');
            }

            // Sécurité : Un membre ne peut voir que son propre historique
            $currentUser = $this->getCurrentUser();
            if ($currentUser['role'] === 'membre' && $membre['user_id'] != $currentUser['id']) {
                $this->setFlash('error', 'Accès non autorisé.');
                $this->redirect(BASE_URL . '/dashboard');
            }

            $versements = $this->versementModel->getByMembre((int) $membreId);
            $membres = [];
        } else {
            // Liste globale réservée aux admin/comptables
            $this->requireRole(['admin', 'comptable']);
            
            if ($statut) {
                $sql = "SELECT DISTINCT m.* 
                        FROM membres m 
                        JOIN versements v ON m.id = v.membre_id 
                        WHERE v.statut = ? 
                        ORDER BY m.designation ASC";
                $membres = $this->db->fetchAll($sql, [$statut]);
                $versements = [];
                $membre = null;
            } else {
                // Liste unique des membres
                $membres = $this->membreModel->findAll([], 'designation ASC');
                $versements = [];
                $membre = null;
            }
        }

        $data = [
            'versements' => $versements,
            'membre' => $membre,
            'membres' => $membres,
            'currentStatut' => $statut,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('versements/index', $data);
    }

    /**
     * Créer un versement (Vue Grille)
     */
    public function create(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $membreId = (int) $this->get('membre_id');
        $annee = $this->get('annee') ? (int) $this->get('annee') : (int) date('Y');
        $mode = $this->get('mode') === 'amende' ? 'amende' : 'versement';

        $membre = $this->membreModel->find($membreId);

        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        // Récupérer les versements existants pour l'année (y compris virtuels/anticipés via getWithRelations)
        $membreData = $this->membreModel->getWithRelations($membreId, $annee);
        $versements = $membreData['versements'] ?? [];
        
        $paymentsMap = [];
        foreach ($versements as $p) {
            $paymentsMap[$p['mois']] = $p;
        }

        $data = [
            'membre' => $membre,
            'currentUser' => $this->getCurrentUser(),
            'annee' => $annee,
            'paymentsMap' => $paymentsMap,
            'mode' => $mode
        ];

        $this->render('versements/form', $data);
    }

    /**
     * Enregistrer les versements (Bulk Update)
     */
    public function store(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/membres');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/membres');
        }

        $membreId = (int) $this->post('membre_id');
        $annee = (int) $this->post('annee');
        $mode = $this->post('mode') === 'amende' ? 'amende' : 'versement';
        
        // Récupérer le membre pour avoir le montant mensuel
        $membre = $this->membreModel->find($membreId);
        if (!$membre) {
            $this->redirect(BASE_URL . '/membres');
        }

        $moisList = [
            'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
        ];

        try {
            $this->db->beginTransaction();

            // Récupérer le membre AVEC ses relations pour que getSituationFinanciere fonctionne
            // getWithRelations retourne déjà le tableau enrichi, mais on peut aussi juste peupler manuellement
            // pour éviter la logique d'affichage de getWithRelations.
            // On va faire simple:
            $membre['versements'] = $this->versementModel->getByMembre($membreId);
            $membre['avances'] = (new Avance())->getByMembre($membreId);

            // Récupérer la situation financière actuelle pour identifier les mois couverts par l'avance
            $situation = $this->membreModel->getSituationFinanciere($membre);
            $reconciledInfo = $situation['reconciled'] ?? [];
            // Les virtuels ne sont pas dans 'reconciled' par mois textuel, mais on peut les déduire ou checker reconciled
            // Correction: getSituationFinanciere retourne 'reconciled' indexé par ID (int ou string virt_...)
            // Il faut mapper mois/année -> statut dynamique
            
            $dynamicStatusMap = [];
            foreach ($reconciledInfo as $id => $info) {
                // Trouver le mois/année correspondant à cet ID
                // Si c'est un versement réel
                if (is_numeric($id)) {
                    $v = $this->versementModel->find($id);
                    if ($v) {
                        $key = $v['mois'] . '-' . $v['annee'];
                        $dynamicStatusMap[$key] = $info['display_statut'];
                    }
                } elseif (strpos($id, 'virt_') === 0) {
                     // Virtuel: on doit retrouver le mois/année depuis l'info ou le parsing de l'ID ?
                     // L'ID est virt_ANNEE_MOISINDEX. Mais on a pas le mois textuel direct dans l'ID.
                     // Heureusement situation['virtual_versements'] contient tout.
                }
            }

            // Mieux: utiliser virtual_versements pour les futurs
            foreach ($situation['virtual_versements'] ?? [] as $virt) {
                 $key = $virt['mois'] . '-' . $virt['annee'];
                 $dynamicStatusMap[$key] = $virt['statut'];
            }

            foreach ($moisList as $mois) {
                // Vérifier l'état actuel en base
                $sql = "SELECT id, statut, has_amende FROM versements WHERE membre_id = ? AND mois = ? AND annee = ?";
                $existing = $this->db->fetchOne($sql, [$membreId, $mois, $annee]);

                $monthKey = $mois . '-' . $annee;
                $dynamicStatus = $dynamicStatusMap[$monthKey] ?? ($existing['statut'] ?? 'EN_ATTENTE');
                
                // Est-ce couvert par l'avance ?
                $isCoveredByAdvance = in_array($dynamicStatus, ['PAYE (AVANCE)', 'PARTIEL (AVANCE)', 'ANTICIPATION', 'ANTICIPATION (PARTIEL)']);

                if ($mode === 'versement') {
                    // --- MODE VERSEMENT ---
                    $isPaid = isset($_POST['months'][$mois]);
                    
                    if ($isPaid) {
                        // Si c'est couvert par l'avance, ON NE TOUCHE PAS à la BDD (on laisse le dynamique faire)
                        if ($isCoveredByAdvance) {
                            continue; 
                        }

                        // Sinon, c'est un paiement manuel (Cash) -> On enregistre
                        // COCHÉ: Payer et enlever amende (Exclusivité)
                        if (!$existing) {
                            $this->versementModel->create([
                                'membre_id' => $membreId,
                                'mois' => $mois,
                                'annee' => $annee,
                                'montant' => $membre['montant_mensuel'],
                                'statut' => 'PAYE',
                                'has_amende' => 0, // Pas d'amende si payé
                                'date_paiement' => date('Y-m-d')
                            ]);
                        } else {
                            $this->versementModel->update($existing['id'], [
                                'statut' => 'PAYE',
                                'montant' => $membre['montant_mensuel'],
                                'has_amende' => 0, // Reset amende
                                'date_paiement' => date('Y-m-d')
                            ]);
                        }
                    } else {
                        // DÉCOCHÉ
                        if ($existing && $existing['statut'] === 'PAYE') {
                            $this->versementModel->update($existing['id'], [
                                'statut' => 'EN_ATTENTE',
                                'montant' => 0,
                                'date_paiement' => null
                            ]);
                        }
                         if (!$existing) {
                            $this->versementModel->create([
                                'membre_id' => $membreId,
                                'mois' => $mois,
                                'annee' => $annee,
                                'montant' => 0,
                                'statut' => 'EN_ATTENTE',
                                'has_amende' => 0
                            ]);
                         }
                    }

                } elseif ($mode === 'amende') {
                    // --- MODE AMENDE ---
                    $hasAmende = isset($_POST['amendes'][$mois]);
                    
                    if ($hasAmende) {
                        // COCHÉ: Mettre Amende et forcer Statut AMENDE (Exclusivité)
                        if (!$existing) {
                            $this->versementModel->create([
                                'membre_id' => $membreId,
                                'mois' => $mois,
                                'annee' => $annee,
                                'montant' => -2000, // Montant négatif pour Amende
                                'statut' => 'AMENDE', // Nouveau statut pour AMENDE
                                'has_amende' => 1
                            ]);
                        } else {
                            $this->versementModel->update($existing['id'], [
                                'statut' => 'AMENDE', // Nouveau statut pour AMENDE
                                'montant' => -2000, // Montant négatif pour Amende
                                'date_paiement' => null,
                                'has_amende' => 1
                            ]);
                        }
                    } else {
                        // DÉCOCHÉ: Enlever Amende
                        if ($existing && $existing['has_amende']) {
                            // Si statut était AMENDE, on remet EN_ATTENTE
                            $newStatut = ($existing['statut'] === 'AMENDE') ? 'EN_ATTENTE' : $existing['statut'];
                            
                            $this->versementModel->update($existing['id'], [
                                'has_amende' => 0,
                                'montant' => 0, // Reset montant si on enlève l'amende
                                'statut' => $newStatut
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
            
            // Redirect vers la fiche membre (Idéalement on garde l'année)
            $this->setFlash('success', 'Mise à jour effectuée.');
            $this->redirect(BASE_URL . '/membres/show?id=' . $membreId . '&annee=' . $annee);

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/versements/create?membre_id=' . $membreId . '&annee=' . $annee);
        }
    }

    /**
     * Marquer comme payé (AJAX)
     */
    public function markPaid(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');
        $montant = (float) $this->post('montant');

        try {
            $this->versementModel->marquerPaye($id, $montant);
            $this->json(['success' => true, 'message' => 'Versement marqué comme payé.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * Marquer comme partiel
     */
    public function markPartial(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');
        $montant = (float) $this->post('montant');

        try {
            $this->versementModel->marquerPartiel($id, $montant);
            $this->json(['success' => true, 'message' => 'Versement marqué comme partiel.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * Supprimer un versement (Hard Delete)
     * Utile pour supprimer des retards générés par erreur
     */
    public function delete(): void
    {
        $this->requireRole(['admin']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');
        
        try {
            $this->versementModel->delete($id);
            $this->json(['success' => true, 'message' => 'Versement définitivement supprimé.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression.'], 500);
        }
    }

    /**
     * Annuler un versement
     */
    public function cancel(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');

        try {
            $this->versementModel->annuler($id);
            $this->json(['success' => true, 'message' => 'Versement annulé.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de l\'annulation.'], 500);
        }
    }

    /**
     * Éditer un versement
     */
    public function edit(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $id = (int) $this->get('id');
        $versement = $this->versementModel->find($id);

        if (!$versement) {
            $this->setFlash('error', 'Versement non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        $membre = $this->membreModel->find($versement['membre_id']);

        $data = [
            'versement' => $versement,
            'membre' => $membre,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('versements/edit', $data);
    }

    /**
     * Mettre à jour un versement
     */
    public function update(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/membres');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/membres');
        }

        $id = (int) $this->post('id');
        $versement = $this->versementModel->find($id); // Pour redirection

        if (!$versement) {
             $this->redirect(BASE_URL . '/membres');
        }
        
        $membreId = $versement['membre_id'];

        $mois = $this->post('mois');
        $annee = (int) $this->post('annee');
        $montant = (float) $this->post('montant');
        $statut = $this->post('statut');
        $hasAmende = (int) $this->post('has_amende', 0);

        try {
            $this->versementModel->update($id, [
                'mois' => $mois,
                'annee' => $annee,
                'montant' => $montant,
                'statut' => $statut,
                'has_amende' => $hasAmende,
                'date_paiement' => ($statut === 'PAYE' || $statut === 'PARTIEL') ? date('Y-m-d') : null
            ]);

            $this->setFlash('success', 'Versement mis à jour avec succès.');
            $this->redirect(BASE_URL . '/membres/show?id=' . $membreId . '&annee=' . $annee);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour.');
            $this->redirect(BASE_URL . '/versements/edit?id=' . $id);
        }
    }

    /**
     * Supprimer tous les retards
     */
    public function deleteAllRetards(): void
    {
        $this->requireRole(['admin']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $membreId = (int) $this->post('membre_id');

        try {
            $count = $this->versementModel->deleteAllCurrentRetards($membreId);
            $this->json(['success' => true, 'message' => "{$count} retard(s) supprimé(s)."]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression massive.'], 500);
        }
    }
}
