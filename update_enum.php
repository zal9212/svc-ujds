<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';

try {
    $db = Database::get();
    
    echo "Updating versements table statut column...\n";
    
    // Add 'AMENDE' to the enum
    $sql = "ALTER TABLE versements MODIFY COLUMN statut ENUM('EN_ATTENTE', 'PAYE', 'PARTIEL', 'ANNULE', 'EXEMPTE', 'AMENDE') NOT NULL DEFAULT 'EN_ATTENTE'";
    $db->query($sql);
    
    echo "Successfully updated ENUM to include 'AMENDE'.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
