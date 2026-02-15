<?php
session_start();
header('Content-Type: application/json');

// Leggiamo i dati inviati dal frontend (JavaScript)
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['x']) && isset($_SESSION['captcha_target_x'])) {
    $user_x = (int)$data['x'];
    $target_x = (int)$_SESSION['captcha_target_x'];
    
    // Tolleranza di 6 pixel per non frustrare l'utente se non è millimetrico
    if (abs($user_x - $target_x) <= 6) {
        $_SESSION['captcha_verified'] = true;
        echo json_encode(['success' => true, 'message' => 'Verificato']);
        exit;
    }
}

// Se non coincide o mancano i dati
$_SESSION['captcha_verified'] = false;
echo json_encode(['success' => false, 'message' => 'Errore posizione']);
?>