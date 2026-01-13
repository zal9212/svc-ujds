<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

// Mock current date to Jan 2026
// We can't easily mock date() in PHP without extensions, so we'll just check the logic in the model.
// But wait, the metadata says it's Jan 2026.

$membreModel = new Membre();

// Let's mock a member with 11 late months in 2026
$membre = [
    'id' => 999,
    'designation' => 'Test Member',
    'montant_mensuel' => 50000,
    'statut' => 'ACTIF',
    'versements' => [],
    'avances' => []
];

// Create 11 months of EN_ATTENTE with fine for 2026 (Jan to Nov)
$moisList = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre'
];

foreach ($moisList as $mois) {
    $membre['versements'][] = [
        'id' => 1000 + count($membre['versements']),
        'membre_id' => 999,
        'mois' => $mois,
        'annee' => 2026,
        'montant' => 0,
        'statut' => 'EN_ATTENTE',
        'has_amende' => 1,
        'date_paiement' => null
    ];
}

// Get situation
$situation = $membreModel->getSituationFinanciere($membre);

echo "Current Date: " . date('Y-m-d') . "\n";
echo "Global Late Months: " . $situation['mois_retard'] . "\n";
echo "Expected for Jan 2026: 1 (only January should be late)\n";

if ($situation['mois_retard'] == 11) {
    echo "BUG CONFIRMED: Future months are counted as late.\n";
} else {
    echo "Current count: " . $situation['mois_retard'] . "\n";
}
