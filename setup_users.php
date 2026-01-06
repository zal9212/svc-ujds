<?php
/**
 * Script de vérification et création des utilisateurs
 * À exécuter une seule fois pour initialiser les utilisateurs
 */

require_once __DIR__ . '/config/config.php';

echo "=== VÉRIFICATION ET CRÉATION DES UTILISATEURS ===\n\n";

try {
    // Connexion à la base de données
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connexion à la base de données réussie\n\n";
    
    // Vérifier si la table utilisateurs existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'utilisateurs'");
    if ($stmt->rowCount() === 0) {
        echo "✗ ERREUR: La table 'utilisateurs' n'existe pas!\n";
        echo "→ Veuillez importer database/schema.sql d'abord\n";
        exit(1);
    }
    
    echo "✓ Table 'utilisateurs' existe\n\n";
    
    // Vérifier les utilisateurs existants
    $stmt = $pdo->query("SELECT username, role FROM utilisateurs");
    $existingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($existingUsers) > 0) {
        echo "Utilisateurs existants:\n";
        foreach ($existingUsers as $user) {
            echo "  - {$user['username']} ({$user['role']})\n";
        }
        echo "\n";
    } else {
        echo "Aucun utilisateur trouvé. Création des utilisateurs par défaut...\n\n";
    }
    
    // Créer/Mettre à jour les utilisateurs par défaut
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => 'password123',
            'role' => 'admin'
        ],
        [
            'username' => 'comptable',
            'password' => 'password123',
            'role' => 'comptable'
        ],
        [
            'username' => 'membre',
            'password' => 'password123',
            'role' => 'membre'
        ]
    ];
    
    foreach ($defaultUsers as $userData) {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = ?");
        $stmt->execute([$userData['username']]);
        $exists = $stmt->fetch();
        
        // Hasher le mot de passe
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        if ($exists) {
            // Mettre à jour
            $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ?, role = ? WHERE username = ?");
            $stmt->execute([
                $hashedPassword,
                $userData['role'],
                $userData['username']
            ]);
            echo "✓ Utilisateur '{$userData['username']}' mis à jour\n";
            echo "  → Mot de passe: {$userData['password']}\n";
            echo "  → Hash: " . substr($hashedPassword, 0, 30) . "...\n\n";
        } else {
            // Créer
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([
                $userData['username'],
                $hashedPassword,
                $userData['role']
            ]);
            echo "✓ Utilisateur '{$userData['username']}' créé\n";
            echo "  → Mot de passe: {$userData['password']}\n";
            echo "  → Hash: " . substr($hashedPassword, 0, 30) . "...\n\n";
        }
    }
    
    echo "\n=== RÉSUMÉ ===\n";
    echo "Tous les utilisateurs ont été créés/mis à jour avec succès!\n\n";
    echo "Vous pouvez maintenant vous connecter avec:\n";
    echo "  - admin / password123\n";
    echo "  - comptable / password123\n";
    echo "  - membre / password123\n\n";
    
    // Test de vérification du mot de passe
    echo "=== TEST DE VÉRIFICATION ===\n";
    $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE username = 'admin'");
    $stmt->execute();
    $adminHash = $stmt->fetchColumn();
    
    if (password_verify('password123', $adminHash)) {
        echo "✓ Vérification du mot de passe: OK\n";
        echo "✓ La connexion devrait fonctionner maintenant!\n";
    } else {
        echo "✗ Vérification du mot de passe: ÉCHEC\n";
    }
    
} catch (PDOException $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "→ La base de données 'svc_ujds' n'existe pas!\n";
        echo "→ Veuillez créer la base de données:\n";
        echo "   1. Ouvrir phpMyAdmin: http://localhost/phpmyadmin\n";
        echo "   2. Créer une nouvelle base: svc_ujds\n";
        echo "   3. Importer: database/schema.sql\n";
        echo "   4. Relancer ce script\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "→ Erreur de connexion MySQL!\n";
        echo "→ Vérifiez les identifiants dans config/config.php\n";
    }
    
    exit(1);
}

echo "\n✓ Script terminé avec succès!\n";
