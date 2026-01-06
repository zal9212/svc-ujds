<?php
require 'config/config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $phone = '776541531';
    
    // Rechercher le membre
    $stmt = $pdo->prepare("SELECT * FROM membres WHERE telephone = ? OR code LIKE ?");
    $stmt->execute([$phone, "%$phone%"]);
    $membre = $stmt->fetch();

    if ($membre) {
        echo "Membre trouvé: " . $membre['designation'] . " (ID: " . $membre['id'] . ")\n";
        echo "Téléphone: " . $membre['telephone'] . "\n";
        echo "Code: " . $membre['code'] . "\n";
        echo "Utilisateur ID: " . ($membre['user_id'] ?? 'NULL') . "\n";
        
        if ($membre['user_id']) {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$membre['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "Username: " . $user['username'] . "\n";
                echo "Role: " . $user['role'] . "\n";
                
                if (password_verify('123456', $user['password'])) {
                    echo "Le mot de passe par défaut (123456) est CORRECT.\n";
                } else {
                    echo "Le mot de passe par défaut (123456) est INCORRECT.\n";
                    // Resetting password to 123456 as a fix
                    $newHash = password_hash('123456', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);
                    echo "Mot de passe réinitialisé à '123456'.\n";
                }
            } else {
                echo "Compte utilisateur non trouvé dans la table utilisateurs.\n";
            }
        } else {
            echo "Ce membre n'a pas de compte utilisateur lié.\n";
        }
    } else {
        echo "Membre non trouvé.\n";
        
        // Lister tous les membres pour voir s'il y a une faute de frappe
        echo "\nListe des 5 derniers membres créés:\n";
        $stmt = $pdo->query("SELECT designation, telephone, code FROM membres ORDER BY id DESC LIMIT 5");
        while ($row = $stmt->fetch()) {
            echo "- " . $row['designation'] . " | Tel: " . $row['telephone'] . " | Code: " . $row['code'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
