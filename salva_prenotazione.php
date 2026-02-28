<?php
session_start();
require_once 'db.php';

// Sicurezza
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: prenotazione.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ruolo = strtolower(trim($_SESSION['user_ruolo'] ?? 'dipendente'));

// Presa dati dal Form
$asset_id = $_POST['asset_id'] ?? '';
$slot_id = $_POST['slot_id'] ?? null;
$tipo_risorsa = $_POST['tipo_risorsa'] ?? ''; // Passato dal JS
$data_prenotazione = $_POST['data'] ?? '';
$ora_inizio = $_POST['inizio'] ?? '';
$ora_fine = $_POST['fine'] ?? '';

if (empty($asset_id) || empty($data_prenotazione) || empty($ora_inizio) || empty($ora_fine)) {
    header("Location: prenotazione.php?error=Compila tutti i dati.");
    exit();
}

// 1. LIMITI LOGICI PER RUOLO
// Ipotizziamo: Parcheggio (Max 1 sempre, tranne Admin). Scrivania (Dipendente max 1, Coordinatore max 2, Admin illimitati)
$is_parcheggio = strpos($asset_id, 'park') !== false;
$is_scrivania = strpos($asset_id, 'desk') !== false;

if ($ruolo !== 'amministratore') {
    $limite_parcheggio = 1;
    $limite_scrivania = ($ruolo === 'coordinatore') ? 2 : 1;

    // Contiamo quante prenotazioni l'utente ha già fatto oggi per il tipo specifico
    $stmt_count = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN risorsa_id LIKE '%park%' THEN 1 ELSE 0 END) as tot_park,
            SUM(CASE WHEN risorsa_id LIKE '%desk%' THEN 1 ELSE 0 END) as tot_desk
        FROM prenotazioni 
        WHERE utente_id = ? AND data_prenotazione = ?
    ");
    $stmt_count->execute([$user_id, $data_prenotazione]);
    $conteggio = $stmt_count->fetch(PDO::FETCH_ASSOC);

    if ($is_parcheggio && $conteggio['tot_park'] >= $limite_parcheggio) {
        header("Location: prenotazione.php?error=Limite parcheggi raggiunto per questa data.");
        exit();
    }
    if ($is_scrivania && $conteggio['tot_desk'] >= $limite_scrivania) {
        header("Location: prenotazione.php?error=Limite scrivanie raggiunto per questa data.");
        exit();
    }
}

// 2. CONTROLLO DISPONIBILITÀ ORARIO (evitare sovrapposizioni)
$stmt_check = $pdo->prepare("
    SELECT id FROM prenotazioni 
    WHERE data_prenotazione = ? 
    AND risorsa_id = ? 
    AND (slot_id = ? OR slot_id IS NULL OR ? = '')
    AND (ora_inizio < ? AND ora_fine > ?)
");
$stmt_check->execute([$data_prenotazione, $asset_id, $slot_id, $slot_id, $ora_fine, $ora_inizio]);

if ($stmt_check->rowCount() > 0) {
    header("Location: prenotazione.php?error=Risorsa già occupata in questo orario.");
    exit();
}

// 3. INSERIMENTO DATABASE
try {
    // Adatta il nome delle colonne a quelle effettive del tuo DB (`db.sql`)
    $stmt_insert = $pdo->prepare("
        INSERT INTO prenotazioni (utente_id, risorsa_id, slot_id, data_prenotazione, ora_inizio, ora_fine) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt_insert->execute([$user_id, $asset_id, $slot_id, $data_prenotazione, $ora_inizio, $ora_fine]);

    // Ritorna con il flag success che innescherà il Popup
    header("Location: prenotazione.php?success=1&data={$data_prenotazione}&inizio={$ora_inizio}&fine={$ora_fine}");
    exit();

} catch (PDOException $e) {
    header("Location: prenotazione.php?error=Errore del server durante il salvataggio.");
    exit();
}
?>