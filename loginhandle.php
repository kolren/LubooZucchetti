<?php
session_start();
require_once 'db.php';

// 1. VALIDAZIONE CAPTCHA SERVER-SIDE
if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
    header("Location: front-page.php?error=" . urlencode("Verifica Captcha fallita!"));
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 2. QUERY AL DATABASE (Modificato "nome" in "nome" come da te indicato)
$stmt = $conn->prepare("SELECT id, nome, cognome, role, codice_identificativo FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 3. CREAZIONE SESSIONE PERFETTA
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    
    // Assegno la colonna 'nome' alla variabile 'nome' per la dashboard
    $_SESSION['nome'] = $user['nome']; 
    $_SESSION['ruolo'] = $user['role']; 
    
    // Variabili accessorie
    $_SESSION['user_nome'] = $user['nome'];
    $_SESSION['user_cognome'] = $user['cognome'];
    $_SESSION['user_ruolo'] = $user['role']; 
    $_SESSION['user_codice'] = $user['codice_identificativo'];
    
    $_SESSION['captcha_verified'] = false; // Reset Sicurezza

    // 1. Recupero il ruolo reale dal database
    $ruolo_db = strtolower(trim($user['role']));

    // 2. Imposto i valori di DEFAULT (Dipendente)
    $lottieUrl = "src/Post-LOGINDipendente.json";
    $bgGradient = "bg-gradient-to-br from-[#071B2B] via-[#0E2F47] to-[#2E6F9E]";
    $rolenome = "Dipendente";

    // 3. Controllo se il ruolo è diverso e aggiorno i valori
    if ($ruolo_db === 'coordinatore') {
        $lottieUrl = "src/Post-LOGINCoordinatore.json"; 
        $rolenome = "Coordinatore";
        // Se vuoi un gradiente diverso per il coordinatore, aggiungilo qui
    } elseif ($ruolo_db === 'amministratore' || $ruolo_db === 'admin') {
        $lottieUrl = "src/Post-LOGINAdmin.json";
        $rolenome = "Amministratore";
        // Se vuoi un gradiente diverso per l'admin, aggiungilo qui
    }
    // 5. RENDER DELLA PAGINA DI CARICAMENTO
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <meta http-equiv="refresh" content="2.5;url=dashboard.php">
        <title>Accesso in corso...</title>
        <style>
            @font-face { font-family: 'SF Pro Rounded'; src: local('SF Pro Rounded'); }
            body { font-family: 'SF Pro Rounded', sans-serif; }

        </style>
    </head>
    <body class="bg-gradient-to-br <?= $bgGradient ?> h-screen flex flex-col items-center justify-center overflow-hidden m-0">
        <lottie-player src="<?= htmlspecialchars($lottieUrl) ?>" background="transparent" speed="1" class="w-[600px] h-[600px]" loop autoplay></lottie-player>
    </body>
    </html>
    <?php
    exit();
} else {
    // Credenziali errate
    header("Location: front-page.php?error=" . urlencode("Credenziali non valide"));
    exit();
}
?>