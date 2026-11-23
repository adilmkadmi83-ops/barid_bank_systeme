-- ===== database.sql =====
-- Création de la base de données pour le projet Banque Barid Bank

CREATE DATABASE IF NOT EXISTS baridcash CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE baridcash;

-- 1. Table des utilisateurs (pour la connexion)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insertion de l'utilisateur de test (admin / 1234)
-- Compte de DÉMONSTRATION local uniquement. Le mot de passe est stocké haché (password_hash).
INSERT IGNORE INTO users (username, password) VALUES ('admin', '$2y$10$kUpHtHcmmPB7pkebQhjCbedqv2mQ.ErcLG1JR5/e64I0V2ppObyeu');

-- 2. Table des mandats
CREATE TABLE IF NOT EXISTS mandats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_mandat VARCHAR(50) NOT NULL,
    nom_expediteur VARCHAR(100) NOT NULL,
    nom_beneficiaire VARCHAR(100) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table des paiements (Barid Pay)
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(100) NOT NULL,
    numero_client VARCHAR(50) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Table des réclamations
CREATE TABLE IF NOT EXISTS reclamations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(100) NOT NULL,
    sujet VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    statut VARCHAR(50) DEFAULT 'En attente',
    date_reclamation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. Table de la comptabilité
CREATE TABLE IF NOT EXISTS comptabilite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_operation VARCHAR(50) NOT NULL, -- Entrée ou Sortie
    description VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_operation DATETIME DEFAULT CURRENT_TIMESTAMP
);
