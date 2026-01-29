<?php
/**
 * Contrôleur des Déclarations de Paiement
 * Gère le cycle de vie des déclarations : soumission, chat, validation
 */

class DeclarationController extends Controller
{
    private DeclarationPaiement $declarationModel;
    private DeclarationMessage $messageModel;
    private Membre $membreModel;
    private Versement $versementModel;
    private Avance $avanceModel;

    public function __construct()
    {
        $this->declarationModel = new DeclarationPaiement();
        $this->messageModel = new DeclarationMessage();
        $this->membreModel = new Membre();
        $this->versementModel = new Versement();
        $this->avanceModel = new Avance();
    }

    /**
     * Dashboard des déclarations pour le membre
     */
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        
        // Trouver le membre associé à l'utilisateur
        $membre = $this->membreModel->findByUserId($user['id']);
        
        if (!$membre && $user['role'] === 'membre') {
            $this->setFlash('error', 'Aucun membre associé à votre compte.');
            $this->redirect(BASE_URL . '/dashboard');
        }

        $declarations = [];
        if ($membre) {
            $declarations = $this->declarationModel->getByMembre($membre['id']);
        } elseif ($user['role'] !== 'membre') {
            // Admin/Comptable : Rediriger vers la liste admin s'ils accèdent à l'index
            $this->redirect(BASE_URL . '/declarations/admin');
        }

        $this->render('declarations/index', [
            'declarations' => $declarations,
            'membre' => $membre,
            'currentUser' => $user,
            'title' => 'Mes Déclarations de Paiement'
        ]);
    }

    /**
     * Formulaire de soumission
     */
    public function submit(): void
    {
        $this->requireRole(['membre']);
        $user = $this->getCurrentUser();
        $membre = $this->membreModel->findByUserId($user['id']);

        if (!$membre) {
            $this->setFlash('error', 'Aucun membre associé.');
            $this->redirect(BASE_URL . '/dashboard');
        }

        // Vérifier si le mois en cours est déjà payé
        $moisList = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $currentMois = $moisList[date('n') - 1];
        $currentAnnee = (int) date('Y');
        $isCurrentMonthPaid = $this->versementModel->isMonthPaid($membre['id'], $currentMois, $currentAnnee);

        $this->render('declarations/submit', [
            'membre' => $membre,
            'isCurrentMonthPaid' => $isCurrentMonthPaid,
            'currentUser' => $user,
            'title' => 'Déclarer un Paiement'
        ]);
    }

    /**
     * Enregistrer une nouvelle déclaration
     */
    public function store(): void
    {
        $this->requireRole(['membre']);
        $user = $this->getCurrentUser();
        $membre = $this->membreModel->findByUserId($user['id']);

        if (!$membre) {
            $this->redirect(BASE_URL . '/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect(BASE_URL . '/declarations');
        }

        $montant = (float) $this->post('montant');
        $type = $this->post('type_paiement');
        $message = $this->post('message');

        // Gestion de l'upload de preuve
        $preuvePath = null;
        if (isset($_FILES['preuve']) && $_FILES['preuve']['error'] === UPLOAD_ERR_OK) {
            $relativeUploadDir = 'uploads/declarations/';
            $uploadDir = PUBLIC_PATH . '/' . $relativeUploadDir;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = Security::sanitizeFilename(time() . '_' . $_FILES['preuve']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['preuve']['tmp_name'], $targetPath)) {
                $preuvePath = $relativeUploadDir . $filename;
            }
        }

        $declarationId = $this->declarationModel->create([
            'membre_id' => $membre['id'],
            'montant' => $montant,
            'type_paiement' => $type,
            'preuve_path' => $preuvePath,
            'statut' => 'EN_ATTENTE'
        ]);

        if ($declarationId && !empty($message)) {
            $this->messageModel->create([
                'declaration_id' => $declarationId,
                'sender_id' => $user['id'],
                'message' => $this->sanitize($message)
            ]);
        }

        $this->setFlash('success', 'Votre déclaration a été soumise avec succès.');
        $this->redirect(BASE_URL . '/declarations/show?id=' . $declarationId);
    }

    /**
     * Voir une déclaration et le chat
     */
    public function show(): void
    {
        $this->requireAuth();
        $id = (int) $this->get('id');
        $user = $this->getCurrentUser();

        $declaration = $this->declarationModel->getWithMembre($id);
        if (!$declaration) {
            $this->setFlash('error', 'Déclaration non trouvée.');
            $this->redirect(BASE_URL . '/declarations');
        }

        // Sécurité : Un membre ne peut voir que ses propres déclarations
        if ($user['role'] === 'membre') {
            $membre = $this->membreModel->findByUserId($user['id']);
            if (!$membre || $declaration['membre_id'] != $membre['id']) {
                $this->setFlash('error', 'Accès non autorisé.');
                $this->redirect(BASE_URL . '/declarations');
            }
        }

        $messages = $this->messageModel->getByDeclaration($id);

        $this->render('declarations/show', [
            'declaration' => $declaration,
            'messages' => $messages,
            'currentUser' => $user,
            'title' => 'Suivi de la Déclaration #' . $id
        ]);
    }

    /**
     * Envoyer un message dans le chat
     */
    public function sendMessage(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect(BASE_URL . '/declarations');
        }
        $user = $this->getCurrentUser();
        $id = (int) $this->post('declaration_id');
        $message = $this->post('message', '');

        $declaration = $this->declarationModel->find($id);
        if (!$declaration) {
            $this->redirect(BASE_URL . '/declarations');
        }

        // Sécurité : Un membre ne peut envoyer des messages que sur ses propres déclarations
        if ($user['role'] === 'membre') {
            $membre = $this->membreModel->findByUserId($user['id']);
            if (!$membre || $declaration['membre_id'] != $membre['id']) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
                }
                $this->redirect(BASE_URL . '/declarations');
            }
        }

        // Upload d'image
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $relativeUploadDir = 'uploads/chat/';
            $uploadDir = PUBLIC_PATH . '/' . $relativeUploadDir;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = Security::sanitizeFilename(time() . '_' . $_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = $relativeUploadDir . $filename;
            }
        }

        // Upload d'audio (Vocal)
        $audioPath = null;
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $relativeUploadDir = 'uploads/chat/audio/';
            $uploadDir = PUBLIC_PATH . '/' . $relativeUploadDir;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            // Détection de l'extension depuis le nom du fichier envoyé
            $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
            if (empty($ext)) $ext = 'webm';
            
            $filename = Security::sanitizeFilename(time() . '_vocal.' . $ext);
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadDir . $filename)) {
                $audioPath = $relativeUploadDir . $filename;
            }
        }

        if (!empty($message) || $imagePath || $audioPath) {
            $this->messageModel->create([
                'declaration_id' => $id,
                'sender_id' => $user['id'],
                'message' => Security::sanitize((string)$message),
                'image_path' => $imagePath,
                'audio_path' => $audioPath
            ]);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $this->redirect(BASE_URL . '/declarations/show?id=' . $id);
    }

    /**
     * Liste admin des déclarations en attente
     */
    public function adminList(): void
    {
        $this->requireRole(['admin', 'comptable']);
        $declarations = $this->declarationModel->getPending();

        $this->render('declarations/admin_list', [
            'declarations' => $declarations,
            'currentUser' => $this->getCurrentUser(),
            'title' => 'Paiements en Attente de Validation'
        ]);
    }

    /**
     * Valider une déclaration
     */
    public function validate(): void
    {
        $this->requireRole(['admin', 'comptable']);
        if (!$this->validateCsrf()) $this->redirect(BASE_URL . '/declarations/admin');

        $id = (int) $this->post('id');
        $declaration = $this->declarationModel->find($id);

        if ($declaration && $declaration['statut'] === 'EN_ATTENTE') {
            try {
                $db = Database::get();
                $db->beginTransaction();

                // 1. Marquer comme valide
                $this->declarationModel->update($id, ['statut' => 'VALIDE']);

                // 2. Appliquer la logique financière
                $membreId = $declaration['membre_id'];
                $montant = $declaration['montant'];
                $type = $declaration['type_paiement'];

                if ($type === 'mois_en_cours') {
                    // Trouver le mois en cours
                    $moisList = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                    $mois = $moisList[date('n') - 1];
                    $annee = (int) date('Y');
                    
                    if ($this->versementModel->versementExists($membreId, $mois, $annee)) {
                        $v = $db->fetchOne("SELECT id FROM versements WHERE membre_id = ? AND mois = ? AND annee = ?", [$membreId, $mois, $annee]);
                        $this->versementModel->update($v['id'], [
                            'montant' => $montant,
                            'statut' => 'PAYE',
                            'date_paiement' => date('Y-m-d'),
                            'declaration_id' => $id
                        ]);
                    } else {
                        // On crée manuellement pour inclure declaration_id
                        $this->versementModel->create([
                            'membre_id' => $membreId,
                            'mois' => $mois,
                            'annee' => $annee,
                            'montant' => $montant,
                            'statut' => 'PAYE',
                            'date_paiement' => date('Y-m-d'),
                            'declaration_id' => $id
                        ]);
                    }
                } elseif ($type === 'dette_anterieure') {
                    $this->avanceModel->create([
                        'membre_id' => $membreId,
                        'montant' => $montant,
                        'motif' => "Règlement dette via déclaration #" . $id,
                        'date_avance' => date('Y-m-d'),
                        'type' => 'AVANCE',
                        'declaration_id' => $id
                    ]);
                } elseif ($type === 'avance_mois' || $type === 'avance_annee' || $type === 'anticipation') {
                    $this->avanceModel->create([
                        'membre_id' => $membreId,
                        'montant' => $montant,
                        'motif' => "Avance via déclaration #" . $id,
                        'date_avance' => date('Y-m-d'),
                        'type' => 'ANTICIPATION',
                        'declaration_id' => $id
                    ]);
                }

                $db->commit();
                $this->setFlash('success', 'Déclaration validée et compte membre mis à jour.');
            } catch (Exception $e) {
                $db->rollBack();
                $this->setFlash('error', 'Erreur lors de la validation : ' . $e->getMessage());
            }
        }

        $this->redirect(BASE_URL . '/declarations/show?id=' . $id);
    }

    /**
     * Rejeter une déclaration
     */
    public function reject(): void
    {
        $this->requireRole(['admin', 'comptable']);
        if (!$this->validateCsrf()) $this->redirect(BASE_URL . '/declarations/admin');

        $id = (int) $this->post('id');
        $this->declarationModel->update($id, ['statut' => 'REJETE']);
        

        $this->setFlash('success', 'Déclaration rejetée.');
        $this->redirect(BASE_URL . '/declarations/show?id=' . $id);
    }

    /**
     * Supprimer plusieurs messages du chat
     */
    public function deleteMessages(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $messageIds = $this->post('ids');

        if (empty($messageIds) || !is_array($messageIds)) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/declarations');
        }

        $deletedCount = 0;
        foreach ($messageIds as $id) {
            $message = $this->messageModel->find((int)$id);
            if ($message) {
                // SEUL l'expéditeur ou un ADMIN peut supprimer
                if ($user['role'] === 'admin' || $user['id'] == $message['sender_id']) {
                    // Suppression fichiers
                    if ($message['image_path']) {
                        $p = PUBLIC_PATH . '/' . $message['image_path'];
                        if (file_exists($p)) @unlink($p);
                    }
                    if ($message['audio_path']) {
                        $p = PUBLIC_PATH . '/' . $message['audio_path'];
                        if (file_exists($p)) @unlink($p);
                    }
                    $this->messageModel->delete((int)$id);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $this->setFlash('success', "$deletedCount message(s) supprimé(s).");
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/declarations');
    }

    /**
     * Effacer toute la discussion (Admin uniquement)
     */
    public function clearChat(): void
    {
        $this->requireAdmin();
        $declarationId = (int)$this->post('declaration_id');
        
        if ($declarationId > 0) {
            $messages = $this->messageModel->getByDeclaration($declarationId);
            foreach ($messages as $m) {
                // Suppression fichiers
                if ($m['image_path']) {
                    $p = PUBLIC_PATH . '/' . $m['image_path'];
                    if (file_exists($p)) @unlink($p);
                }
                if ($m['audio_path']) {
                    $p = PUBLIC_PATH . '/' . $m['audio_path'];
                    if (file_exists($p)) @unlink($p);
                }
                $this->messageModel->delete((int)$m['id']);
            }
            $this->setFlash('success', "Discussion effacée.");
        }
        
        $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/declarations');
    }
}
?>
