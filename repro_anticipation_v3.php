<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

echo "--- SCENARIO: 4 PAID + 8 TOTAL DUE + 6000 ANTICIPATION ---\n";
// Total target: Jan to Dec 2026 (12 months)
// Paid: Jan to Apr
// Debt Records (Retards): May to Dec (8 months)
// Anticipation: 6000
// Expected: May to Oct paid (6 months), Nov to Dec remain EN_ATTENTE (2 months).

$membreMock = [
    'id' => 1,
    'montant_mensuel' => 1000,
    'statut' => 'ACTIF',
    'versements' => [
        ['id' => 1, 'mois' => 'janvier', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 2, 'mois' => 'février', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 3, 'mois' => 'mars', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        ['id' => 4, 'mois' => 'avril', 'annee' => 2026, 'montant' => 1000, 'statut' => 'PAYE'],
        
        ['id' => 5, 'mois' => 'mai', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 6, 'mois' => 'juin', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 7, 'mois' => 'juillet', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 8, 'mois' => 'août', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 9, 'mois' => 'septembre', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 10, 'mois' => 'octobre', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 11, 'mois' => 'novembre', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
        ['id' => 12, 'mois' => 'décembre', 'annee' => 2026, 'montant' => 0, 'statut' => 'EN_ATTENTE'],
    ],
    'avances' => [
        ['id' => 1, 'montant' => 6000, 'type' => 'ANTICIPATION', 'date_avance' => '2026-05-01']
    ]
];

$situation = $membreModel->getSituationFinanciere($membreMock);

foreach ($situation['reconciled'] as $id => $data) {
    echo "{$data['mois']} {$data['annee']}: Statut={$data['display_statut']}, Paid={$data['display_montant']}\n";
}
