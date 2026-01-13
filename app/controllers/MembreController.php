<?php
/**
 * Contrôleur des membres
 * Gestion CRUD des membres
 */

class MembreController extends Controller
{
    private Membre $membreModel;
    private Versement $versementModel;
    private Avance $avanceModel;

    public function __construct()
    {
        $this->membreModel = new Membre();
        $this->versementModel = new Versement();
        $this->avanceModel = new Avance();
    }

    /**
     * Liste des membres
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $statut = $this->get('statut');
        $search = $this->get('search');
        $situation = $this->get('situation');
        $hasAmende = $this->get('has_amende');

        if ($search) {
            $membres = $this->membreModel->search($search);
        } elseif ($statut) {
            $membres = $this->membreModel->getByStatut($statut);
        } else {
            $membres = $this->membreModel->findAll([], 'designation ASC');
        }

        $filteredMembres = [];
        // Ajouter les calculs pour chaque membre
        foreach ($membres as &$membre) {
            $membre['versements'] = $this->versementModel->getByMembre($membre['id']);
            $membre['avances'] = $this->avanceModel->getByMembre($membre['id']);
            
            $sit = $this->membreModel->getSituationFinanciere($membre);
            $membre = array_merge($membre, $sit);

            // Appliquer les filtres financiers
            $matchSituation = true;
            if ($situation === 'a_jour') {
                $matchSituation = ($membre['montant_du'] <= 0);
            } elseif ($situation === 'en_retard') {
                $matchSituation = ($membre['montant_du'] > 0);
            }

            $matchAmende = true;
            if ($hasAmende === 'yes') {
                $matchAmende = ($membre['amende'] > 0);
            } elseif ($hasAmende === 'no') {
                $matchAmende = ($membre['amende'] <= 0);
            }

            if ($matchSituation && $matchAmende) {
                $filteredMembres[] = $membre;
            }
        }

        $data = [
            'membres' => $filteredMembres,
            'currentStatut' => $statut,
            'currentSearch' => $search,
            'currentSituation' => $situation,
            'currentHasAmende' => $hasAmende,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('membres/index', $data);
    }

    /**
     * Détail d'un membre
     */
    public function show(): void
    {
        $this->requireAuth();

        $id = (int) $this->get('id');
        $annee = $this->get('annee') ? (int) $this->get('annee') : (int) date('Y');

        $membre = $this->membreModel->getWithRelations($id, $annee);
        
        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        // Sécurité supplémentaire : Un membre ne peut voir que son propre détail
        $currentUser = $this->getCurrentUser();
        if ($currentUser['role'] === 'membre' && $membre['user_id'] != $currentUser['id']) {
            $this->setFlash('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/dashboard');
        }

        $globalSituation = $this->membreModel->getWithRelations($id);
        $availableYears = $this->membreModel->getAvailableYears($id);
        $lastRetardDate = $this->versementModel->getLastRetardDate($id);

        // Nettoyage de la vue : On pré-calcule les listes filtrées
        $avances = array_filter($membre['avances'] ?? [], fn($a) => ($a['type'] ?? 'AVANCE') === 'AVANCE');
        $anticipations = array_filter($membre['avances'] ?? [], fn($a) => ($a['type'] ?? 'AVANCE') === 'ANTICIPATION');

        $data = [
            'membre' => $membre,
            'globalSituation' => $globalSituation,
            'availableYears' => $availableYears,
            'currentUser' => $currentUser,
            'annee' => $annee,
            'lastRetardDate' => $lastRetardDate,
            'lists' => [
                'avances' => $avances,
                'anticipations' => $anticipations,
                'total_avances' => array_sum(array_column($avances, 'montant')),
                'total_anticipations' => array_sum(array_column($anticipations, 'montant'))
            ]
        ];

        $this->render('membres/show', $data);
    }

    /**
     * Formulaire de création
     */
    public function create(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $data = [
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('membres/form', $data);
    }

    /**
     * Enregistrer un nouveau membre
     */
    public function store(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/membres/create');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/membres/create');
        }

        // Génération automatique du numéro et du code
        $numero = $this->membreModel->getNextNumero();
        $code = 'N°-' . str_pad($numero, 4, '0', STR_PAD_LEFT) . '-' . date('Y');

        $data = [
            'numero' => $numero,
            'code' => $code,
            'telephone' => $this->sanitize($this->post('telephone')),
            'titre' => $this->sanitize($this->post('titre')),
            'designation' => $this->sanitize($this->post('designation')),
            'misside' => $this->sanitize($this->post('misside')),
            'montant_mensuel' => (float) $this->post('montant_mensuel'),
            'statut' => $this->post('statut', 'ACTIF')
        ];

        // Validation
        if (empty($data['designation'])) {
            $this->setFlash('error', 'La désignation est obligatoire.');
            $this->redirect(BASE_URL . '/membres/create');
        }

        // Vérifier si le code existe déjà (théoriquement non si auto-généré, mais sécurité)
        if ($this->membreModel->codeExists($data['code'])) {
            $this->setFlash('error', 'Erreur de génération : Ce code membre existe déjà.');
            $this->redirect(BASE_URL . '/membres/create');
        }

        try {
            $db = Database::get();
            $db->beginTransaction();

            // 1. Créer le compte utilisateur pour le membre
            // Username = Numéro de téléphone, Password par défaut = 123456
            $utilisateurModel = new Utilisateur();
            $username = !empty($data['telephone']) ? $data['telephone'] : $data['code'];
            $userId = $utilisateurModel->createUser($username, '123456', 'membre');

            // 2. Créer le membre avec le lien user_id
            $data['user_id'] = $userId;
            $id = $this->membreModel->createMembre($data);
            
            $db->commit();

            $msg = 'Membre créé avec succès. Un compte utilisateur a été généré (Identifiant: ' . $username . ', MDP: 123456).';
            $this->setFlash('success', $msg);
            
            $this->redirect(BASE_URL . '/membres/show?id=' . $id);
        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            $this->setFlash('error', 'Erreur lors de la création du membre : ' . $e->getMessage());
            $this->redirect(BASE_URL . '/membres/create');
        }
    }

    /**
     * Créer un compte utilisateur pour un membre existant
     */
    public function createAccount(): void
    {
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/membres');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/membres');
        }

        $id = (int) $this->post('id');
        $membre = $this->membreModel->find($id);

        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        if ($membre['user_id']) {
            $this->setFlash('error', 'Ce membre a déjà un compte utilisateur.');
            $this->redirect(BASE_URL . '/membres/show?id=' . $id);
        }

        try {
            $db = Database::get();
            $db->beginTransaction();

            $utilisateurModel = new Utilisateur();
            $username = !empty($membre['telephone']) ? $membre['telephone'] : $membre['code'];
            $userId = $utilisateurModel->createUser($username, '123456', 'membre');

            $this->membreModel->updateMembre($id, ['user_id' => $userId]);

            $db->commit();

            $this->setFlash('success', 'Compte utilisateur créé avec succès. Identifiant: ' . $username . ', MDP: 123456');
        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            $this->setFlash('error', 'Erreur lors de la création du compte : ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/membres/show?id=' . $id);
    }

    /**
     * Formulaire d'édition
     */
    public function edit(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $id = (int) $this->get('id');
        $membre = $this->membreModel->find($id);

        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        $data = [
            'membre' => $membre,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('membres/form', $data);
    }

    /**
     * Mettre à jour un membre
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

        // Récupérer les anciennes valeurs pour numero/code car on ne veut pas les changer via form
        // On ignore simplement les valeurs POSTées pour ces champs pour garantir l'intégrité
        
        $data = [
            'telephone' => $this->sanitize($this->post('telephone')),
            'titre' => $this->sanitize($this->post('titre')),
            'designation' => $this->sanitize($this->post('designation')),
            'misside' => $this->sanitize($this->post('misside')),
            'montant_mensuel' => (float) $this->post('montant_mensuel'),
            'statut' => $this->post('statut')
        ];

        try {
            $this->membreModel->updateMembre($id, $data);
            
            $this->setFlash('success', 'Membre mis à jour avec succès.');

            $this->redirect(BASE_URL . '/membres/show?id=' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour du membre.');
            $this->redirect(BASE_URL . '/membres/edit?id=' . $id);
        }
    }

    /**
     * Supprimer un membre
     */
    public function delete(): void
    {
        $this->requireRole(['admin']);

        $id = (int) $this->post('id');

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/membres');
        }

        try {
            $this->membreModel->delete($id);
            $this->setFlash('success', 'Membre supprimé avec succès.');
            $this->redirect(BASE_URL . '/membres');
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la suppression.');
            $this->redirect(BASE_URL . '/membres');
        }
    }

    /**
     * Changer le statut d'un membre
     */
    public function changeStatut(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');
        $statut = $this->post('statut');

        if (!in_array($statut, ['ACTIF', 'VG', 'SUSPENDU'], true)) {
            $this->json(['success' => false, 'message' => 'Statut invalide'], 400);
        }

        try {
            $this->membreModel->update($id, ['statut' => $statut]);
            $this->json(['success' => true, 'message' => 'Statut mis à jour avec succès.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * Mettre à jour la configuration financière (Retards/Avances)
     */
    public function update_financial_config(): void
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
        
        // Gestion des retards automatiques
        $moisRetard = (int) $this->post('mois_retard', 0);
        $dateFinale = $this->post('date_finale');
        $resetRetards = $this->post('reset_retards') === '1'; // Checkbox
        $avanceInitiale = (float) $this->post('avance_initiale', 0);
        
        $messages = [];

        try {
            if ($moisRetard > 0 && !empty($dateFinale)) {
                try {
                    if ($resetRetards) {
                        $deletedCount = $this->versementModel->deleteAllCurrentRetards($id);
                        $messages[] = "Remise à zéro : {$deletedCount} ancien(s) retard(s) supprimé(s).";
                    }

                    $versementsCreated = $this->versementModel->createBulkUnpaidVersements($id, $moisRetard, $dateFinale);
                    if ($versementsCreated > 0) {
                        $messages[] = "{$versementsCreated} nouveau(x) versement(s) en retard généré(s).";
                    } else {
                        $messages[] = "Aucun nouveau retard généré (existent déjà ?).";
                    }
                } catch (Exception $e) {
                    $this->setFlash('warning', "Erreur lors de la génération des retards : " . $e->getMessage());
                }
            }

            if ($avanceInitiale > 0) {
                try {
                    $type = $this->post('type', 'AVANCE'); // AVANCE ou ANTICIPATION
                    $dateDebut = $this->post('date_debut'); // Nouveau champ
                    $motif = $type === 'ANTICIPATION' ? "Anticipation ajoutée via configuration" : "Avance ajoutée via configuration";
                    
                    $this->avanceModel->createAvance($id, $avanceInitiale, $motif, null, $type, $dateDebut);
                    
                    $typeName = $type === 'ANTICIPATION' ? 'Anticipation' : 'Avance';
                    $messages[] = "{$typeName} de " . number_format($avanceInitiale, 0, ',', ' ') . " FCFA ajoutée.";
                } catch (Exception $e) {
                    $this->setFlash('warning', "Erreur lors de l'ajout : " . $e->getMessage());
                }
            }

            if (empty($messages)) {
                $this->setFlash('info', "Aucune modification financière n'a été appliquée.");
            } else {
                $this->setFlash('success', "Configuration appliquée : " . implode(' ', $messages));
            }

        } catch (Exception $e) {
            $this->setFlash('error', "Erreur technique : " . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/membres/show?id=' . $id);
    }
}
