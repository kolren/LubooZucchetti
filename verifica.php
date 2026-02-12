<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userX = isset($input['x']) ? floatval($input['x']) : 0;
    
    if (!isset($_SESSION['puzzle_x'])) {
        echo json_encode(['success' => false, 'message' => 'Session expired']);
        exit;
    }

    $targetX = $_SESSION['puzzle_x'];
    $tolerance = 6; // Tolleranza di 6 pixel

    // La logica matematica:
    // L'utente muove uno slider. Il JS converte % in Pixel.
    // L'offset iniziale del pezzo nel CSS è "left: 10px". 
    // Quindi la posizione reale è UserMovement + 10px.
    // Ma nel codice JS sotto invieremo già il calcolo corretto.

    if (abs($userX - $targetX) <= $tolerance) {
        $_SESSION['captcha_verified'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
