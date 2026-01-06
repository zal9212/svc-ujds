<?php
require_once 'config/config.php';
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    echo "Connected successfully to " . DB_HOST . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    // Try scanning a few common ports
    $ports = [3306, 3307, 3308, 8889];
    foreach ($ports as $port) {
        try {
            $dsn = "mysql:host=127.0.0.1;port=$port;dbname=svc_ujds";
            $p = new PDO($dsn, DB_USER, DB_PASS);
            echo "SUCCESS on port $port!\n";
            exit;
        } catch (Exception $ex) {}
    }
}
