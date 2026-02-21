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