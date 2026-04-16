<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['partner_id'])) {
    exit();
}

$mio_id = $_SESSION['user_id'];
$partner_id = intval($_GET['partner_id']);

// Segna i messaggi ricevuti come letti
$stmt_update = $conn->prepare("UPDATE messaggi SET letto = 1 WHERE mittente_id = ? AND destinatario_id = ?");
$stmt_update->bind_param("ii", $partner_id, $mio_id);
$stmt_update->execute();

// Recupera la cronologia
$stmt = $conn->prepare("
    SELECT * FROM messaggi 
    WHERE (mittente_id = ? AND destinatario_id = ?) 
       OR (mittente_id = ? AND destinatario_id = ?) 
    ORDER BY data_invio ASC
");
$stmt->bind_param("iiii", $mio_id, $partner_id, $partner_id, $mio_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $is_mio = ($row['mittente_id'] == $mio_id);
    $time = date('H:i', strtotime($row['data_invio']));
    
    if ($is_mio) {
        // Messaggio inviato da me (Verde, a destra)
        echo '<div class="flex justify-end mb-2">';
        echo '<div class="bg-[#36A482] text-white rounded-l-xl rounded-tr-xl px-4 py-2 max-w-[80%] text-sm shadow-md">';
        echo htmlspecialchars($row['testo']);
        echo '<div class="text-[10px] text-white/60 text-right mt-1">'.$time.'</div>';
        echo '</div></div>';
    } else {
        // Messaggio ricevuto (Blu scuro, a sinistra)
        echo '<div class="flex justify-start mb-2">';
        echo '<div class="bg-[#0A2338] border border-[#BFD6E8]/20 text-white rounded-r-xl rounded-tl-xl px-4 py-2 max-w-[80%] text-sm shadow-md">';
        echo htmlspecialchars($row['testo']);
        echo '<div class="text-[10px] text-[#BFD6E8]/60 text-left mt-1">'.$time.'</div>';
        echo '</div></div>';
    }
}
?>