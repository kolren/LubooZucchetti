<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: prenotazione.php");
    exit();
}

$utente_id = $_SESSION['user_id'];
$asset_id = intval($_POST['asset_id']);
$data = $_POST['data'];
$inizio = $_POST['inizio'];
$fine = $_POST['fine'];

// Recupera il ruolo aggiornato dal DB per massima sicurezza
$stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->bind_param("i", $utente_id);
$stmt_role->execute();
$role_data = $stmt_role->get_result()->fetch_assoc();
$ruolo = strtolower(trim(isset($role_data['role']) ? $role_data['role'] : 'dipendente'));

function redirectWithError($msg, $data, $inizio, $fine) {
    $url = "prenotazione.php?error=" . urlencode($msg) . "&data=$data&inizio=$inizio&fine=$fine";
    header("Location: $url");
    exit();
}

if (empty($asset_id) || empty($data) || empty($inizio) || empty($fine)) {
    redirectWithError("Tutti i campi sono obbligatori.", $data, $inizio, $fine);
}
if (strtotime($inizio) >= strtotime($fine)) {
    redirectWithError("L'orario di fine deve essere successivo all'orario di inizio.", $data, $inizio, $fine);
}
if (strtotime($data) < strtotime(date('Y-m-d'))) {
    redirectWithError("Non puoi prenotare in una data passata.", $data, $inizio, $fine);
}

// 1. Recupero informazioni dell'Asset dal DB
$stmt = $conn->prepare("SELECT tipo, nome FROM asset WHERE id = ?");
$stmt->bind_param("i", $asset_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    redirectWithError("La risorsa selezionata non esiste.", $data, $inizio, $fine);
}
$asset = $res->fetch_assoc();
$tipo_risorsa = strtolower($asset['tipo']);

// 2. Controllo Sovrapposizione RISORSA (Qualcun altro ha già prenotato questo asset a quest'ora?)
$stmt_res_occ = $conn->prepare("
    SELECT id FROM prenotazioni 
    WHERE asset_id = ? AND data_prenotazione = ? AND stato != 'annullata' 
    AND (ora_inizio < ? AND ora_fine > ?)
");
$stmt_res_occ->bind_param("isss", $asset_id, $data, $fine, $inizio);
$stmt_res_occ->execute();
if ($stmt_res_occ->get_result()->num_rows > 0) {
    redirectWithError("Questa risorsa è già occupata da un altro utente nell'orario selezionato.", $data, $inizio, $fine);
}

// 3. Controllo UBIQUITÀ (Sovrapposizione Fisica per lo STESSO UTENTE - Valido per TUTTI i ruoli)
// Dividiamo le postazioni fisiche (base, tech, meeting) dal parcheggio
$condizione_tipo = ($tipo_risorsa === 'parking') ? "a.tipo = 'parking'" : "a.tipo != 'parking'";

$stmt_ubi = $conn->prepare("
    SELECT a.nome FROM prenotazioni p
    JOIN asset a ON p.asset_id = a.id
    WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata' 
    AND $condizione_tipo
    AND (p.ora_inizio < ? AND p.ora_fine > ?)
");
$stmt_ubi->bind_param("isss", $utente_id, $data, $fine, $inizio);
$stmt_ubi->execute();
$res_ubi = $stmt_ubi->get_result();

if ($res_ubi->num_rows > 0) {
    $risorsa_in_conflitto = $res_ubi->fetch_assoc()['nome'];
    if ($tipo_risorsa === 'parking') {
        redirectWithError("Hai già il posto auto '$risorsa_in_conflitto' in questo orario.", $data, $inizio, $fine);
    } else {
        redirectWithError("Non puoi essere in due posti contemporaneamente! In questo lasso di tempo stai già occupando: $risorsa_in_conflitto.", $data, $inizio, $fine);
    }
}

// 4. Controllo LIMITI GIORNALIERI per Ruolo (Esclude l'Amministratore)
if ($ruolo !== 'amministratore') {
    $prenotazioni_oggi = ['base' => 0, 'tech' => 0, 'meeting' => 0, 'parking' => 0];

    $stmt_lim = $conn->prepare("
        SELECT a.tipo, COUNT(*) as conteggio 
        FROM prenotazioni p JOIN asset a ON p.asset_id = a.id 
        WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata'
        GROUP BY a.tipo
    ");
    $stmt_lim->bind_param("is", $utente_id, $data);
    $stmt_lim->execute();
    $res_user_occ = $stmt_lim->get_result();
    while ($row = $res_user_occ->fetch_assoc()) {
        $prenotazioni_oggi[strtolower($row['tipo'])] = (int)$row['conteggio'];
    }

    $tot_scrivanie = $prenotazioni_oggi['base'] + $prenotazioni_oggi['tech'];

    if ($ruolo === 'dipendente') {
        if ($tipo_risorsa === 'meeting') {
            redirectWithError("Non hai i permessi per prenotare una Sala Riunioni.", $data, $inizio, $fine);
        }
        if (($tipo_risorsa === 'base' || $tipo_risorsa === 'tech') && $tot_scrivanie >= 1) {
            redirectWithError("Hai già raggiunto il limite massimo di 1 scrivania per oggi.", $data, $inizio, $fine);
        }
        if ($tipo_risorsa === 'parking' && $prenotazioni_oggi['parking'] >= 1) {
            redirectWithError("Hai già prenotato un posto auto per oggi.", $data, $inizio, $fine);
        }
    } 
    elseif ($ruolo === 'coordinatore') {
        if (($tipo_risorsa === 'base' || $tipo_risorsa === 'tech') && $tot_scrivanie >= 1) {
            redirectWithError("Hai già raggiunto il limite massimo di 1 scrivania per oggi.", $data, $inizio, $fine);
        }
        if ($tipo_risorsa === 'parking' && $prenotazioni_oggi['parking'] >= 1) {
            redirectWithError("Hai già prenotato un posto auto per oggi.", $data, $inizio, $fine);
        }
        if ($tipo_risorsa === 'meeting' && $prenotazioni_oggi['meeting'] >= 2) {
            redirectWithError("Hai già raggiunto il limite massimo di 2 sale riunioni per oggi.", $data, $inizio, $fine);
        }
    }
}

// 5. Salvataggio della Prenotazione se tutti i controlli sono passati
$stmt_insert = $conn->prepare("
    INSERT INTO prenotazioni (user_id, asset_id, data_prenotazione, ora_inizio, ora_fine, stato) 
    VALUES (?, ?, ?, ?, ?, 'attiva')
");
$stmt_insert->bind_param("iisss", $utente_id, $asset_id, $data, $inizio, $fine);

if ($stmt_insert->execute()) {
    $url = "prenotazione.php?success=1&asset_nome=" . urlencode($asset['nome']) . "&data=$data&inizio=$inizio&fine=$fine";
    header("Location: $url");
    exit();
} else {
    redirectWithError("Si è verificato un errore nel salvataggio. Riprova più tardi.", $data, $inizio, $fine);
}

$conn->close();
?>