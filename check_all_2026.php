<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    $res = $db->query('SELECT membre_id, mois, annee, statut, has_amende FROM versements WHERE annee = 2026')->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($res));
} catch (Exception $e) {
    die($e->getMessage());
}
