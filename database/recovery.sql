USE svc_ujds;

-- missing tables for support and declarations
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT,
    image_path VARCHAR(255),
    audio_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS declarations_paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    type_paiement VARCHAR(50),
    preuve_path VARCHAR(255),
    statut ENUM('EN_ATTENTE', 'VALIDE', 'REJETE') DEFAULT 'EN_ATTENTE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES membres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS declaration_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    declaration_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT,
    image_path VARCHAR(255),
    audio_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (declaration_id) REFERENCES declarations_paiements(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- add missing columns to existing tables
ALTER TABLE versements ADD COLUMN IF NOT EXISTS declaration_id INT NULL;
ALTER TABLE versements ADD COLUMN IF NOT EXISTS has_amende TINYINT(1) DEFAULT 0;

ALTER TABLE avances ADD COLUMN IF NOT EXISTS declaration_id INT NULL;
ALTER TABLE avances ADD COLUMN IF NOT EXISTS type VARCHAR(20) DEFAULT 'AVANCE';
ALTER TABLE avances ADD COLUMN IF NOT EXISTS date_debut DATE NULL;
