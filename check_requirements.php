<?php
/**
 * Script de vérification des pré-requis pour le déploiement
 */

$requirements = [
    'php_version' => '8.0.0',
    'extensions' => [
        'pdo',
        'pdo_mysql',
        'mbstring',
        'json',
        'zip',
        'gd', // ou imagick
    ],
    'writable_dirs' => [
        __DIR__ . '/public/uploads',
        __DIR__ . '/public/logs', // Si vous utilisez un dossier logs public, sinon '../logs'
    ]
];

echo "=== Vérification de l'environnement Serveur ===\n\n";

// 1. Version PHP
$currentPhpVersion = PHP_VERSION;
echo "[PHP] Version actuelle : $currentPhpVersion\n";
if (version_compare($currentPhpVersion, $requirements['php_version'], '>=')) {
    echo "✅ Version PHP OK (>= {$requirements['php_version']})\n";
} else {
    echo "❌ Version PHP INSUFFISANTE (Requis : >= {$requirements['php_version']})\n";
}
echo "\n";

// 2. Extensions
echo "[EXTENSIONS] Vérification des extensions requises :\n";
foreach ($requirements['extensions'] as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extension '$ext' chargée\n";
    } else {
        echo "❌ Extension '$ext' MANQUANTE\n";
    }
}
echo "\n";

// 3. Permissions Dossiers
echo "[PERMISSIONS] Vérification des dossiers accessibles en écriture :\n";
foreach ($requirements['writable_dirs'] as $dir) {
    // Créer le dossier s'il n'existe pas pour tester
    if (!file_exists($dir)) {
        echo "⚠️  Le dossier '$dir' n'existe pas. Tentative de création...\n";
        @mkdir($dir, 0755, true);
    }

    if (is_writable($dir)) {
        echo "✅ Dossier '" . basename($dir) . "' est inscriptible\n";
    } else {
        echo "❌ Dossier '" . basename($dir) . "' N'EST PAS inscriptible\n";
    }
}
echo "\n";

// 4. Vérification MySQL (simple connection check if credentials available, skipped here as we are pre-config)
echo "[MYSQL] La vérification de connexion se fera lors de l'installation de la BDD.\n";

echo "\n=== Fin de la vérification ===\n";
