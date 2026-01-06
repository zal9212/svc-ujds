<?php
require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Ajout de la colonne has_amende...\n";
    
    // Vérifier si la colonne existe déjà pour éviter l'erreur
    $stmt = $pdo->query("SHOW COLUMNS FROM versements LIKE 'has_amende'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE versements ADD COLUMN has_amende TINYINT(1) NOT NULL DEFAULT 0");
        echo "✓ Colonne ajoutée avec succès.\n";
    } else {
        echo "✓ La colonne existe déjà.\n";
    }

} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
