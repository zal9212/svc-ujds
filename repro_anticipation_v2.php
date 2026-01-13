<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

echo "--- SCENARIO: 4 PAID (JAN-APR) + 6000 ANTICIPATION ---\n";
echo "Current Date: " . date('Y-m-d') . " (Assuming Jan 13th 2026 as per system)\n";

$membreMock = [
    'id' => 1,
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

foreach ($situation['reconciled'] as $id => $data) {
    if ($data['display_montant'] > 0 || $data['display_statut'] !== 'EN_ATTENTE') {
        echo "{$data['mois']} {$data['annee']}: Statut={$data['display_statut']}, Paid={$data['display_montant']}\n";
    }
}
