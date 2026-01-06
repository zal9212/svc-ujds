-- Schéma de base de données pour le Système de Gestion des Versements
-- Base de données: svc_ujds

CREATE DATABASE IF NOT EXISTS svc_ujds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE svc_ujds;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'comptable', 'membre') NOT NULL DEFAULT 'membre',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des membres
CREATE TABLE IF NOT EXISTS membres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    code VARCHAR(50) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    titre VARCHAR(50),
    designation VARCHAR(255) NOT NULL,
    misside VARCHAR(100),
    montant_mensuel DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    statut ENUM('ACTIF', 'VG', 'SUSPENDU') NOT NULL DEFAULT 'ACTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_statut (statut),
    INDEX idx_designation (designation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des versements
CREATE TABLE IF NOT EXISTS versements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    mois VARCHAR(20) NOT NULL,
    annee INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    statut ENUM('EN_ATTENTE', 'PAYE', 'PARTIEL', 'ANNULE') NOT NULL DEFAULT 'EN_ATTENTE',
    date_paiement DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE,
    INDEX idx_membre_id (membre_id),
    INDEX idx_statut (statut),
    INDEX idx_mois_annee (mois, annee),
    UNIQUE KEY unique_versement (membre_id, mois, annee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des avances
CREATE TABLE IF NOT EXISTS avances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    motif TEXT,
    date_avance DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE,
    INDEX idx_membre_id (membre_id),
    INDEX idx_date_avance (date_avance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table d'historique (audit trail)
CREATE TABLE IF NOT EXISTS historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_utilisateur_id (utilisateur_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
