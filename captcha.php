<?php
session_start();

// Configurazione
$bgImage = 'src/CaptchaBg.png'; // La tua immagine di sfondo (deve essere ca. 240x140 o più grande)
$pieceWidth = 50;
$pieceHeight = 50;
$yPosition = 45; // Altezza fissa verticale del puzzle

// Genera coordinate random se non esistono
if (!isset($_SESSION['puzzle_x'])) {
    // Il pezzo può andare da X=50 a X=200 (basato sulla larghezza del tuo container)
    $_SESSION['puzzle_x'] = random_int(50, 190); 
}
$targetX = $_SESSION['puzzle_x'];

// Determina cosa generare: 'bg' (sfondo con buco) o 'piece' (tassello)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'bg';

// Carica immagine
$im = @imagecreatefrompng($bgImage);
if (!$im) {
    // Fallback se l'immagine non esiste: crea un gradiente blu
    $im = imagecreatetruecolor(240, 140);
    $blue = imagecolorallocate($im, 48, 169, 255);
    imagefill($im, 0, 0, $blue);
}

if ($mode === 'piece') {
    // --- MODO PEZZO ---
    // Crea una nuova immagine piccola per il pezzo
    $piece = imagecreatetruecolor($pieceWidth, $pieceHeight);
    
    // Copia la porzione dall'immagine originale alle coordinate target
    imagecopy($piece, $im, 0, 0, $targetX, $yPosition, $pieceWidth, $pieceHeight);
    
    // Output
    header('Content-Type: image/png');
    imagepng($piece);
    imagedestroy($piece);

} else {
    // --- MODO SFONDO ---
    // Disegna il "buco" scuro sull'immagine principale
    // Usiamo il nero con opacità (alpha) per simulare l'ombra
    $black = imagecolorallocatealpha($im, 0, 0, 0, 60); // 60 = semi-trasparente
    
    // Disegna un rettangolo scuro dove deve andare il pezzo
    // Nota: Usiamo un rettangolo qui. Nel CSS applicheremo la clip-path al pezzo.
    // Per un effetto perfetto, servirebbe un'immagine "maschera" PNG, ma questo è un buon compromesso.
    imagefilledrectangle($im, $targetX, $yPosition, $targetX + $pieceWidth, $yPosition + $pieceHeight, $black);
    
    // Output
    header('Content-Type: image/png');
    imagepng($im);
}

imagedestroy($im);
?>
