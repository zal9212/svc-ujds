<?php
/**
 * Contrôleur de Support (Chat Global)
 * Gère les discussions générales entre membres et admin
 */

class SupportController extends Controller
{
    private SupportMessage $messageModel;
    private Membre $membreModel;

    public function __construct()
    {
        $this->messageModel = new SupportMessage();
        $this->membreModel = new Membre();
    }

    /**
     * Interface de chat pour le membre
     */
    public function index(): void
    {
        $this->requireRole(['membre']);
        $user = $this->getCurrentUser();
        $membre = $this->membreModel->findByUserId($user['id']);

        if (!$membre) {
            $this->setFlash('error', 'Compte non lié à un membre.');
            $this->redirect(BASE_URL . '/dashboard');
        }

        $messages = $this->messageModel->getByMembre($membre['id']);

        $this->render('support/index', [
            'membre' => $membre,
            'messages' => $messages,
            'currentUser' => $user,
            'title' => 'Chat de Support'
        ]);
    }

    /**
     * Liste des conversations pour l'admin
     */
    public function adminList(): void
    {
        $this->requireRole(['admin', 'comptable']);
        $conversations = $this->messageModel->getLatestConversations();

        $this->render('support/admin_list', [
            'conversations' => $conversations,
            'currentUser' => $this->getCurrentUser(),
            'title' => 'Conversations Support'
        ]);
    }

    /**
     * Voir une conversation spécifique (Admin)
     */
    public function view(): void
    {
        $this->requireRole(['admin', 'comptable']);
        $membreId = (int) $this->get('membre_id');
        
        $membre = $this->membreModel->find($membreId);
        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/support/admin');
        }

        $messages = $this->messageModel->getByMembre($membreId);

        $this->render('support/index', [
            'membre' => $membre,
            'messages' => $messages,
            'currentUser' => $this->getCurrentUser(),
            'isAdminView' => true,
            'title' => 'Chat with ' . $membre['designation']
        ]);
    }

    /**
     * Envoyer un message
     */
    public function send(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect(BASE_URL . '/support');
        }
        $user = $this->getCurrentUser();
        
        $membreId = (int) $this->post('membre_id');
        $message = $this->post('message', '');

        // Sécurité membre : peut seulement envoyer à soi-même
        if ($user['role'] === 'membre') {
            $membre = $this->membreModel->findByUserId($user['id']);
            if (!$membre || $membre['id'] != $membreId) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
                }
                $this->redirect(BASE_URL . '/support');
            }
        }

        // Upload d'image (réutilisation de la logique de DeclarationController)
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
                'membre_id' => $membreId,
                'sender_id' => $user['id'],
                'message' => Security::sanitize((string)$message),
                'image_path' => $imagePath,
                'audio_path' => $audioPath
            ]);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        if ($user['role'] === 'membre') {
            $this->redirect(BASE_URL . '/support');
        } else {
            $this->redirect(BASE_URL . '/support/view?membre_id=' . $membreId);
        }
    }

    /**
     * Supprimer plusieurs messages
     */
    public function deleteMultiple(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $messageIds = $this->post('ids');

        if (empty($messageIds) || !is_array($messageIds)) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/support');
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
        
        $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/support');
    }

    /**
     * Effacer toute la discussion (Admin uniquement)
     */
    public function clearChat(): void
    {
        $this->requireRole(['admin']);
        $membreId = (int)$this->post('membre_id');
        
        if ($membreId > 0) {
            $messages = $this->messageModel->getByMembre($membreId);
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
        
        $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/support');
    }
}
