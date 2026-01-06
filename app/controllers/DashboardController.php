<?php
/**
 * Contrôleur du tableau de bord
 * Affichage des statistiques et KPIs
 */

class DashboardController extends Controller
{
    private Membre $membreModel;
    private Versement $versementModel;
    private Database $db;

    public function __construct()
    {
        $this->membreModel = new Membre();
        $this->versementModel = new Versement();
        $this->db = Database::get();
    }

    /**
     * Page d'accueil du dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        // --- SPECIAL REDIRECTION FOR MEMBERS ---
        if ($user['role'] === 'membre') {
            $membreLie = $this->membreModel->findByUserId((int) $user['id']);
            
            if ($membreLie) {
                $this->redirect(BASE_URL . '/membres/show?id=' . $membreLie['id']);
            } else {
                echo "<h1>Compte non lié</h1><p>Votre compte utilisateur n'est associé à aucune fiche membre. Veuillez contacter l'administrateur.</p>";
                echo "<a href='" . BASE_URL . "/logout'>Se déconnecter</a>";
                exit;
            }
        }

        // --- ADMIN / COMPTABLE DASHBOARD ---

        // Récupération du filtre année
        $anneeParam = $this->get('annee');
        
        // Par défaut: toutes les années pour une vue d'ensemble complète
        if (!$anneeParam) {
            $selectedYear = 'all';
        } else {
            $selectedYear = $anneeParam === 'all' ? 'all' : (int) $anneeParam;
        }

        $calcYear = $selectedYear === 'all' ? null : $selectedYear;

        // Statistiques générales
        $totalMembres = $this->membreModel->count();
        $membresActifs = $this->membreModel->count(['statut' => 'ACTIF']);
        $membresVG = $this->membreModel->count(['statut' => 'VG']);
        $membresSuspendus = $this->membreModel->count(['statut' => 'SUSPENDU']);

        // Calcul du total collecté
        $sql = "SELECT SUM(montant) as total FROM versements WHERE statut IN ('PAYE', 'PARTIEL')";
        $params = [];
        if ($selectedYear !== 'all') {
            $sql .= " AND annee = ?";
            $params[] = $selectedYear;
        }
        $result = $this->db->fetchOne($sql, $params);
        $totalCollecte = (float) ($result['total'] ?? 0);

        // Calcul du total dû
        $membres = $this->membreModel->getAllWithCalculations($calcYear);
        $totalDu = 0;
        foreach ($membres as $membre) {
            $totalDu += $membre['montant_du'];
        }

        // Calcul du total des avances
        $sql = "SELECT SUM(montant) as total FROM avances";
        $params = [];
        if ($selectedYear !== 'all') {
            $sql .= " WHERE YEAR(date_avance) = ?";
            $params[] = $selectedYear;
        }
        $result = $this->db->fetchOne($sql, $params);
        $totalAvances = (float) ($result['total'] ?? 0);

        // Membres à jour (Dette = 0)
        $membresAJour = [];
        foreach ($membres as $membre) {
            if ($membre['montant_du'] == 0 && $membre['statut'] !== 'VG') {
                $membresAJour[] = $membre;
            }
        }
        usort($membresAJour, fn($a, $b) => strcmp($a['designation'], $b['designation']));
        $membresAJour = array_slice($membresAJour, 0, 10);

        // Membres avec le plus de retard
        $membresRetard = [];
        foreach ($membres as $membre) {
            if ($membre['mois_retard'] > 0) {
                $membresRetard[] = $membre;
            }
        }
        usort($membresRetard, fn($a, $b) => $b['mois_retard'] <=> $a['mois_retard']);
        $membresRetard = array_slice($membresRetard, 0, 10);

        // Liste des années disponibles pour le filtre
        $availableYears = $this->membreModel->getGlobalAvailableYears();

        $data = [
            'totalMembres' => $totalMembres,
            'membresActifs' => $membresActifs,
            'membresVG' => $membresVG,
            'membresSuspendus' => $membresSuspendus,
            'totalCollecte' => $totalCollecte,
            'totalDu' => $totalDu,
            'totalAvances' => $totalAvances,
            'membresAJour' => $membresAJour,
            'membresRetard' => $membresRetard,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('dashboard/index', $data);
    }
}
