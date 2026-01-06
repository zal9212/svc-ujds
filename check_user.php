<?php
require 'config/config.php';
require 'app/core/Database.php';
require 'app/core/Model.php';
require 'app/core/Security.php';
require 'app/models/Versement.php';
require 'app/models/Avance.php';
require 'app/models/Membre.php';
require 'app/models/Utilisateur.php';

$membreModel = new Membre();
$userModel = new Utilisateur();

$phone = '776541531';
$membre = $membreModel->db->fetchOne("SELECT * FROM membres WHERE telephone = ?", [$phone]);

if (!$membre) {
    echo "Membre non trouvé avec le téléphone: $phone\n";
    // Essayer par code au cas où
    $membre = $membreModel->db->fetchOne("SELECT * FROM membres WHERE code LIKE ?", ["%$phone%"]);
}

if ($membre) {
    echo "Membre trouvé: " . $membre['designation'] . " (ID: " . $membre['id'] . ")\n";
    echo "Utilisateur ID: " . ($membre['user_id'] ?? 'NULL') . "\n";
    
    if ($membre['user_id']) {
        $user = $userModel->find($membre['user_id']);
        if ($user) {
            echo "Username: " . $user['username'] . "\n";
            echo "Role: " . $user['role'] . "\n";
            // On ne peut pas voir le mdp mais on peut vérifier s'il correspond à 123456
            if (password_verify('123456', $user['password'])) {
                echo "Le mot de passe par défaut (123456) est CORRECT.\n";
            } else {
                echo "Le mot de passe par défaut (123456) est INCORRECT.\n";
            }
        } else {
            echo "Utilisateur non trouvé en base (ID: " . $membre['user_id'] . ")\n";
        }
    } else {
        echo "Ce membre n'a pas de compte utilisateur lié.\n";
    }
} else {
    echo "Aucun membre trouvé.\n";
}
