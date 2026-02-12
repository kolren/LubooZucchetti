CREATE DATABASE IF NOT EXISTS luboo_zucchetti5ib;
USE luboo_zucchetti5ib;

-- Cancella la tabella vecchia se esiste per applicare le modifiche
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'coordinator', 'employee') NOT NULL,
    
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
('ad.mvalentina', 'admin', 'admin', 'Valentina', 'Malatesta', '2007-11-23', 'F', 18, '9B21k3'),
('co.amaichol', 'coordinatore', 'coordinator', 'Aprea', 'Maichol', '2007-04-18', 'M', 18, '1X88m9'),
('co.knehemie', 'coordinatore', 'coordinator', 'Kablan', 'Nehemie', '2007-02-10', 'M', 18, '7P44q2'),
('dp.pana', 'dipendente', 'employee', 'Padurariu', 'Ana', '2007-11-30', 'M', 18, '3L55z1'),
('dp.pgiovanni', 'dipendente', 'employee', 'Papetti', 'Giovanni', '2007-04-12', 'M', 18, '8H99o4'),
('dp.nthomas', 'dipendente', 'employee', 'Nervi', 'Thomas', '2007-01-01', 'M', 18, '2J66w7');