<?php
require 'config/config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $phone = '776541531';
    
    // Rechercher le membre
    $stmt = $pdo->prepare("SELECT * FROM membres WHERE telephone = ?");
    $stmt->execute([$phone]);
    $membre = $stmt->fetch();

    if ($membre) {
        echo "Membre: " . $membre['designation'] . " | Code: " . $membre['code'] . " | UserID: " . $membre['user_id'] . "\n";
        
        if ($membre['user_id']) {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$membre['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "UTILISATEUR RECORD:\n";
                echo "Username: '" . $user['username'] . "'\n";
                echo "Role: " . $user['role'] . "\n";
            } else {
                echo "Aucun enregistrement trouvé dans 'utilisateurs' pour ID " . $membre['user_id'] . "\n";
            }
        }
    } else {
        echo "Membre non trouvé.\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
