<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    $db->exec('ALTER TABLE avances ADD COLUMN date_debut DATE NULL AFTER date_avance');
    echo "Column date_debut added successfully.";
} catch (Exception $e) {
    echo $e->getMessage();
}
