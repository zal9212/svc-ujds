-- Données de test pour le Système de Gestion des Versements
USE svc_ujds;

-- Utilisateurs par défaut
-- Mot de passe pour tous: password123
INSERT INTO utilisateurs (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('comptable', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'comptable'),
('membre', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'membre');

-- Membres de test
INSERT INTO membres (numero, code, telephone, titre, designation, misside, montant_mensuel, statut) VALUES
(1, 'MBR001', '+243 900 000 001', 'M.', 'Jean Mukendi', 'Kinshasa', 50000.00, 'ACTIF'),
(2, 'MBR002', '+243 900 000 002', 'Mme', 'Marie Kabongo', 'Lubumbashi', 50000.00, 'ACTIF'),
(3, 'MBR003', '+243 900 000 003', 'M.', 'Pierre Tshisekedi', 'Goma', 50000.00, 'VG'),
(4, 'MBR004', '+243 900 000 004', 'M.', 'Joseph Kalala', 'Kinshasa', 50000.00, 'SUSPENDU'),
(5, 'MBR005', '+243 900 000 005', 'Mme', 'Grace Mbuyi', 'Matadi', 50000.00, 'ACTIF');

-- Versements de test (année 2024)
-- Membre 1: Tous payés
INSERT INTO versements (membre_id, mois, annee, montant, statut, date_paiement) VALUES
(1, 'janvier', 2024, 50000.00, 'PAYE', '2024-01-15'),
(1, 'février', 2024, 50000.00, 'PAYE', '2024-02-15'),
(1, 'mars', 2024, 50000.00, 'PAYE', '2024-03-15'),
(1, 'avril', 2024, 50000.00, 'PAYE', '2024-04-15'),
(1, 'mai', 2024, 50000.00, 'PAYE', '2024-05-15'),
(1, 'juin', 2024, 50000.00, 'PAYE', '2024-06-15'),
(1, 'juillet', 2024, 50000.00, 'PAYE', '2024-07-15'),
(1, 'août', 2024, 50000.00, 'PAYE', '2024-08-15'),
(1, 'septembre', 2024, 50000.00, 'PAYE', '2024-09-15'),
(1, 'octobre', 2024, 50000.00, 'PAYE', '2024-10-15'),
(1, 'novembre', 2024, 50000.00, 'PAYE', '2024-11-15'),
(1, 'décembre', 2024, 50000.00, 'PAYE', '2024-12-15');

-- Membre 2: Quelques retards
INSERT INTO versements (membre_id, mois, annee, montant, statut, date_paiement) VALUES
(2, 'janvier', 2024, 50000.00, 'PAYE', '2024-01-20'),
(2, 'février', 2024, 50000.00, 'PAYE', '2024-02-20'),
(2, 'mars', 2024, 50000.00, 'PAYE', '2024-03-20'),
(2, 'avril', 2024, 0.00, 'EN_ATTENTE', NULL),
(2, 'mai', 2024, 0.00, 'EN_ATTENTE', NULL),
(2, 'juin', 2024, 30000.00, 'PARTIEL', '2024-06-25'),
(2, 'juillet', 2024, 50000.00, 'PAYE', '2024-07-20'),
(2, 'août', 2024, 50000.00, 'PAYE', '2024-08-20'),
(2, 'septembre', 2024, 0.00, 'EN_ATTENTE', NULL),
(2, 'octobre', 2024, 0.00, 'EN_ATTENTE', NULL),
(2, 'novembre', 2024, 0.00, 'EN_ATTENTE', NULL),
(2, 'décembre', 2024, 0.00, 'EN_ATTENTE', NULL);

-- Membre 3: VG (aucun versement attendu)
-- Pas de versements pour les membres VG

-- Membre 4: Suspendu
INSERT INTO versements (membre_id, mois, annee, montant, statut, date_paiement) VALUES
(4, 'janvier', 2024, 50000.00, 'PAYE', '2024-01-10'),
(4, 'février', 2024, 50000.00, 'PAYE', '2024-02-10'),
(4, 'mars', 2024, 0.00, 'ANNULE', NULL),
(4, 'avril', 2024, 0.00, 'ANNULE', NULL);

-- Membre 5: Actif avec paiements réguliers
INSERT INTO versements (membre_id, mois, annee, montant, statut, date_paiement) VALUES
(5, 'janvier', 2024, 50000.00, 'PAYE', '2024-01-12'),
(5, 'février', 2024, 50000.00, 'PAYE', '2024-02-12'),
(5, 'mars', 2024, 50000.00, 'PAYE', '2024-03-12'),
(5, 'avril', 2024, 50000.00, 'PAYE', '2024-04-12'),
(5, 'mai', 2024, 50000.00, 'PAYE', '2024-05-12'),
(5, 'juin', 2024, 50000.00, 'PAYE', '2024-06-12'),
(5, 'juillet', 2024, 0.00, 'EN_ATTENTE', NULL),
(5, 'août', 2024, 0.00, 'EN_ATTENTE', NULL),
(5, 'septembre', 2024, 0.00, 'EN_ATTENTE', NULL),
(5, 'octobre', 2024, 0.00, 'EN_ATTENTE', NULL),
(5, 'novembre', 2024, 0.00, 'EN_ATTENTE', NULL),
(5, 'décembre', 2024, 0.00, 'EN_ATTENTE', NULL);

-- Avances de test
INSERT INTO avances (membre_id, montant, motif, date_avance) VALUES
(2, 100000.00, 'Avance sur cotisations futures', '2024-03-01'),
(5, 50000.00, 'Avance partielle', '2024-06-01');

-- Historique de test
INSERT INTO historique (utilisateur_id, action, table_name, record_id, details) VALUES
(1, 'CREATE', 'membres', 1, 'Création du membre Jean Mukendi'),
(1, 'CREATE', 'membres', 2, 'Création du membre Marie Kabongo'),
(1, 'UPDATE', 'versements', 1, 'Paiement marqué comme PAYE'),
(2, 'CREATE', 'avances', 1, 'Avance de 100000 FCFA pour Marie Kabongo');
