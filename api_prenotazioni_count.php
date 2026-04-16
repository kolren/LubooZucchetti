<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Validazione data (no domenica)
$dataObj = new DateTime($data);
if ($dataObj->format('w') == 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Domenica non permessa', 'totale' => 0, 'is_weekend' => true]);
    exit();
}

// Query per contare le prenotazioni dell'utente per il giorno selezionato
$stmt = $conn->prepare("
    SELECT a.tipo, COUNT(*) as conteggio 
    FROM prenotazioni p 
    JOIN asset a ON p.asset_id = a.id 
    WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata' 
    GROUP BY a.tipo
");
$stmt->bind_param("is", $user_id, $data);
$stmt->execute();
$result = $stmt->get_result();

$prenotazioni = ['base' => 0, 'tech' => 0, 'meeting' => 0, 'parking' => 0];
while ($row = $result->fetch_assoc()) {
    $prenotazioni[strtolower($row['tipo'])] = (int)$row['conteggio'];
}

// Calcola totale
$totale = array_sum($prenotazioni);

// Ruolo utente per sapere il limite
$stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->bind_param("i", $user_id);
$stmt_role->execute();
$role_result = $stmt_role->get_result()->fetch_assoc();
$ruolo = strtolower(trim($role_result['role'] ?? 'dipendente'));

$limiti_ruolo = [
    'amministratore' => 3,
    'coordinatore' => 2,
    'dipendente' => 1
];
$limite_max = isset($limiti_ruolo[$ruolo]) ? $limiti_ruolo[$ruolo] : 1;
$limite_raggiunto = ($totale >= $limite_max);

header('Content-Type: application/json');
echo json_encode([
    'totale' => $totale,
    'limite_max' => $limite_max,
    'limite_raggiunto' => $limite_raggiunto,
    'prenotazioni' => $prenotazioni,
    'is_weekend' => false
]);
$stmt->close();
$stmt_role->close();
$conn->close();
?>
