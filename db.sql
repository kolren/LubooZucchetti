CREATE DATABASE IF NOT EXISTS luboo_zucchetti5ib;
USE luboo_zucchetti5ib;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS prenotazioni;
DROP TABLE IF EXISTS asset;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

-- ==========================================
-- TABELLA UTENTI
-- ==========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('amministratore', 'coordinatore', 'dipendente') NOT NULL,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    data_nascita DATE NOT NULL,
    sesso ENUM('M', 'F', 'Altro') NOT NULL,
    eta INT NOT NULL,
    codice_identificativo VARCHAR(6) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, role, nome, cognome, data_nascita, sesso, eta, codice_identificativo) VALUES 
('ad.mvalentina', 'amministratore', 'amministratore', 'Valentina', 'Malatesta', '2007-11-23', 'F', 18, '9B21k3'),
('co.amaichol', 'coordinatore', 'coordinatore', 'Maichol', 'Aprea', '2007-04-18', 'M', 18, '1X88m9'),
('co.knehemie', 'coordinatore', 'coordinatore', 'Nehemie', 'Kablan', '2007-02-10', 'M', 18, '7P44q2'),
('dp.pana', 'dipendente', 'dipendente', 'Ana', 'Padurariu', '2007-11-30', 'M', 18, '3L55z1'),
('dp.pgiovanni', 'dipendente', 'dipendente', 'Giovanni', 'Papetti', '2007-04-12', 'M', 18, '8H99o4'),
('dp.nthomas', 'dipendente', 'dipendente', 'Thomas', 'Nervi', '2007-01-01', 'M', 18, '2J66w7');

-- ==========================================
-- TABELLA ASSET (Gestione Spazi Z-Volta)
-- ==========================================
CREATE TABLE asset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Tipo A (base), Tipo A2 (tech), Tipo B (meeting), Tipo C (parking)
    tipo ENUM('base', 'tech', 'meeting', 'parking') NOT NULL, 
    nome VARCHAR(100) NOT NULL,
    codice_univoco VARCHAR(50) NOT NULL UNIQUE, 
    piano VARCHAR(50) DEFAULT 'Piano 1',
    armadietto VARCHAR(50) DEFAULT NULL, -- N/A per Sale e Parcheggi
    stato_strutturale ENUM('disponibile', 'non_prenotabile') DEFAULT 'disponibile'
);

-- Popolamento massivo secondo i minimi richiesti dal Caso Studio:
-- 20 Unità Tipo A (Base)
INSERT INTO asset (tipo, nome, codice_univoco, armadietto, piano)
WITH RECURSIVE seq AS (SELECT 1 AS v UNION ALL SELECT v + 1 FROM seq WHERE v < 20)
SELECT 'base', CONCAT('Scrivania Base ', v), CONCAT('desk-b-', v), CONCAT('ARM-', v), 'Piano 1' FROM seq;

-- 30 Unità Tipo A2 (Tech)
INSERT INTO asset (tipo, nome, codice_univoco, armadietto, piano)
WITH RECURSIVE seq AS (SELECT 1 AS v UNION ALL SELECT v + 1 FROM seq WHERE v < 30)
SELECT 'tech', CONCAT('Scrivania Tech ', v), CONCAT('desk-t-', v), CONCAT('ARM-T', v), 'Piano 2' FROM seq;

-- 5 Unità Tipo B (Sale Riunioni)
INSERT INTO asset (tipo, nome, codice_univoco, armadietto, piano)
WITH RECURSIVE seq AS (SELECT 1 AS v UNION ALL SELECT v + 1 FROM seq WHERE v < 5)
SELECT 'meeting', CONCAT('Sala Meeting ', v), CONCAT('room-', v), 'N/A', 'Piano 1' FROM seq;

-- 10 Unità Tipo C (Parcheggi)
INSERT INTO asset (tipo, nome, codice_univoco, armadietto, piano)
WITH RECURSIVE seq AS (SELECT 1 AS v UNION ALL SELECT v + 1 FROM seq WHERE v < 10)
SELECT 'parking', CONCAT('Posto Auto ', v), CONCAT('park-', v), 'N/A', 'Piano Interrato' FROM seq;

-- ==========================================
-- TABELLA PRENOTAZIONI
-- ==========================================
CREATE TABLE prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    data_prenotazione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    stato ENUM('attiva', 'annullata', 'conclusa') DEFAULT 'attiva',
    numero_modifiche INT DEFAULT 0, -- Limite a 2 come da specifiche
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES asset(id) ON DELETE CASCADE
);