CREATE DATABASE IF NOT EXISTS luboo_zucchetti5ib;
USE luboo_zucchetti5ib;

-- Disabilita i constraint di foreign key per evitare errori durante il drop
SET FOREIGN_KEY_CHECKS=0;

-- Cancella le tabelle vecchie se esistono per applicare le modifiche
DROP TABLE IF EXISTS prenotazioni;
DROP TABLE IF EXISTS postazioni;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('amministratore', 'coordinatore', 'dipendente') NOT NULL,
    
    -- Nuovi parametri richiesti
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    data_nascita DATE NOT NULL,
    sesso ENUM('M', 'F', 'Altro') NOT NULL,
    eta INT NOT NULL,
    codice_identificativo VARCHAR(6) NOT NULL UNIQUE, -- Es: 5A45u8
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserimento utenti con i nuovi dati "prefatti"
-- I codici identificativi rispettano il formato: 4 numeri e 2 lettere (posizioni miste)
INSERT INTO users (username, password, role, nome, cognome, data_nascita, sesso, eta, codice_identificativo) VALUES 
('ad.mvalentina', 'amministratore', 'amministratore', 'Valentina', 'Malatesta', '2007-11-23', 'F', 18, '9B21k3'),
('co.amaichol', 'coordinatore', 'coordinatore', 'Maichol', 'Aprea', '2007-04-18', 'M', 18, '1X88m9'),
('co.knehemie', 'coordinatore', 'coordinatore', 'Nehemie', 'Kablan', '2007-02-10', 'M', 18, '7P44q2'),
('dp.pana', 'dipendente', 'dipendente', 'Ana', 'Padurariu', '2007-11-30', 'M', 18, '3L55z1'),
('dp.pgiovanni', 'dipendente', 'dipendente', 'Giovanni', 'Papetti', '2007-04-12', 'M', 18, '8H99o4'),
('dp.nthomas', 'dipendente', 'dipendente', 'Thomas', 'Nervi', '2007-01-01', 'M', 18, '2J66w7');
USE luboo_zucchetti5ib;

-- Tabella degli Asset (Postazioni fisiche, Sale Riunioni, Parcheggi)
CREATE TABLE IF NOT EXISTS asset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('Postazione', 'Riunione', 'Parcheggio') NOT NULL,
    nome VARCHAR(100) NOT NULL,
    svg_id VARCHAR(50) NOT NULL UNIQUE, -- Es: 'desk-01', 'park-01'
    piano VARCHAR(50) DEFAULT 'Piano 1',
    is_attivo BOOLEAN DEFAULT TRUE
);

-- Tabella delle Prenotazioni
CREATE TABLE IF NOT EXISTS prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_id INT NOT NULL,
    data_prenotazione DATE NOT NULL,
    ora_inizio TIME NOT NULL,
    ora_fine TIME NOT NULL,
    stato ENUM('attiva', 'annullata', 'conclusa') DEFAULT 'attiva',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (asset_id) REFERENCES asset(id) ON DELETE CASCADE
);

-- Popolamento di base (Assicurati che gli svg_id coincidano con gli ID nei tuoi file SVG)
INSERT INTO asset (tipo, nome, svg_id) VALUES 
('Postazione', 'Scrivania 01', 'desk-01'),
('Postazione', 'Scrivania 02', 'desk-02'),
('Riunione', 'Sala Copernico', 'room-01'),
('Parcheggio', 'Posto Auto 1', 'park-01'),
('Parcheggio', 'Posto Auto 2', 'park-02')
ON DUPLICATE KEY UPDATE nome=VALUES(nome);