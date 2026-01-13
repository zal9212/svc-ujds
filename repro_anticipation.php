<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

// Scenario: 
// 1000 FCFA/month
// Paid 4 months (real versements)
// Anticipation of 6000 FCFA added
// Expected: It should cover 6 months in the future.

echo "--- SCENARIO: 4 PAID + 6000 ANTICIPATION ---\n";

$membreMock = [
    'id' => 999,
    'designation' => 'Test Logic',
    'montant_mensuel' => 1000,
    'statut' => 'ACTIF',
    'versements' => [
        ['id' => 1, 'mois' => 'janvier', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 2, 'mois' => 'février', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 3, 'mois' => 'mars', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 4, 'mois' => 'avril', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
    ],
    'avances' => [
        ['id' => 1, 'montant' => 6000, 'type' => 'ANTICIPATION', 'date_avance' => '2026-05-01']
    ]
];

$situation = $membreModel->getSituationFinanciere($membreMock);

echo "Total Anticipated Created: " . count(array_filter($situation['reconciled'], fn($r) => strpos($r['display_statut'], 'ANTICIPATION') !== false)) . "\n";

foreach ($situation['reconciled'] as $id => $data) {
    echo "Month: {$data['mois']} {$data['annee']} | Statut: {$data['display_statut']} | Paid: {$data['display_montant']}\n";
}

echo "\n--- SCENARIO: 2 RETARDS + 2ND ANTICIPATION 6000 ---\n";
// Maybe the user meant: 4 paid, but then there's a gap?
// "Si je doit verser 1000fr sa doit complet les 6 mois qui suivent et il doit rester 2 mois"
// If there are 8 months to pay, I pay 4. 4 left. 6000 covers 4 + 2 anticipation?

$membreMock2 = [
    'id' => 999,
    'designation' => 'Test Logic 2',
    'montant_mensuel' => 1000,
    'statut' => 'ACTIF',
    'versements' => [
        ['id' => 1, 'mois' => 'janvier', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 2, 'mois' => 'février', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 3, 'mois' => 'mars', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 4, 'mois' => 'avril', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        // Gap of 4 months (May, June, July, August)
        ['id' => 5, 'mois' => 'mai', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 6, 'mois' => 'juin', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 7, 'mois' => 'juillet', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 8, 'mois' => 'août', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
    ],
    'avances' => [
        ['id' => 1, 'montant' => 6000, 'type' => 'ANTICIPATION', 'date_avance' => '2026-09-01']
    ]
];

$situation2 = $membreModel->getSituationFinanciere($membreMock2);

foreach ($situation2['reconciled'] as $id => $data) {
    echo "Month: {$data['mois']} {$data['annee']} | Statut: {$data['display_statut']} | Paid: {$data['display_montant']}\n";
}
