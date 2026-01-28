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
        $user = $this->getCurrentUser();
        
        $membreId = (int) $this->post('membre_id');
        $message = $this->post('message');

        // Sécurité membre : peut seulement envoyer à soi-même
        if ($user['role'] === 'membre') {
            $membre = $this->membreModel->findByUserId($user['id']);
            if (!$membre || $membre['id'] != $membreId) {
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
                'message' => Security::sanitize($message),
                'image_path' => $imagePath,
                'audio_path' => $audioPath
            ]);
        }

        if ($user['role'] === 'membre') {
            $this->redirect(BASE_URL . '/support');
        } else {
            $this->redirect(BASE_URL . '/support/view?membre_id=' . $membreId);
        }
    }
}
