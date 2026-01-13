<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/models/Versement.php';

$vModel = new Versement();
$membreId = 12; // M. Numéro 8
$moisRetard = 5;
$dateFinale = '2026-01-31';
$reset = true;

echo "Simulating update for member $membreId...\n";

try {
    if ($reset) {
        $deleted = $vModel->deleteAllCurrentRetards($membreId);
        echo "Deleted $deleted records.\n";
    }

    $created = $vModel->createBulkUnpaidVersements($membreId, $moisRetard, $dateFinale);
    echo "Created $created records.\n";

    // Check what months were created
    $db = Database::get()->pdo();
    $res = $db->query("SELECT mois, annee, statut FROM versements WHERE membre_id = $membreId ORDER BY annee DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) {
        echo "{$row['mois']} {$row['annee']} : {$row['statut']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
