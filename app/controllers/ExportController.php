<?php
/**
 * Contrôleur d'export
 * Gestion des exports Excel et PDF
 */

class ExportController extends Controller
{
    private Membre $membreModel;

    public function __construct()
    {
        $this->membreModel = new Membre();
    }

    /**
     * Export Excel
     */
    public function excel(): void
    {
        $this->requireAuth();

        $type = $this->get('type', 'complet');

        try {
            // Récupérer les données selon le type
            switch ($type) {
                case 'retards':
                    $membres = $this->membreModel->getAllWithCalculations();
                    $membres = array_filter($membres, fn($m) => $m['mois_retard'] > 0);
                    $filename = 'membres_en_retard_' . date('Y-m-d') . '.xlsx';
                    $title = 'Membres en Retard';
                    break;
                
                case 'actifs':
                    $membres = $this->membreModel->getByStatut('ACTIF');
                    foreach ($membres as &$m) {
                        $m = array_merge($m, $this->membreModel->getSituationFinanciere($m));
                    }
                    $filename = 'membres_actifs_' . date('Y-m-d') . '.xlsx';
                    $title = 'Membres Actifs';
                    break;
                
                default: // complet
                    $membres = $this->membreModel->getAllWithCalculations();
                    $filename = 'export_complet_' . date('Y-m-d') . '.xlsx';
                    $title = 'Export Complet des Membres';
            }

            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                $this->setFlash('error', 'Le dossier "vendor" est manquant. Veuillez exécuter "composer install" pour activer l\'export Excel.');
                $this->redirect(BASE_URL . '/import');
                return;
            }
            require_once $autoloadPath;
            
            if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
               $this->setFlash('error', 'L\'export Excel est temporairement désactivé (bibliothèque manquante). Veuillez utiliser l\'export PDF.');
               $this->redirect(BASE_URL . '/dashboard');
               return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Membres');

            // En-têtes
            $headers = ['Code', 'Désignation', 'Téléphone', 'Titre', 'Missidé', 'Montant Mensuel', 'Statut', 'Mois Retard', 'Amende', 'Total Versé', 'Montant Dû'];
            $sheet->fromArray($headers, NULL, 'A1');

            // Style de l'en-tête
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

            // Données
            $row = 2;
            foreach ($membres as $membre) {
                $dataRow = [
                    $membre['code'],
                    $membre['designation'],
                    $membre['telephone'] ?? '',
                    $membre['titre'] ?? '',
                    $membre['misside'] ?? '',
                    $membre['montant_mensuel'],
                    $membre['statut'],
                    $membre['mois_retard'] ?? 0,
                    $membre['amende'] ?? 0,
                    $membre['total_verse'] ?? 0,
                    $membre['montant_du'] ?? 0
                ];
                $sheet->fromArray($dataRow, NULL, 'A' . $row);
                
                // Coloration conditionnelle pour le montant dû
                if (($membre['montant_du'] ?? 0) > 0) {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('EF4444'); // Red
                } else {
                    $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('10B981'); // Green
                }
                
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Output
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/import');
        }
    }

    /**
     * Export PDF
     */
    public function pdf(): void
    {
        $this->requireAuth();

        $type = $this->get('type', 'rapport-general');

        try {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                $this->setFlash('error', 'Le dossier "vendor" est manquant. Veuillez exécuter "composer install" pour activer l\'export PDF.');
                $this->redirect(BASE_URL . '/dashboard');
                return;
            }
            require_once $autoloadPath;
            
            // Créer une nouvelle instance de TCPDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Définir les informations du document
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('SVC-UJDS');
            $pdf->SetTitle('Export PDF - ' . $type);

            // Supprimer les en-têtes/pieds de page par défaut
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(true);

            // Définir les marges
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Définir la police
            $pdf->SetFont('helvetica', '', 10);

            // Ajouter une page
            $pdf->AddPage();

            // Récupérer les données
            $membres = $this->membreModel->getAllWithCalculations();
            $date = date('d/m/Y');

            // Logo ou Nom de l'association
            $html = '<h1 style="text-align: center; color: #1F2937;">SVC-UJDS</h1>';
            $html .= '<h3 style="text-align: center; color: #4B5563;">SYSTÈME DE GESTION DES VERSEMENTS</h3>';
            $html .= '<hr>';
            
            switch ($type) {
                case 'a-jour':
                    $membres = array_filter($membres, fn($m) => $m['montant_du'] == 0 && $m['statut'] !== 'VG');
                    $html .= '<h2 style="color: #059669;">Liste des Membres à Jour</h2>';
                    $html .= '<p>Membres n\'ayant aucune dette au ' . htmlspecialchars($date) . '</p>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #ECFDF5; font-weight: bold; color: #065F46;">
                                    <th width="15%">Code</th>
                                    <th width="45%">Désignation</th>
                                    <th width="20%">Total Versé</th>
                                    <th width="20%">Statut</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['code']) . '</td>
                                    <td>' . htmlspecialchars($m['designation']) . '</td>
                                    <td style="text-align: right;">' . number_format($m['total_verse'], 0, ',', ' ') . '</td>
                                    <td>' . htmlspecialchars($m['statut']) . '</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'membres_a_jour_' . date('Ymd') . '.pdf';
                    break;

                case 'en-retard':
                    $membres = array_filter($membres, fn($m) => $m['montant_du'] > 0);
                    $html .= '<h2 style="color: #DC2626;">Liste des Membres en Retard</h2>';
                    $html .= '<p>Membres ayant des dettes au ' . htmlspecialchars($date) . '</p>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #FEF2F2; font-weight: bold; color: #991B1B;">
                                    <th width="15%">Code</th>
                                    <th width="40%">Désignation</th>
                                    <th width="15%">Mois</th>
                                    <th width="30%">Montant Dû</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['code']) . '</td>
                                    <td>' . htmlspecialchars($m['designation']) . '</td>
                                    <td style="text-align: center;">' . (int)$m['mois_retard'] . '</td>
                                    <td style="text-align: right; color: #DC2626; font-weight: bold;">' . number_format($m['montant_du'], 0, ',', ' ') . ' FCFA</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'membres_en_retard_' . date('Ymd') . '.pdf';
                    break;

                case 'tous':
                    $html .= '<h2 style="color: #111827;">État Global des Membres</h2>';
                    $html .= '<p>Situation complète au ' . htmlspecialchars($date) . '</p>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #F3F4F6; font-weight: bold;">
                                    <th width="12%">Code</th>
                                    <th width="33%">Désignation</th>
                                    <th width="10%">Retard</th>
                                    <th width="20%">Total Versé</th>
                                    <th width="25%">Montant Dû</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $colorDu = ($m['montant_du'] > 0) ? '#DC2626' : '#059669';
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['code']) . '</td>
                                    <td>' . htmlspecialchars($m['designation']) . '</td>
                                    <td style="text-align: center;">' . (int)$m['mois_retard'] . '</td>
                                    <td style="text-align: right;">' . number_format($m['total_verse'], 0, ',', ' ') . '</td>
                                    <td style="text-align: right; color: ' . $colorDu . '; font-weight: bold;">' . number_format($m['montant_du'], 0, ',', ' ') . ' FCFA</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'etat_global_membres_' . date('Ymd') . '.pdf';
                    break;

                case 'liste-membres':
                    $html .= '<h2 style="color: #111827;">Liste des Membres</h2>';
                    $html .= '<p>Date de génération : ' . htmlspecialchars($date) . '</p>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #F3F4F6; font-weight: bold;">
                                    <th width="15%">Code</th>
                                    <th width="45%">Désignation</th>
                                    <th width="20%">Téléphone</th>
                                    <th width="20%">Statut</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['code']) . '</td>
                                    <td>' . htmlspecialchars($m['designation']) . '</td>
                                    <td>' . htmlspecialchars($m['telephone'] ?? '-') . '</td>
                                    <td>' . htmlspecialchars($m['statut']) . '</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'liste_membres_' . date('Ymd') . '.pdf';
                    break;

                case 'etat-paiements':
                    $html .= '<h2 style="color: #111827;">État des Paiements</h2>';
                    $html .= '<p>Date de génération : ' . htmlspecialchars($date) . '</p>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #F3F4F6; font-weight: bold;">
                                    <th width="40%">Membre</th>
                                    <th width="15%">Retard</th>
                                    <th width="20%">Total Versé</th>
                                    <th width="25%">Montant Dû</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $styleDu = ($m['montant_du'] > 0) ? 'color: red;' : 'color: green;';
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['designation'] . ' (' . $m['code'] . ')') . '</td>
                                    <td>' . (int)$m['mois_retard'] . ' mois</td>
                                    <td>' . number_format($m['total_verse'], 0, ',', ' ') . '</td>
                                    <td style="' . $styleDu . '">' . number_format($m['montant_du'], 0, ',', ' ') . ' FCFA</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'etat_paiements_' . date('Ymd') . '.pdf';
                    break;

                case 'fiche-membre':
                    $id = $this->get('id');
                    if (!$id) {
                        $this->setFlash('error', 'ID membre manquant.');
                        $this->redirect(BASE_URL . '/dashboard');
                        return;
                    }

                    // Sécurité : Vérifier les droits d'accès
                    $currentUser = $this->getCurrentUser();
                    if ($currentUser['role'] === 'membre') {
                        $ownMembre = $this->membreModel->findByUserId($currentUser['id']);
                        if (!$ownMembre || $ownMembre['id'] != $id) {
                            $this->setFlash('error', 'Accès non autorisé.');
                            $this->redirect(BASE_URL . '/dashboard');
                            return;
                        }
                    }

                    $membre = $this->membreModel->getWithRelations($id);
                    if (!$membre) {
                        $this->setFlash('error', 'Membre introuvable.');
                        $this->redirect(BASE_URL . '/dashboard');
                        return;
                    }

                    // Les calculs sont déjà faits dans getWithRelations
                    // $membre contient déjà : mois_retard, total_verse, montant_du, amende
                    // et aussi : reconciled, virtual_versements
                    
                    // Compter les mois avec un montant versé (payé totalement, partiellement, ou via anticipation)
                    // Un mois est considéré comme "versé" s'il a reçu un paiement (display_montant > 0)
                    $moisPayesCount = 0;
                    foreach ($membre['reconciled'] as $m) {
                        // Compter si le mois a reçu un paiement (direct ou via anticipation)
                        if (($m['display_montant'] ?? 0) > 0) {
                            $moisPayesCount++;
                        }
                    }

                    // Utiliser directement le montant_du calculé par le système
                    // Ce montant tient compte de TOUTES les dettes : paiements partiels, 
                    // retards, amendes, etc.
                    $montantDu = (float)($membre['montant_du'] ?? 0);

                    $filename = 'fiche_membre_' . preg_replace('/[^a-z0-9]/i', '_', $membre['code']) . '_' . date('Ymd') . '.pdf';
                    $pdf->SetTitle('Fiche Situation - ' . $membre['designation']);

                    $html .= '<h2 style="color: #111827; text-align:center;">Fiche de Situation Individuelle</h2>';
                    $html .= '<p style="text-align:center;">Généré le ' . htmlspecialchars($date) . '</p><br>';

                    // Informations Membre
                    $html .= '<table cellpadding="5" border="0" style="background-color: #F9FAFB;">
                                <tr>
                                    <td width="30%"><b>Code :</b> ' . htmlspecialchars($membre['code']) . '</td>
                                    <td width="70%"><b>Nom Complet :</b> ' . htmlspecialchars($membre['designation']) . '</td>
                                </tr>
                                <tr>
                                    <td><b>Téléphone :</b> ' . htmlspecialchars($membre['telephone'] ?? '-') . '</td>
                                    <td><b>Statut :</b> ' . htmlspecialchars($membre['statut']) . '</td>
                                </tr>
                              </table><br><br>';

                    // Résumé Financier (KPIs demandés)
                    $html .= '<table border="1" cellpadding="8" style="text-align:center;">
                                <tr style="background-color: #1F2937; color: white; font-weight: bold;">
                                    <th width="25%">Mois en Retard</th>
                                    <th width="25%">Montant à Verser</th>
                                    <th width="25%">Mois Versés</th>
                                    <th width="25%">Total Versé</th>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; font-weight: bold; color: #DC2626;">' . (int)$membre['mois_retard'] . '</td>
                                    <td style="font-size: 14px; font-weight: bold; color: #DC2626;">' . number_format($montantDu, 0, ',', ' ') . ' FCFA</td>
                                    <td style="font-size: 14px; font-weight: bold; color: #059669;">' . $moisPayesCount . '</td>
                                    <td style="font-size: 14px; font-weight: bold; color: #059669;">' . number_format($membre['total_verse'], 0, ',', ' ') . ' FCFA</td>
                                </tr>
                              </table><br><br>';

                    // Détail des impayés (si retard > 0)
                    if ($montantDu > 0) {
                        $html .= '<h3 style="color: #DC2626;">Détail des Dettes & Amendes</h3>';
                        $html .= '<table border="1" cellpadding="4">
                                    <tr style="background-color: #FEF2F2; font-weight: bold; color: #991B1B;">
                                        <th width="30%">Mois</th>
                                        <th width="20%">Année</th>
                                        <th width="25%">Statut</th>
                                        <th width="25%">Reste à Payer</th>
                                    </tr>';
                        foreach ($membre['reconciled'] as $ver) {
                            $reste = $ver['due_total'] - $ver['display_montant'];
                            
                            // Afficher tous les mois avec un reste à payer > 0
                            // Cela inclut les paiements partiels, retards, amendes, etc.
                            if ($reste > 0.01) {
                                $displayStatus = $ver['display_statut']; 
                                $html .= '<tr>
                                            <td>' . ucfirst($ver['mois']) . '</td>
                                            <td>' . $ver['annee'] . '</td>
                                            <td>' . $displayStatus . '</td>
                                            <td style="text-align: right;">' . number_format($reste, 0, ',', ' ') . ' FCFA</td>
                                          </tr>';
                            }
                        }
                        $html .= '</table>';
                    } else {
                        $html .= '<div style="background-color: #ECFDF5; color: #065F46; padding: 10px; border: 1px solid #059669; text-align: center;">
                                    <b>Aucune dette impayée (Retards ou Amendes).</b>
                                  </div>';
                    }
                    break;

                default: // rapport-general
                    $totalCollecte = array_sum(array_column($membres, 'total_verse'));
                    $totalDu = array_sum(array_column($membres, 'montant_du'));
                    
                    $html .= '<h2 style="color: #111827;">Rapport Général</h2>';
                    $html .= '<p>Date de génération : ' . htmlspecialchars($date) . '</p>';
                    
                    $html .= '<div style="background-color: #F9FAFB; padding: 15px; border: 1px solid #E5E7EB;">
                                <h3>Résumé Financier</h3>
                                <p><b>Total Membres :</b> ' . count($membres) . '</p>
                                <p><b>Total Collecté :</b> <span style="color: green;">' . number_format($totalCollecte, 0, ',', ' ') . ' FCFA</span></p>
                                <p><b>Total Dû :</b> <span style="color: red;">' . number_format($totalDu, 0, ',', ' ') . ' FCFA</span></p>
                              </div>';
                    
                    $html .= '<h4>Détail par Membre</h4>';
                    $html .= '<table border="1" cellpadding="5">
                                <tr style="background-color: #F3F4F6; font-weight: bold;">
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Retard</th>
                                    <th>Montant Dû</th>
                                </tr>';
                    foreach ($membres as $m) {
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($m['code']) . '</td>
                                    <td>' . htmlspecialchars($m['designation']) . '</td>
                                    <td>' . (int)$m['mois_retard'] . '</td>
                                    <td>' . number_format($m['montant_du'], 0, ',', ' ') . '</td>
                                  </tr>';
                    }
                    $html .= '</table>';
                    $filename = 'rapport_general_' . date('Ymd') . '.pdf';
            }

            // Écrire le contenu HTML
            $pdf->writeHTML($html, true, false, true, false, '');

            // Sortie du PDF
            $pdf->Output($filename, 'D');
            exit;
            
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de l\'export PDF: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/import');
        }
    }
}
