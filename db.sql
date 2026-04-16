CREATE DATABASE IF NOT EXISTS luboo_zucchetti5ib;
USE luboo_zucchetti5ib;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS messaggi;
DROP TABLE IF EXISTS prenotazioni;
DROP TABLE IF EXISTS asset;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS team;
SET FOREIGN_KEY_CHECKS=1;

-- ==========================================
-- TABELLA TEAM
-- ==========================================
CREATE TABLE team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_team VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO team (nome_team) VALUES 
('Team Alpha - Sviluppo'), 
('Team Beta - Design'), 
('Team Gamma - Marketing');

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
    team_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    CONSTRAINT fk_users_team FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dati originari aggiornati con l'assegnazione ai Team
INSERT INTO users (username, password, role, nome, cognome, data_nascita, sesso, eta, codice_identificativo, team_id) VALUES 
('ad.mvalentina', 'amministratore', 'amministratore', 'Valentina', 'Malatesta', '2007-11-23', 'F', 18, '9B21k3', NULL),
('co.amaichol', 'coordinatore', 'coordinatore', 'Maichol', 'Aprea', '2007-04-18', 'M', 18, '1X88m9', 1),
('co.knehemie', 'coordinatore', 'coordinatore', 'Nehemie', 'Kablan', '2007-02-10', 'M', 18, '7P44q2', 2),
('dp.pana', 'dipendente', 'dipendente', 'Ana', 'Padurariu', '2007-11-30', 'M', 18, '3L55z1', 1),
('dp.pgiovanni', 'dipendente', 'dipendente', 'Giovanni', 'Papetti', '2007-04-12', 'M', 18, '8H99o4', 1),
('dp.nthomas', 'dipendente', 'dipendente', 'Thomas', 'Nervi', '2007-01-01', 'M', 18, '2J66w7', 2);

-- ==========================================
-- TABELLA MESSAGGI
-- ==========================================
CREATE TABLE IF NOT EXISTS messaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mittente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    testo TEXT NOT NULL,
    letto TINYINT(1) DEFAULT 0,
    data_invio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mittente_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Messaggi pre-impostati per simulare interazioni sulla messaggistica
INSERT INTO messaggi (mittente_id, destinatario_id, testo, letto, data_invio) VALUES 
(2, 1, 'Ciao Valentina, potresti verificare se la Sala Meeting 1 è libera per una riunione urgente domani mattina?', 1, '2026-04-15 09:15:00'),
(1, 2, 'Ciao Maichol, ho controllato. È libera dalle 10:00 alle 11:30, te l\'ho prenotata.', 1, '2026-04-15 09:30:00'),
(4, 5, 'Giovanni, scusa il disturbo. Per caso hai parcheggiato nel posto auto 10 stamattina? Risulta occupato e io avevo prenotato quello.', 1, '2026-04-16 08:35:00'),
(5, 4, 'Scusami Ana! Ho fatto confusione con le prenotazioni. Scendo subito a spostare l\'auto nel posto 12.', 1, '2026-04-16 08:38:00'),
(4, 5, 'Nessun problema, grazie mille!', 0, '2026-04-16 08:40:00'),
(6, 3, 'Nehemie, ricordati che oggi alle 14:00 abbiamo il sync per il nuovo design nella Sala 2. Ho già prenotato.', 1, '2026-04-16 09:00:00'),
(3, 6, 'Grazie Thomas, me lo ero segnato. Ci vediamo direttamente lì.', 0, '2026-04-16 09:10:00'),
(2, 4, 'Ana, ricordati di aggiornare il task su Trello prima della riunione di domani. Grazie!', 0, '2026-04-16 11:00:00');

-- ==========================================
-- TABELLA ASSET
-- ==========================================
CREATE TABLE asset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('base', 'tech', 'meeting', 'parking') NOT NULL, 
    nome VARCHAR(100) NOT NULL,
    codice_univoco VARCHAR(50) NOT NULL UNIQUE, 
    piano VARCHAR(50) DEFAULT 'Piano 1',
    armadietto VARCHAR(50) DEFAULT NULL, 
    stato_strutturale ENUM('disponibile', 'non_prenotabile') DEFAULT 'disponibile',
    INDEX idx_ricerca (piano, tipo),
    INDEX idx_tipo_asset (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

TRUNCATE TABLE asset; 
INSERT INTO asset (tipo, nome, codice_univoco, armadietto, piano) VALUES
-- PIANO 1: Sale Riunioni (1-5)
('meeting', 'Sala Meeting 1', 'room-1', 'N/A', 'Piano 1'),
('meeting', 'Sala Meeting 2', 'room-2', 'N/A', 'Piano 1'),
('meeting', 'Sala Meeting 3', 'room-3', 'N/A', 'Piano 1'),
('meeting', 'Sala Meeting 4', 'room-4', 'N/A', 'Piano 1'),
('meeting', 'Sala Meeting 5', 'room-5', 'N/A', 'Piano 1'),

-- PIANO 2: Sale Riunioni (6-10)
('meeting', 'Sala Meeting 6', 'room-6', 'N/A', 'Piano 2'),
('meeting', 'Sala Meeting 7', 'room-7', 'N/A', 'Piano 2'),
('meeting', 'Sala Meeting 8', 'room-8', 'N/A', 'Piano 2'),
('meeting', 'Sala Meeting 9', 'room-9', 'N/A', 'Piano 2'),
('meeting', 'Sala Meeting 10', 'room-10', 'N/A', 'Piano 2'),

-- PIANO 1: Scrivanie Tech (1-12)
('tech', 'Scrivania Tech 1', 'desk-t-1', 'ARM-T1', 'Piano 1'),
('tech', 'Scrivania Tech 2', 'desk-t-2', 'ARM-T2', 'Piano 1'),
('tech', 'Scrivania Tech 3', 'desk-t-3', 'ARM-T3', 'Piano 1'),
('tech', 'Scrivania Tech 4', 'desk-t-4', 'ARM-T4', 'Piano 1'),
('tech', 'Scrivania Tech 5', 'desk-t-5', 'ARM-T5', 'Piano 1'),
('tech', 'Scrivania Tech 6', 'desk-t-6', 'ARM-T6', 'Piano 1'),
('tech', 'Scrivania Tech 7', 'desk-t-7', 'ARM-T7', 'Piano 1'),
('tech', 'Scrivania Tech 8', 'desk-t-8', 'ARM-T8', 'Piano 1'),
('tech', 'Scrivania Tech 9', 'desk-t-9', 'ARM-T9', 'Piano 1'),
('tech', 'Scrivania Tech 10', 'desk-t-10', 'ARM-T10', 'Piano 1'),
('tech', 'Scrivania Tech 11', 'desk-t-11', 'ARM-T11', 'Piano 1'),
('tech', 'Scrivania Tech 12', 'desk-t-12', 'ARM-T12', 'Piano 1'),

-- PIANO 2: Scrivanie Tech (13-20)
('tech', 'Scrivania Tech 13', 'desk-t-13', 'ARM-T13', 'Piano 2'),
('tech', 'Scrivania Tech 14', 'desk-t-14', 'ARM-T14', 'Piano 2'),
('tech', 'Scrivania Tech 15', 'desk-t-15', 'ARM-T15', 'Piano 2'),
('tech', 'Scrivania Tech 16', 'desk-t-16', 'ARM-T16', 'Piano 2'),
('tech', 'Scrivania Tech 17', 'desk-t-17', 'ARM-T17', 'Piano 2'),
('tech', 'Scrivania Tech 18', 'desk-t-18', 'ARM-T18', 'Piano 2'),
('tech', 'Scrivania Tech 19', 'desk-t-19', 'ARM-T19', 'Piano 2'),
('tech', 'Scrivania Tech 20', 'desk-t-20', 'ARM-T20', 'Piano 2'),

-- PIANO 1: Scrivanie Base (1-14)
('base', 'Scrivania Base 1', 'desk-b-1', 'ARM-B1', 'Piano 1'),
('base', 'Scrivania Base 2', 'desk-b-2', 'ARM-B2', 'Piano 1'),
('base', 'Scrivania Base 3', 'desk-b-3', 'ARM-B3', 'Piano 1'),
('base', 'Scrivania Base 4', 'desk-b-4', 'ARM-B4', 'Piano 1'),
('base', 'Scrivania Base 5', 'desk-b-5', 'ARM-B5', 'Piano 1'),
('base', 'Scrivania Base 6', 'desk-b-6', 'ARM-B6', 'Piano 1'),
('base', 'Scrivania Base 7', 'desk-b-7', 'ARM-B7', 'Piano 1'),
('base', 'Scrivania Base 8', 'desk-b-8', 'ARM-B8', 'Piano 1'),
('base', 'Scrivania Base 9', 'desk-b-9', 'ARM-B9', 'Piano 1'),
('base', 'Scrivania Base 10', 'desk-b-10', 'ARM-B10', 'Piano 1'),
('base', 'Scrivania Base 11', 'desk-b-11', 'ARM-B11', 'Piano 1'),
('base', 'Scrivania Base 12', 'desk-b-12', 'ARM-B12', 'Piano 1'),
('base', 'Scrivania Base 13', 'desk-b-13', 'ARM-B13', 'Piano 1'),
('base', 'Scrivania Base 14', 'desk-b-14', 'ARM-B14', 'Piano 1'),

-- PIANO 2: Scrivanie Base (15-30)
('base', 'Scrivania Base 15', 'desk-b-15', 'ARM-B15', 'Piano 2'),
('base', 'Scrivania Base 16', 'desk-b-16', 'ARM-B16', 'Piano 2'),
('base', 'Scrivania Base 17', 'desk-b-17', 'ARM-B17', 'Piano 2'),
('base', 'Scrivania Base 18', 'desk-b-18', 'ARM-B18', 'Piano 2'),
('base', 'Scrivania Base 19', 'desk-b-19', 'ARM-B19', 'Piano 2'),
('base', 'Scrivania Base 20', 'desk-b-20', 'ARM-B20', 'Piano 2'),
('base', 'Scrivania Base 21', 'desk-b-21', 'ARM-B21', 'Piano 2'),
('base', 'Scrivania Base 22', 'desk-b-22', 'ARM-B22', 'Piano 2'),
('base', 'Scrivania Base 23', 'desk-b-23', 'ARM-B23', 'Piano 2'),
('base', 'Scrivania Base 24', 'desk-b-24', 'ARM-B24', 'Piano 2'),
('base', 'Scrivania Base 25', 'desk-b-25', 'ARM-B25', 'Piano 2'),
('base', 'Scrivania Base 26', 'desk-b-26', 'ARM-B26', 'Piano 2'),
('base', 'Scrivania Base 27', 'desk-b-27', 'ARM-B27', 'Piano 2'),
('base', 'Scrivania Base 28', 'desk-b-28', 'ARM-B28', 'Piano 2'),
('base', 'Scrivania Base 29', 'desk-b-29', 'ARM-B29', 'Piano 2'),
('base', 'Scrivania Base 30', 'desk-b-30', 'ARM-B30', 'Piano 2'),

-- PARCHEGGIO (1-25)
('parking', 'Posto Auto 1', 'park-1', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 2', 'park-2', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 3', 'park-3', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto Disabili 4', 'park-4', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto Disabili 5', 'park-5', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 6', 'park-6', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 7', 'park-7', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 8', 'park-8', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 9', 'park-9', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 10', 'park-10', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 11', 'park-11', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 12', 'park-12', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 13', 'park-13', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 14', 'park-14', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 15', 'park-15', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 16', 'park-16', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 17', 'park-17', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 18', 'park-18', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 19', 'park-19', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 20', 'park-20', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 21', 'park-21', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 22', 'park-22', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 23', 'park-23', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 24', 'park-24', 'N/A', 'Parcheggio'),
('parking', 'Posto Auto 25', 'park-25', 'N/A', 'Parcheggio');

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data_prenotazione (data_prenotazione),
    INDEX idx_stato (stato),
    CONSTRAINT fk_prenotazioni_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_prenotazioni_asset FOREIGN KEY (asset_id) REFERENCES asset(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO prenotazioni (user_id, asset_id, data_prenotazione, ora_inizio, ora_fine, stato) VALUES 

-- ==========================================
-- PRENOTAZIONI PASSATE (Stato: conclusa o annullata)
-- ==========================================
(4, 35, '2026-04-13', '09:00:00', '18:00:00', 'conclusa'),
(5, 62, '2026-04-14', '08:30:00', '18:00:00', 'conclusa'),
(1, 5, '2026-04-14', '10:00:00', '12:00:00', 'conclusa'),
(1, 63, '2026-04-14', '08:00:00', '18:00:00', 'conclusa'),
(6, 12, '2026-04-15', '09:00:00', '13:00:00', 'conclusa'),
(2, 1, '2026-04-15', '10:00:00', '12:00:00', 'annullata'),
(3, 2, '2026-04-15', '14:00:00', '16:00:00', 'conclusa'),

-- ==========================================
-- PRENOTAZIONI DI OGGI: 16 APRILE 2026
-- ==========================================
(1, 11, '2026-04-16', '08:30:00', '18:00:00', 'attiva'), 
(1, 61, '2026-04-16', '08:00:00', '18:00:00', 'attiva'), 
(1, 1, '2026-04-16', '10:00:00', '12:00:00', 'conclusa'), 
(2, 31, '2026-04-16', '09:00:00', '18:00:00', 'attiva'), 
(2, 62, '2026-04-16', '08:30:00', '17:30:00', 'attiva'), 
(3, 15, '2026-04-16', '09:00:00', '18:00:00', 'attiva'), 
(3, 5, '2026-04-16', '15:00:00', '16:30:00', 'attiva'), 
(4, 36, '2026-04-16', '09:00:00', '18:00:00', 'attiva'), 
(5, 40, '2026-04-16', '08:30:00', '17:30:00', 'attiva'), 
(6, 70, '2026-04-16', '09:00:00', '18:00:00', 'attiva'), 

-- ==========================================
-- PRENOTAZIONI FUTURE (Stato: attiva)
-- ==========================================
(1, 11, '2026-04-17', '09:00:00', '18:00:00', 'attiva'), 
(1, 8, '2026-04-17', '14:00:00', '16:00:00', 'attiva'), 
(4, 35, '2026-04-17', '09:00:00', '18:00:00', 'attiva'), 
(6, 12, '2026-04-20', '09:00:00', '18:00:00', 'attiva'), 
(3, 61, '2026-04-20', '08:00:00', '18:00:00', 'attiva'), 
(5, 63, '2026-04-20', '08:15:00', '17:45:00', 'attiva'), 
(2, 50, '2026-04-21', '09:30:00', '17:30:00', 'attiva'), 
(6, 10, '2026-04-21', '14:00:00', '18:00:00', 'attiva'), 
(1, 2,  '2026-04-21', '10:00:00', '11:30:00', 'attiva'); 

-- ==========================================
-- TABELLA LOGS
-- ==========================================
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    azione VARCHAR(100) NOT NULL,
    dettagli TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Log pre-impostati per simulare le attività sul gestionale
INSERT INTO logs (user_id, azione, dettagli, created_at) VALUES 
(4, 'Login', 'Accesso effettuato con successo.', '2026-04-13 08:50:00'),
(4, 'Nuova Prenotazione', 'Prenotata Scrivania Base 15 per il 2026-04-13.', '2026-04-13 08:55:00'),
(1, 'Login', 'Accesso effettuato con successo.', '2026-04-14 07:55:00'),
(1, 'Nuova Prenotazione', 'Prenotato Posto Auto 1 per il 2026-04-14.', '2026-04-14 07:58:00'),
(5, 'Nuova Prenotazione', 'Prenotato Posto Auto 10 per il 2026-04-14.', '2026-04-14 08:20:00'),
(2, 'Login', 'Accesso effettuato con successo.', '2026-04-15 08:15:00'),
(2, 'Annullamento Prenotazione', 'Annullata prenotazione Sala Meeting 1 per il 2026-04-15.', '2026-04-15 09:35:00'),
(1, 'Nuova Prenotazione', 'Prenotata Sala Meeting 1 per utente ID 2 per il 2026-04-16.', '2026-04-15 09:36:00'),
(1, 'Modifica Profilo', 'Aggiornati dati utente ID 4 (Ana Padurariu).', '2026-04-15 11:15:00'),
(6, 'Login', 'Accesso effettuato con successo.', '2026-04-16 08:50:00'),
(6, 'Nuova Prenotazione', 'Prenotato Posto Auto 8 per il 2026-04-16.', '2026-04-16 08:55:00');