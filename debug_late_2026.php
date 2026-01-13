<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

// Mock member matching the screenshot
$membre = [
    'id' => 8,
    'designation' => 'M. Numéro 8',
    'montant_mensuel' => 2000, // Guessing from the screenshot (Montant Dû 6000 for 2 months of retard + 2 amendes? No, 2000 amende each. 2000*2 + 2000*x = 6000? So x=1? Wait, 2 months = 2000*2 principal + 2000*2 amende = 8000. If 6000, maybe monthly is 1000?)
    'statut' => 'ACTIF',
    'versements' => [
        ['id' => 101, 'annee' => 2026, 'mois' => 'janvier', 'montant' => 0, 'statut' => 'AMENDE', 'has_amende' => 1],
        ['id' => 102, 'annee' => 2026, 'mois' => 'février', 'montant' => 0, 'statut' => 'AMENDE', 'has_amende' => 1],
    ],
    'avances' => []
];

// Calculation with current date being Jan 2026 (as per metadata)
$situation = $membreModel->getSituationFinanciere($membre);

echo "Current Date: " . date('Y-m-d') . "\n";
echo "Global Late Months: " . $situation['mois_retard'] . "\n";

foreach ($situation['reconciled'] as $id => $data) {
    echo "ID: $id | Month: {$data['mois']} | Year: {$data['annee']} | Statut: {$data['display_statut']} | is_amende: " . ($data['is_amende'] ? 'Y' : 'N') . "\n";
}

// Logic for annual stats in getWithRelations
$annee = 2026;
$currYear = (int)date('Y');
$currMonth = (int)date('n');
$reflection = new ReflectionClass('Membre');
$methodMois = $reflection->getMethod('_getMoisOrder');
$methodMois->setAccessible(true);
$moisOrder = $methodMois->invoke($membreModel);

$mois_retard_annee = 0;
foreach ($situation['reconciled'] as $id => $data) {
    if ((int)$data['annee'] === (int)$annee) {
        $vMonth = $moisOrder[$data['mois']] ?? 0;
        $isPastOrCurrent = ($annee < $currYear) || ($annee == $currYear && $vMonth <= $currMonth);
        $hasExplicitAmende = $data['is_amende'] || $data['display_statut'] === 'AMENDE';
        
        echo "Check $id: vMonth=$vMonth, isPastOrCurrent=" . ($isPastOrCurrent?'Y':'N') . ", hasExplicitAmende=" . ($hasExplicitAmende?'Y':'N') . "\n";

        if (($isPastOrCurrent || $hasExplicitAmende) && !in_array($data['display_statut'], ['PAYE', 'PAYE (AVANCE)', 'ANTICIPATION']) && $data['due_total'] > $data['display_montant']) {
            $mois_retard_annee++;
        }
    }
}
echo "Calculated Year Late Months: $mois_retard_annee\n";
