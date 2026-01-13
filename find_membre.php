<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $res = $db->query("SELECT id, designation, code FROM membres WHERE id=8 OR code='8' OR numero='8' OR numero='08'")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
