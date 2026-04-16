<?php
session_start();
require_once 'db.php';

// Controllo sicurezza sessione
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $asset_id = intval($_POST['asset_id']);
    $data_prenotazione = $_POST['data'];
    $ora_inizio = $_POST['inizio'];
    $ora_fine = $_POST['fine'];
    $mappa_corrente = isset($_POST['mappa_corrente']) ? $_POST['mappa_corrente'] : 'piano1';

    // 1. GESTIONE DOMENICA (Controllo Backend)
    // 'w' restituisce 0 per Domenica, 1 per Lunedì, ecc.
    $giorno_settimana = date('w', strtotime($data_prenotazione));
    if ($giorno_settimana == 0) {
        header("Location: prenotazione.php?mappa=$mappa_corrente&data=$data_prenotazione&err=domenica");
        exit();
    }

    // 2. RECUPERO RUOLO E DEFINIZIONE LIMITI
    $stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_role->bind_param("i", $user_id);
    $stmt_role->execute();
    $role_result = $stmt_role->get_result()->fetch_assoc();
    $ruolo = strtolower(trim($role_result['role'] ?? 'dipendente'));

    $limiti = [
        'amministratore' => 3,
        'coordinatore' => 2,
        'dipendente' => 1
    ];
    $limite_max = $limiti[$ruolo] ?? 1;

    // 3. CONTROLLO LIMITE SULLA SINGOLA GIORNATA
    // Qui correggiamo il tuo bug filtrando per data_prenotazione = ?
    $stmt_count = $conn->prepare("
        SELECT COUNT(*) as conteggio 
        FROM prenotazioni 
        WHERE user_id = ? AND data_prenotazione = ? AND stato != 'annullata'
    ");
    $stmt_count->bind_param("is", $user_id, $data_prenotazione);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result()->fetch_assoc();
    $prenotazioni_effettuate = (int)$count_result['conteggio'];

    if ($prenotazioni_effettuate >= $limite_max) {
        header("Location: prenotazione.php?mappa=$mappa_corrente&data=$data_prenotazione&err=limite_raggiunto");
        exit();
    }

    // 4. CONTROLLO SOVRAPPOSIZIONE ORARI SULLO STESSO ASSET
    $stmt_overlap = $conn->prepare("
        SELECT id FROM prenotazioni 
        WHERE asset_id = ? AND data_prenotazione = ? AND stato != 'annullata'
        AND (ora_inizio < ? AND ora_fine > ?)
    ");
    $stmt_overlap->bind_param("isss", $asset_id, $data_prenotazione, $ora_fine, $ora_inizio);
    $stmt_overlap->execute();
    
    if ($stmt_overlap->get_result()->num_rows > 0) {
        header("Location: prenotazione.php?mappa=$mappa_corrente&data=$data_prenotazione&err=occupato");
        exit();
    }

    // 5. INSERIMENTO DELLA PRENOTAZIONE
    $stmt_insert = $conn->prepare("
        INSERT INTO prenotazioni (user_id, asset_id, data_prenotazione, ora_inizio, ora_fine, stato) 
        VALUES (?, ?, ?, ?, ?, 'attiva')
    ");
    $stmt_insert->bind_param("iisss", $user_id, $asset_id, $data_prenotazione, $ora_inizio, $ora_fine);
    
    if ($stmt_insert->execute()) {
        header("Location: prenotazione.php?mappa=$mappa_corrente&data=$data_prenotazione&success=1");
        exit();
    } else {
        header("Location: prenotazione.php?mappa=$mappa_corrente&err=db_error");
        exit();
    }
}
?>