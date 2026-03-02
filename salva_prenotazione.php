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
$ruolo = strtolower(trim($role_data['role'] ?? 'dipendente'));

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

// 2. Controllo Sovrapposizioni SULLA RISORSA (Qualcun altro ha già prenotato questo asset a quest'ora?)
$stmt = $conn->prepare("
    SELECT id FROM prenotazioni 
    WHERE asset_id = ? AND data_prenotazione = ? AND stato != 'annullata' 
    AND (ora_inizio < ? AND ora_fine > ?)
");
$stmt->bind_param("isss", $asset_id, $data, $fine, $inizio);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    redirectWithError("Questa risorsa è già occupata nell'orario selezionato.", $data, $inizio, $fine);
}

// 3. Controllo Limiti e Permessi dell'Utente per la giornata
if ($ruolo !== 'amministratore') {
    $prenotazioni_oggi = ['base' => 0, 'tech' => 0, 'meeting' => 0, 'parking' => 0];

    $stmt = $conn->prepare("
        SELECT a.tipo, COUNT(*) as conteggio 
        FROM prenotazioni p 
        JOIN asset a ON p.asset_id = a.id 
        WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata'
        GROUP BY a.tipo
    ");
    $stmt->bind_param("is", $utente_id, $data);
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
        if ($tipo_risorsa === 'meeting') {
            if ($prenotazioni_oggi['meeting'] >= 2) {
                redirectWithError("Hai già raggiunto il limite massimo di 2 sale riunioni per oggi.", $data, $inizio, $fine);
            }
            // Controllo sovrapposizione TUA con altre sale riunioni (non in contemporanea)
            $stmt_contemp = $conn->prepare("
                SELECT p.id FROM prenotazioni p
                JOIN asset a ON p.asset_id = a.id
                WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata' AND a.tipo = 'meeting'
                AND (p.ora_inizio < ? AND p.ora_fine > ?)
            ");
            $stmt_contemp->bind_param("isss", $utente_id, $data, $fine, $inizio);
            $stmt_contemp->execute();
            if ($stmt_contemp->get_result()->num_rows > 0) {
                redirectWithError("Stai già occupando un'altra Sala Riunioni in questo orario. Non puoi prenotarle in contemporanea.", $data, $inizio, $fine);
            }
        }
    }
}

// 4. Salvataggio della Prenotazione
$stmt = $conn->prepare("
    INSERT INTO prenotazioni (user_id, asset_id, data_prenotazione, ora_inizio, ora_fine, stato) 
    VALUES (?, ?, ?, ?, ?, 'attiva')
");
$stmt->bind_param("iisss", $utente_id, $asset_id, $data, $inizio, $fine);

if ($stmt->execute()) {
    $url = "prenotazione.php?success=1&asset_nome=" . urlencode($asset['nome']) . "&data=$data&inizio=$inizio&fine=$fine";
    header("Location: $url");
    exit();
} else {
    redirectWithError("Si è verificato un errore nel salvataggio. Riprova più tardi.", $data, $inizio, $fine);
}

$stmt->close();
$conn->close();
?>