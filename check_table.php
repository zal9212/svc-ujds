<?php
require_once 'app/core/Database.php';
require_once 'config/config.php';
try {
    $db = Database::get();
    $res = $db->fetchAll("DESCRIBE membres");
    print_r($res);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
