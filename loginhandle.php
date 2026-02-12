<?php
session_start();

// --- 1. CONFIGURAZIONE DATABASE ---
$host = 'localhost';
$db   = 'luboo_zucchetti5ib';
$user = 'root'; 
$pass = '';     
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Errore Database: " . $e->getMessage());
}

// --- 2. LOGICA LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $captcha_verified = $_POST['captcha_verified'];

    // Controllo Captcha
    if ($captcha_verified != "1") {
        $_SESSION['error'] = "Devi completare il puzzle captcha.";
        header("Location: front-page.php");
        exit();
    }

    // Controllo Formato Username (Prefisso)
    $prefix = substr($username, 0, 3);
    $role_map = ['ad.' => 'admin', 'co.' => 'coordinator', 'dp.' => 'employee'];

    if (!array_key_exists($prefix, $role_map)) {
        $_SESSION['error'] = "Username non valido (usa ad., co. o dp.)";
        header("Location: front-page.php");
        exit();
    }

    $expected_role = $role_map[$prefix];

    // Cerca utente nel DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $userFound = $stmt->fetch();

    // Verifiche Credenziali
    if (!$userFound || $password !== $userFound['password']) {
        $_SESSION['error'] = "Credenziali non valide."; // Messaggio generico per sicurezza
        header("Location: front-page.php");
        exit();
    }

    // Verifica Ruolo
    if ($userFound['role'] !== $expected_role) {
         $_SESSION['error'] = "Ruolo non corretto per questo account.";
         header("Location: front-page.php");
         exit();
    }

    // --- LOGIN EFFETTUATO CON SUCCESSO ---
    $_SESSION['user_id'] = $userFound['id'];
    $_SESSION['username'] = $userFound['username'];
    $_SESSION['role'] = $userFound['role'];

    // --- LOGICA SALUTO DINAMICO (M/F) ---
    // Recuperiamo sesso, nome e cognome dal database
    $sesso = $userFound['sesso']; // 'M' o 'F'
    $nomeReale = $userFound['nome'];
    $cognomeReale = $userFound['cognome'];

    // Determina se scrivere Benvenuto o Benvenuta
    // Se è 'F' usa Benvenuta, altrimenti (M o altro) usa Benvenuto
    $saluto = ($sesso === 'F') ? 'Benvenuta' : 'Benvenuto';
    
    // Costruiamo la frase finale: es. "Benvenuta Valentina Malatesta"
    $messaggioBenvenuto = "$saluto $nomeReale $cognomeReale";


    // --- SETUP ANIMAZIONE ---
    $lottieFile = '';
    $roleTitle = '';
    switch ($userFound['role']) {
        case 'admin':
            $lottieFile = 'Post-LOGINAdmin.json';
            $roleTitle = 'Admin';
            break;
        case 'coordinator':
            $lottieFile = 'Post-LOGINCoordinatore.json';
            $roleTitle = 'Coordinatore';
            break;
        default:
            $lottieFile = 'Post-LOGINDipendente.json';
            $roleTitle = 'Dipendente';
            break;
    }
    ?>
    
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accesso...</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        
        <style>
            @font-face { 
                font-family: 'SF Pro Rounded'; 
                src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2'); 
                font-weight: normal; 
            }
            body {
                font-family: 'SF Pro Rounded', 'Nunito', sans-serif;
                background: linear-gradient(90deg, #30A9FF 0%, #14364F 60%, #0B0F15 100%);
                height: 100vh; 
                width: 100vw; 
                display: flex; 
                flex-direction: column;
                align-items: center; 
                justify-content: center; 
                overflow: hidden; 
                color: white;
            }
            .glass-loader {
                background: rgba(255, 255, 255, 0.05); 
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1); 
                border-radius: 24px; 
                padding: 40px;
                display: flex; 
                flex-direction: column; 
                align-items: center;
                text-align: center; /* Centra il testo lungo */
                animation: fadeIn 0.8s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
        
        <meta http-equiv="refresh" content="4;url=dashboard.php">
    </head>
    <body>
        <div class="glass-loader">
            <h2 class="text-3xl font-bold mb-2">
                <?php echo htmlspecialchars($messaggioBenvenuto); ?>
            </h2>
            
            <p class="text-blue-200 text-sm uppercase tracking-widest mb-6">
                Caricamento Dashboard <?php echo $roleTitle; ?>
            </p>
            
            <div class="w-64 h-64 md:w-80 md:h-80">
                <lottie-player 
                    src="<?php echo $lottieFile; ?>" 
                    background="transparent" 
                    speed="1" 
                    style="width: 100%; height: 100%;" 
                    loop 
                    autoplay>
                </lottie-player>
            </div>
            
            <p class="mt-4 text-xs text-white/40 animate-pulse">Attendere prego...</p>
        </div>

        <script>
            setTimeout(function() { 
                window.location.href = 'dashboard.php'; 
            }, 4000);
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>