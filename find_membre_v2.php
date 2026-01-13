<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    $res = $db->query("SELECT id, designation, code, numero FROM membres WHERE numero=8 OR numero='8' OR numero='08' OR code LIKE '%8%'")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
