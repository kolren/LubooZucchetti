<?php
session_start();
require_once 'db.php';

// Verifica che l'utente sia autenticato
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autenticato']);
    exit();
}

$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$ora_inizio = isset($_GET['inizio']) ? $_GET['inizio'] : '09:00';
$ora_fine = isset($_GET['fine']) ? $_GET['fine'] : '18:00';

$occupati = [];

if (!empty($data) && !empty($ora_inizio) && !empty($ora_fine)) {
    $d = $conn->real_escape_string($data);
    $i = $conn->real_escape_string($ora_inizio);
    $f = $conn->real_escape_string($ora_fine);
    
    $query_occ = "SELECT a.codice_univoco, u.id as user_id, u.nome, u.cognome 
                  FROM prenotazioni p 
                  JOIN asset a ON p.asset_id = a.id
                  JOIN users u ON p.user_id = u.id
                  WHERE p.data_prenotazione = '$d' AND p.stato != 'annullata' 
                  AND ((p.ora_inizio < '$f' AND p.ora_fine > '$i'))";
    $res_occ = $conn->query($query_occ);
    if ($res_occ) {
        while ($row = $res_occ->fetch_assoc()) {
            $occupati[$row['codice_univoco']] = [
                'id' => $row['user_id'],
                'nome_completo' => $row['nome'] . ' ' . $row['cognome']
            ];
        }
    }
}

// Ritorna JSON
header('Content-Type: application/json');
echo json_encode([
    'occupati' => $occupati,
    'data' => $data
]);
?>
