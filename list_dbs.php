<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS);
    $res = $db->query('SHOW DATABASES')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
