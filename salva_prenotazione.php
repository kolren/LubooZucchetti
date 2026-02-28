<?php
session_start();
require_once 'db.php';

// Controllo Login
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

// Se la richiesta non è in POST, rimanda indietro
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: prenotazione.php");
    exit();
}

// 1. Acquisizione Dati
$utente_id = $_SESSION['user_id'];
$ruolo = strtolower(trim(isset($_SESSION['user_ruolo']) ? $_SESSION['user_ruolo'] : 'dipendente'));
$asset_id = intval($_POST['asset_id']);
$data = $_POST['data'];
$inizio = $_POST['inizio'];
$fine = $_POST['fine'];

// Funzione di utilità per rimandare alla pagina prenotazioni con i filtri mantenuti
function redirectWithError($msg, $data, $inizio, $fine) {
    $url = "prenotazione.php?error=" . urlencode($msg) . "&data=$data&inizio=$inizio&fine=$fine";
    header("Location: $url");
    exit();
}

// 2. Validazioni Base
if (empty($asset_id) || empty($data) || empty($inizio) || empty($fine)) {
    redirectWithError("Tutti i campi sono obbligatori.", $data, $inizio, $fine);
}

if (strtotime($inizio) >= strtotime($fine)) {
    redirectWithError("L'orario di fine deve essere successivo all'orario di inizio.", $data, $inizio, $fine);
}

if (strtotime($data) < strtotime(date('Y-m-d'))) {
    redirectWithError("Non puoi prenotare in una data passata.", $data, $inizio, $fine);
}

// 3. Recupero informazioni dell'Asset dal DB
$stmt = $conn->prepare("SELECT tipo, nome FROM asset WHERE id = ?");
$stmt->bind_param("i", $asset_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    redirectWithError("La risorsa selezionata non esiste nel database.", $data, $inizio, $fine);
}
$asset = $res->fetch_assoc();
$tipo_risorsa = $asset['tipo'];

// 4. Controllo Sovrapposizioni (Qualcun altro ha già prenotato questo posto a quest'ora?)
$stmt = $conn->prepare("
    SELECT id FROM prenotazioni 
    WHERE asset_id = ? 
    AND data_prenotazione = ? 
    AND stato != 'annullata' 
    AND (ora_inizio < ? AND ora_fine > ?)
");
// Logica sovrapposizione: inizio_esistente < fine_nuova E fine_esistente > inizio_nuova
$stmt->bind_param("isss", $asset_id, $data, $fine, $inizio);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    redirectWithError("Questa risorsa è già occupata nell'orario selezionato.", $data, $inizio, $fine);
}

// 5. Controllo Limiti e Permessi dell'Utente per la giornata
$prenotazioni_oggi = ['base' => 0, 'tech' => 0, 'meeting' => 0, 'parking' => 0];

$stmt = $conn->prepare("
    SELECT a.tipo, COUNT(*) as conteggio 
    FROM prenotazioni p 
    JOIN asset a ON p.asset_id = a.id 
    WHERE p.utente_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata'
    GROUP BY a.tipo
");
$stmt->bind_param("is", $utente_id, $data);
if (!$stmt->execute()) {
    redirectWithError("Errore nella lettura delle prenotazioni.", $data, $inizio, $fine);
}
$stmt->execute();
$res_user_occ = $stmt->get_result();
while ($row = $res_user_occ->fetch_assoc()) {
    $prenotazioni_oggi[strtolower($row['tipo'])] = (int)$row['conteggio'];
}

$tot_scrivanie = $prenotazioni_oggi['base'] + $prenotazioni_oggi['tech'];

if ($ruolo === 'dipendente') {
    if ($tipo_risorsa === 'meeting') {
        redirectWithError("Non hai i permessi per prenotare una Sala Riunioni.", $data, $inizio, $fine);
    }
    if (($tipo_risorsa === 'base' || $tipo_risorsa === 'tech') && $tot_scrivanie >= 1) {
        redirectWithError("Hai già raggiunto il limite massimo (1 scrivania) per questa giornata.", $data, $inizio, $fine);
    }
    if ($tipo_risorsa === 'parking' && $prenotazioni_oggi['parking'] >= 1) {
        redirectWithError("Hai già prenotato un posto auto per questa giornata.", $data, $inizio, $fine);
    }
} 
elseif ($ruolo === 'coordinatore') {
    if (($tipo_risorsa === 'base' || $tipo_risorsa === 'tech') && $tot_scrivanie >= 1) {
        redirectWithError("Hai già raggiunto il limite massimo (1 scrivania) per questa giornata.", $data, $inizio, $fine);
    }
    if ($tipo_risorsa === 'meeting' && $prenotazioni_oggi['meeting'] >= 2) {
        redirectWithError("Hai già raggiunto il limite massimo (2 sale riunioni) per questa giornata.", $data, $inizio, $fine);
    }
    if ($tipo_risorsa === 'parking' && $prenotazioni_oggi['parking'] >= 1) {
        redirectWithError("Hai già prenotato un posto auto per questa giornata.", $data, $inizio, $fine);
    }
}
// Gli 'amministratori' bypassano i limiti.

// 6. Salvataggio della Prenotazione (Prepared Statement per Sicurezza SQL)
$stmt = $conn->prepare("
    INSERT INTO prenotazioni (utente_id, asset_id, data_prenotazione, ora_inizio, ora_fine, stato) 
    VALUES (?, ?, ?, ?, ?, 'attiva')
");
$stmt->bind_param("iisss", $utente_id, $asset_id, $data, $inizio, $fine);

if ($stmt->execute()) {
    // Redirezione con successo!
    $url = "prenotazione.php?success=" . urlencode("Prenotazione salvata con successo per " . $asset['nome'] . "!") . "&data=$data&inizio=$inizio&fine=$fine";
    header("Location: $url");
    exit();
} else {
    redirectWithError("Si è verificato un errore nel salvataggio. Riprova più tardi.", $data, $inizio, $fine);
}

$stmt->close();
$conn->close();
?>