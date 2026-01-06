<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';

try {
    $db = Database::get();
    
    echo "Updating existing AMENDE records to have -2000 montant...\n";
    
    $sql = "UPDATE versements SET montant = -2000 WHERE statut = 'AMENDE' OR has_amende = 1";
    $stmt = $db->query($sql);
    
    echo "Rows updated: " . $stmt->rowCount() . "\n";
    echo "Successfully applied negative value to fines.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
