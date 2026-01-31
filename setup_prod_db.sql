-- Script de création de la base de production et de l'utilisateur
-- NE PAS EXÉCUTER SUR UNE BASE EXISTANTE IMPORTANTE SANS BACKUP

CREATE DATABASE IF NOT EXISTS svc_ujds_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'svc_prod_user'@'localhost' IDENTIFIED BY 'P@ssw0rd_Prod_Secur3!99';
GRANT ALL PRIVILEGES ON svc_ujds_prod.* TO 'svc_prod_user'@'localhost';
FLUSH PRIVILEGES;

USE svc_ujds_prod;

-- Import du schéma (copie de database/schema.sql)
-- Note: On suppose que le contenu de schema.sql suit ici.
-- Pour simplifier, nous allons demander à l'agent de lire schema.sql et de l'appliquer séparément ou concaténé.
-- Ici, je vais laisser l'instruction d'import à faire via ligne de commande pour plus de robustesse.
