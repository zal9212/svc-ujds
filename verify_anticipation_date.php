<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();

echo "--- SCENARIO: ANTICIPATION WITH START DATE (JULY) BUT DEBT IN MAY ---\n";
// Scenario: 
// Debt in May, June
// Anticipation of 1000 starts in July
// Expected: May, June remain EN_ATTENTE. July is ANTICIPATION.

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
    ],
    'avances' => [
        ['id' => 1, 'montant' => 1000, 'type' => 'ANTICIPATION', 'date_avance' => '2026-05-01', 'date_debut' => '2026-07-01']
    ]
];

$situation = $membreModel->getSituationFinanciere($membreMock);

foreach ($situation['reconciled'] as $id => $data) {
    if ($data['annee'] == 2026 && in_array($data['mois'], ['mai', 'juin', 'juillet'])) {
        echo "{$data['mois']} {$data['annee']}: Statut={$data['display_statut']}, Paid={$data['display_montant']}\n";
    }
}

// Check virtuals too
foreach ($situation['virtual_versements'] as $v) {
   if ($v['annee'] == 2026 && $v['mois'] == 'juillet') {
       echo "VIRTUAL {$v['mois']} {$v['annee']}: Statut={$v['statut']}, Montant={$v['montant']}\n";
   }
}
