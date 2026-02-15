<?php
session_start();
header('Content-Type: application/json');

// Generiamo una posizione X casuale per il buco (tra 50 e 200 pixel)
$target_x = rand(50, 200);

// Salviamo la posizione nella sessione del server (NON manipolabile dall'utente)
$_SESSION['captcha_target_x'] = $target_x;

// Diciamo al frontend dove disegnare il "buco"
echo json_encode([
    'status' => 'success',
    'target' => $target_x
]);
?>