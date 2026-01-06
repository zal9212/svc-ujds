<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

try {
    // Use correct singleton method
    $db = Database::get();
    
    // 1. Add user_id column if not exists
    echo "Adding user_id column to membres table...\n";
    try {
        $sql = "ALTER TABLE membres ADD COLUMN IF NOT EXISTS user_id INT UNIQUE NULL AFTER id";
        $db->query($sql);
    } catch (Exception $e) {
        // Ignorer si existe déjà (MariaDB < 10.2 ne supporte pas IF NOT EXISTS pour ADD COLUMN parfois)
        // Mais ici on capture l'erreur générale
        echo "Column might exist: " . $e->getMessage() . "\n";
    }
    
    // 2. Add Foreign Key
    echo "Adding Foreign Key constraint...\n";
    try {
        // Check if constraint exists (MySQL specific hack or just try/catch)
        $sql = "ALTER TABLE membres ADD CONSTRAINT fk_membres_users FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL";
        $db->query($sql);
    } catch (Exception $e) {
        echo "FK constraint might already exist or error: " . $e->getMessage() . "\n";
    }

    // 3. Link demo user 'membre' to first member (for testing)
    echo "Linking demo user 'membre' to first member...\n";
    $user = $db->fetchOne("SELECT id FROM utilisateurs WHERE role = 'membre' LIMIT 1");
    // Find a member that is NOT already linked
    $member = $db->fetchOne("SELECT id FROM membres WHERE user_id IS NULL LIMIT 1");

    if ($user && $member) {
        $db->query("UPDATE membres SET user_id = ? WHERE id = ?", [$user['id'], $member['id']]);
        echo "Linked User ID {$user['id']} to Member ID {$member['id']}.\n";
    } else {
        echo "Could not find demo user or unlinked member.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
