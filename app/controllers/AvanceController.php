<?php
/**
 * Contrôleur des avances
 * Gestion des avances sur cotisations
 */

class AvanceController extends Controller
{
    private Avance $avanceModel;
    private Membre $membreModel;

    public function __construct()
    {
        $this->avanceModel = new Avance();
        $this->membreModel = new Membre();
    }

    /**
     * Créer une avance
     */
    public function create(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $membreId = (int) $this->get('membre_id');
        $membre = $this->membreModel->find($membreId);

        if (!$membre) {
            $this->setFlash('error', 'Membre non trouvé.');
            $this->redirect(BASE_URL . '/membres');
        }

        $data = [
            'membre' => $membre,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('avances/form', $data);
    }

    /**
     * Enregistrer une avance
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
        $montant = (float) $this->post('montant');
        $motif = $this->sanitize($this->post('motif'));
        $dateAvance = $this->post('date_avance', date('Y-m-d'));

        // Validation
        if ($montant <= 0) {
            $this->setFlash('error', 'Le montant doit être supérieur à zéro.');
            $this->redirect(BASE_URL . '/avances/create?membre_id=' . $membreId);
        }

        try {
            $this->avanceModel->createAvance($membreId, $montant, $motif, $dateAvance);
            $this->setFlash('success', 'Avance enregistrée avec succès.');
            $this->redirect(BASE_URL . '/membres/show?id=' . $membreId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de l\'enregistrement de l\'avance.');
            $this->redirect(BASE_URL . '/avances/create?membre_id=' . $membreId);
        }
    }

    /**
     * Supprimer une avance
     */
    public function delete(): void
    {
        $this->requireRole(['admin']);

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Token invalide'], 403);
        }

        $id = (int) $this->post('id');

        try {
            $this->avanceModel->deleteAvance($id);
            $this->json(['success' => true, 'message' => 'Avance supprimée avec succès.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression.'], 500);
        }
    }

    /**
     * Éditer une avance
     */
    public function edit(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $id = (int) $this->get('id');
        $avance = $this->avanceModel->find($id);

        if (!$avance) {
            $this->setFlash('error', 'Avance non trouvée.');
            $this->redirect(BASE_URL . '/membres');
        }

        $membre = $this->membreModel->find($avance['membre_id']);

        $data = [
            'avance' => $avance,
            'membre' => $membre,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('avances/form', $data);
    }

    /**
     * Mettre à jour une avance
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
        $montant = (float) $this->post('montant');
        $motif = $this->sanitize($this->post('motif'));
        $dateAvance = $this->post('date_avance');
        
        // Retrieve original avance to redirect back to member
        $avance = $this->avanceModel->find($id);
        $membreId = $avance['membre_id'];

        // Validation
        if ($montant <= 0) {
            $this->setFlash('error', 'Le montant doit être supérieur à zéro.');
            $this->redirect(BASE_URL . '/avances/edit?id=' . $id);
        }

        try {
            $this->avanceModel->update($id, [
                'montant' => $montant,
                'motif' => $motif,
                'date_avance' => $dateAvance
            ]);
            
            $this->setFlash('success', 'Avance mise à jour avec succès.');
            $this->redirect(BASE_URL . '/membres/show?id=' . $membreId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour de l\'avance.');
            $this->redirect(BASE_URL . '/avances/edit?id=' . $id);
        }
    }
}
