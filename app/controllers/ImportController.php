<?php
/**
 * Contrôleur d'import
 * Gestion de l'import Excel
 */

class ImportController extends Controller
{
    private Membre $membreModel;

    public function __construct()
    {
        $this->membreModel = new Membre();
    }

    /**
     * Page d'import
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'comptable']);

        $data = [
            'currentUser' => $this->getCurrentUser()
        ];

        $this->render('import/index', $data);
    }

    /**
     * Traiter l'upload Excel
     */
    public function upload(): void
    {
        $this->requireRole(['admin', 'comptable']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/import');
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect(BASE_URL . '/import');
        }

        // Vérifier si un fichier a été uploadé
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Erreur lors de l\'upload du fichier.');
            $this->redirect(BASE_URL . '/import');
        }

        $file = $_FILES['excel_file'];

        // Vérifier la taille
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $this->setFlash('error', 'Le fichier est trop volumineux (max 5MB).');
            $this->redirect(BASE_URL . '/import');
        }

        // Vérifier le type MIME réel (sécurité renforcée)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'application/vnd.ms-excel', 
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', // Parfois détecté pour les .xlsx
            'application/octet-stream' // Cas limite
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            $this->setFlash('error', 'Type de fichier non autorisé. Utilisez .xlsx ou .xls (Détecté: ' . $mimeType . ')');
            $this->redirect(BASE_URL . '/import');
        }

        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Supprimer l'en-tête (on suppose la première ligne)
            array_shift($rows);
            
            $db = Database::get();
            $db->beginTransaction();
            
            $count = 0;
            $anneeCourante = (int)date('Y');
            $moisDeLannee = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
            
            foreach ($rows as $row) {
                if (empty($row[4])) continue; // Désignation vide, on ignore

                // Mapping des colonnes de base
                $data = [
                    'numero' => (int)$row[0],
                    'code' => $row[1],
                    'telephone' => $row[2],
                    'titre' => $row[3],
                    'designation' => $row[4],
                    'misside' => $row[5],
                    'montant_mensuel' => (float)($row[6] ?? 0),
                    'statut' => !empty($row[23]) ? $row[23] : 'ACTIF' 
                ];

                // 1. Créer ou mettre à jour le membre
                $membreId = null;
                $existing = $this->membreModel->findByCode($data['code']);
                if ($existing) {
                    $membreId = (int)$existing['id'];
                    $this->membreModel->updateMembre($membreId, $data);
                } else {
                    $membreId = $this->membreModel->createMembre($data);
                }

                // 2. Traitement des versements mensuels (Colonnes index 7 à 17 pour fév à déc)
                $versementModel = new Versement();
                $moisImport = ['février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                
                // On récupère le nombre de mois en retard et le montant des amendes de l'Excel
                $retardsExcel = (int)($row[18] ?? 0);
                $amendesExcel = (float)($row[19] ?? 0);
                $nombreAmendes = (int)($amendesExcel / 2000); // 2000 FCFA par amende selon le standard

                $amendesAttribuees = 0;
                $retardsAttribues = 0;

                for ($i = 0; $i < count($moisImport); $i++) {
                    $moisNom = $moisImport[$i];
                    $valeur = $row[7 + $i]; 
                    
                    $montant = (float)$valeur;
                    $statutV = 'EN_ATTENTE';
                    $hasAmende = 0;

                    // Si on a une valeur > 0, c'est payé (total ou partiel)
                    if ($montant > 0) {
                        $statutV = ($montant >= $data['montant_mensuel']) ? 'PAYE' : 'PARTIEL';
                    } 
                    // Si c'est vide mais qu'on a encore des retards à attribuer selon l'Excel
                    elseif ($retardsAttribues < $retardsExcel) {
                        $statutV = 'EN_ATTENTE';
                        $retardsAttribues++;
                    }

                    // Attribution des amendes si nécessaire
                    if ($amendesAttribuees < $nombreAmendes) {
                        $hasAmende = 1;
                        $amendesAttribuees++;
                    }

                    // Préparation des données du versement
                    $versementData = [
                        'membre_id' => $membreId,
                        'mois' => $moisNom,
                        'annee' => $anneeCourante,
                        'montant' => $montant,
                        'statut' => $statutV,
                        'has_amende' => $hasAmende,
                        'date_paiement' => ($montant > 0) ? date('Y-m-d') : null
                    ];

                    if ($versementModel->versementExists($membreId, $moisNom, $anneeCourante)) {
                        $sql = "UPDATE versements SET montant = ?, statut = ?, has_amende = ?, date_paiement = ? 
                                WHERE membre_id = ? AND mois = ? AND annee = ?";
                        $db->query($sql, [$montant, $statutV, $hasAmende, $versementData['date_paiement'], $membreId, $moisNom, $anneeCourante]);
                    } else {
                        $sql = "INSERT INTO versements (membre_id, mois, annee, montant, statut, has_amende, date_paiement) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $db->query($sql, [$membreId, $moisNom, $anneeCourante, $montant, $statutV, $hasAmende, $versementData['date_paiement']]);
                    }
                }

                // 3. Avances (Colonne index 20)
                if (!empty($row[20])) {
                    $montantAvance = (float)$row[20];
                    if ($montantAvance > 0) {
                        $avanceModel = new Avance();
                        // Pour l'import, on vérifie si une avance identique existe déjà aujourd'hui pour éviter les doublons lors de ré-imports
                        $sql = "SELECT id FROM avances WHERE membre_id = ? AND montant = ? AND date_avance = ? AND motif = 'Import Excel' LIMIT 1";
                        $existingAvance = $db->fetchOne($sql, [$membreId, $montantAvance, date('Y-m-d')]);
                        
                        if (!$existingAvance) {
                            $avanceModel->create([
                                'membre_id' => $membreId,
                                'montant' => $montantAvance,
                                'motif' => 'Import Excel',
                                'date_avance' => date('Y-m-d')
                            ]);
                        }
                    }
                }

                $count++;
            }
            
            $db->commit();
            $this->setFlash('success', "$count membres importés/mis à jour avec succès.");
            $this->redirect(BASE_URL . '/import');
        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            $this->setFlash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le modèle Excel
     */
    public function template(): void
    {
        $this->requireRole(['admin', 'comptable']);

        require_once __DIR__ . '/../../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Numéro', 'Code membre', 'Téléphone', 'Titre', 'Désignation', 'Missidé', 'Montant mensuel',
            'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
            'Mois Retard', 'Amende', 'Avance', 'Montant Versé', 'Montant Dû', 'Statut'
        ];
        
        $sheet->fromArray($headers, NULL, 'A1');
        
        // Exemple de données
        $example = [
            1, 'N°-0001-2024', '770000000', 'M.', 'Jean Dupont', 'Missidé A', 5000,
            5000, 5000, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            2, 4000, 0, 10000, 14000, 'ACTIF'
        ];
        $sheet->fromArray($example, NULL, 'A2');
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="modele_import_membres.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
