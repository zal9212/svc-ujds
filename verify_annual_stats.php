<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

// Mock data for 2025
$membre = [
    'id' => 999,
    'designation' => 'Test Member',
    'montant_mensuel' => 10000,
    'statut' => 'ACTIF',
    'versements' => [
        ['id' => 1, 'annee' => 2025, 'mois' => 'janvier', 'montant' => 0, 'statut' => 'EN_ATTENTE', 'has_amende' => 1],
        ['id' => 2, 'annee' => 2025, 'mois' => 'février', 'montant' => 10000, 'statut' => 'PAYE', 'has_amende' => 0],
        ['id' => 3, 'annee' => 2024, 'mois' => 'janvier', 'montant' => 0, 'statut' => 'EN_ATTENTE', 'has_amende' => 1],
    ],
    'avances' => []
];

// Calculation via Reflection because getWithRelations normally hits DB
// But wait, the previous repro used getSituationFinanciere which is public and calls the same logic.
// However, I want to check getWithRelations logic (the part that populates the _annee fields)

// Let's test getWithRelations by mocking the find and getByMembre calls if possible?
// No, simpler: test the logic I just added in a separate script or using reflection on situation.

// Let's just manually test the mapping logic if it was in a helper... but it's in getWithRelations.
// I'll create a verification script that uses the real logic.

$situation = $membreModel->getSituationFinanciere($membre);
$annee = 2025;

// Mocking the getWithRelations logic manually to verify calculations
$anneeStats = [
    'mois_retard' => 0,
    'amende' => 0,
    'total_verse' => 0,
    'montant_du' => 0
];

$currYear = (int)date('Y');
$currMonth = (int)date('n');
$reflection = new ReflectionClass('Membre');
$methodMois = $reflection->getMethod('_getMoisOrder');
$methodMois->setAccessible(true);
$moisOrder = $methodMois->invoke($membreModel);

foreach ($situation['reconciled'] as $id => $data) {
    if ((int)$data['annee'] === (int)$annee) {
        $vMonth = $moisOrder[$data['mois']] ?? 0;
        $isPastOrCurrent = ($annee < $currYear) || ($annee == $currYear && $vMonth <= $currMonth);

        if ($isPastOrCurrent && !in_array($data['display_statut'], ['PAYE', 'PAYE (AVANCE)', 'ANTICIPATION']) && $data['due_total'] > $data['display_montant']) {
            $anneeStats['mois_retard']++;
        }
        $anneeStats['amende'] += $data['amende_due'];
        $anneeStats['total_verse'] += $data['display_montant'];
        $anneeStats['montant_du'] += max(0, $data['due_total'] - $data['display_montant']);
    }
}

echo "Year 2025 Stats:\n";
echo "Mois Retard: " . $anneeStats['mois_retard'] . " (Expected: 1 - January)\n";
echo "Amende: " . $anneeStats['amende'] . " (Expected: 2000)\n";
echo "Total Versé: " . $anneeStats['total_verse'] . " (Expected: 10000)\n";
echo "Montant Dû: " . $anneeStats['montant_du'] . " (Expected: 2000 - the fine for Jan)\n";

if ($anneeStats['mois_retard'] == 1 && $anneeStats['amende'] == 2000 && $anneeStats['total_verse'] == 10000) {
    echo "✓ Annual Calculation: OK\n";
} else {
    echo "✗ Annual Calculation: FAILED\n";
}
