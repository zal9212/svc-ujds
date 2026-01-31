<?php
/**
 * Contrôleur d'authentification
 * Gestion de la connexion et déconnexion
 */

class AuthController extends Controller
{
    private Utilisateur $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new Utilisateur();
    }

    /**
     * Afficher le formulaire de connexion
     */
    public function login(): void
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }

        $this->render('auth/login', [], null);
    }

    /**
     * Traiter la connexion
     */
    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/login');
        }

        // Valider le CSRF
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/login');
        }

        $username = $this->post('username');
        $password = $this->post('password');

        // Validation
        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect(BASE_URL . '/login');
        }

        // Rechercher l'utilisateur
        $user = $this->utilisateurModel->findByUsername($username);

        if (!$user) {
            $this->setFlash('error', 'Nom d\'utilisateur ou mot de passe incorrect.');
            $this->redirect(BASE_URL . '/login');
        }

        // Vérifier le mot de passe
        if (!$this->utilisateurModel->verifierMotDePasse($password, $user['password'])) {
            $this->setFlash('error', 'Nom d\'utilisateur ou mot de passe incorrect.');
            $this->redirect(BASE_URL . '/login');
        }

        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        // Si c'est un membre, on récupère sa photo de profil pour le header
        if ($user['role'] === 'membre') {
            $membreModel = new Membre();
            $membres = $membreModel->findAll(['user_id' => $user['id']], '', 1);
            if (!empty($membres)) {
                $_SESSION['user_photo'] = $membres[0]['photo_profil'];
                $_SESSION['member_id'] = $membres[0]['id'];
            }
        }

        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);

        $this->setFlash('success', 'Connexion réussie. Bienvenue ' . $user['username'] . '!');
        $this->redirect(BASE_URL . '/dashboard');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function changePassword(): void
    {
        $this->requireAuth();
        $this->render('auth/change_password', [
            'currentUser' => $this->getCurrentUser()
        ]);
    }

    /**
     * Traiter le changement de mot de passe
     */
    public function updatePasswordPost(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Le nouveau mot de passe et sa confirmation ne correspondent pas.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        if (strlen($newPassword) < 6) {
            $this->setFlash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        $user = $this->utilisateurModel->find($_SESSION['user_id']);

        if (!$user || !$this->utilisateurModel->verifierMotDePasse($currentPassword, $user['password'])) {
            $this->setFlash('error', 'Le mot de passe actuel est incorrect.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }

        if ($this->utilisateurModel->updatePassword((int)$_SESSION['user_id'], $newPassword)) {
            $this->setFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            $this->redirect(BASE_URL . '/dashboard');
        } else {
            $this->setFlash('error', 'Une erreur est survenue lors de la mise à jour du mot de passe.');
            $this->redirect(BASE_URL . '/auth/changePassword');
        }
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        // Détruire la session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();

        $this->redirect(BASE_URL . '/login');
    }
}
