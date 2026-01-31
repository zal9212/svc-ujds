<?php
/**
 * Script d'initialisation de la Base de Données de Production
 */

// Lire les credentials depuis .env ou les définir ici (on lit .env pour être cohérent)
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die(" Fichier .env introuvable !\n");
}
// Parsing manuel pour plus de robustesse
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    $env[trim($name)] = trim($value);
}

$dbHost = 'localhost';
$dbName = $env['DB_NAME'];
$dbUser = $env['DB_USER'];
$dbPass = $env['DB_PASS'];

// Identifiants ROOT pour la création initiale (XAMPP défaut: root / sans mdp)
$rootUser = 'root';
$rootPass = '';

echo "=== Initialisation de la BDD Production ===\n";
echo "Cible : $dbName sur $dbHost\n\n";

try {
    // 1. Connexion en admin (root) pour créer la base et l'utilisateur
    $pdo = new PDO("mysql:host=$dbHost", $rootUser, $rootPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "1. Création de la base de données '$dbName'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "2. Création de l'utilisateur '$dbUser'...\n";
    // Création de l'utilisateur s'il n'existe pas
    // Note: DROP USER IF EXISTS est parfois nécessaire pour reset les droits
    $pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo " Base et Utilisateur configurés.\n\n";

    // 2. Connexion avec le NOUVEL utilisateur pour créer les tables
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "3. Importation du schéma...\n";
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Fichier schema.sql introuvable : $schemaFile");
    }

    $sql = file_get_contents($schemaFile);
    
    // Nettoyage: retirer CREATE DATABASE et USE si présents pour éviter les conflits
    // On split par point-virgule pour exécuter commande par commande
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        // Ignorer les commandes de base de données qui pourraient changer le focus
        if (stripos($stmt, 'CREATE DATABASE') === 0) continue;
        if (stripos($stmt, 'USE ') === 0) continue;
        
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Ignorer si la table existe déjà ou erreur mineure
            echo "  Info: " . $e->getMessage() . "\n";
        }
    }
    echo " Schéma importé.\n\n";

    // 3. Création de l'admin
    echo "4. Création de l'administrateur 'admin'...\n";
    $adminPass = 'Admin_Sup3r_S3cur3!2025';
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    
    // Vérifier si admin existe déjà
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "L'utilisateur 'admin' existe déjà. Mise à jour du mot de passe...\n";
        $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ?, role = 'admin' WHERE username = 'admin'");
        $stmt->execute([$hash]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, role) VALUES ('admin', ?, 'admin')");
        $stmt->execute([$hash]);
    }
    
    echo " Utilisateur Admin créé/mis à jour.\n";
    echo "   Mot de passe : $adminPass\n";

} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/setup_error.log', $e->getMessage());
    die(" Erreur SQL : " . $e->getMessage() . "\n");
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/setup_error.log', $e->getMessage());
    die(" Erreur : " . $e->getMessage() . "\n");
}

echo "\n Initialisation terminée avec succès !\n";
