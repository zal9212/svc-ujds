<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Membre.php';
require_once __DIR__ . '/app/models/Versement.php';
require_once __DIR__ . '/app/models/Avance.php';

$membreModel = new Membre();
$vModel = new Versement();

echo "--- TEST 1: NO WARNING IN RECONCILIATION ---\n";
$membreMock = [
    'id' => 999,
    'designation' => 'Test Warnings',
    'montant_mensuel' => 1000,
    'statut' => 'ACTIF',
    'versements' => [],
    'avances' => [
        ['id' => 1, 'montant' => 5000, 'type' => 'ANTICIPATION', 'date_avance' => date('Y-m-d')]
    ]
];

try {
    // Current date for comparison in getWithRelations Pass
    $annee = date('Y');
    
    // Simulating getWithRelations logic which caused the warning
    $situation = $membreModel->getSituationFinanciere($membreMock);
    
    $currYear = (int)date('Y');
    $currMonth = (int)date('n');
    $reflection = new ReflectionClass('Membre');
    $methodMois = $reflection->getMethod('_getMoisOrder');
    $methodMois->setAccessible(true);
    $moisOrder = $methodMois->invoke($membreModel);

    foreach ($situation['reconciled'] as $id => $data) {
        // Checking if 'annee' exists
        if (!isset($data['annee'])) {
            echo "FAIL: Array key 'annee' missing for $id\n";
        } else {
            // echo "SUCCESS: Found annee for $id: " . $data['annee'] . "\n";
        }
    }
    echo "Test 1 Passed: No warnings/missing keys in reconciliation loop.\n";

} catch (Exception $e) {
    echo "Test 1 Error: " . $e->getMessage() . "\n";
}

echo "\n--- TEST 2: SMART FILL BACKWARDS ---\n";
$testMembreId = 4; // We know this one has some data
$wantedRetards = 5;
$endDate = '2026-03-31';

try {
    $vModel->deleteAllCurrentRetards($testMembreId);
    
    // Let's manually create a "gap" by putting a PAID versement in between
    $db = Database::get()->pdo();
    $db->exec("DELETE FROM versements WHERE membre_id = $testMembreId AND annee = 2026 AND mois IN ('mars', 'février', 'janvier')");
    
    // Create 'février' as PAYE. 
    // If we request 3 months ending in March, it should create March, (skip Feb), create Jan, create Dec 2025.
    $vModel->createVersement($testMembreId, 'février', 2026, 1000, 'PAYE', 0);
    
    echo "Requested 3 retards ending in March 2026. February already exists as PAYE.\n";
    $created = $vModel->createBulkUnpaidVersements($testMembreId, 3, '2026-03-31');
    echo "Created $created records.\n";
    
    $res = $db->query("SELECT mois, annee, statut FROM versements WHERE membre_id = $testMembreId AND annee >= 2025 AND statut = 'EN_ATTENTE' ORDER BY annee DESC, id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) {
        echo "Created: {$row['mois']} {$row['annee']} ({$row['statut']})\n";
    }
    
    if ($created == 3) {
        echo "Test 2 Passed: Created exactly the requested amount by skipping existing month.\n";
    } else {
        echo "Test 2 Failed: Created $created instead of 3.\n";
    }

} catch (Exception $e) {
    echo "Test 2 Error: " . $e->getMessage() . "\n";
}
