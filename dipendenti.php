<?php
session_start();
require_once 'db.php';
// GESTIONE CREAZIONE NUOVO UTENTE (Solo Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crea_utente']) && $ruoloUtente === 'amministratore') {
    $nuovo_nome = trim($_POST['nuovo_nome']);
    $nuovo_cognome = trim($_POST['nuovo_cognome']);
    $nuovo_ruolo = trim($_POST['nuovo_ruolo']);
    $nuovo_team = intval($_POST['nuovo_team']);
    $nuova_password = password_hash($_POST['nuova_password'], PASSWORD_DEFAULT);
    
    // Generazione codice identificativo basato su nome e cognome
    $codice_id = strtoupper(substr($nuovo_nome, 0, 1) . substr($nuovo_cognome, 0, 3)) . rand(100, 999);

    $stmt_insert = $conn->prepare("INSERT INTO users (nome, cognome, role, team_id, password, codice_identificativo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sssiss", $nuovo_nome, $nuovo_cognome, $nuovo_ruolo, $nuovo_team, $nuova_password, $codice_id);
    
    if ($stmt_insert->execute()) {
        header("Location: dipendenti.php?msg=utente_creato");
        exit();
    } else {
        header("Location: dipendenti.php?err=creazione_fallita");
        exit();
    }
}

// Recupera i team disponibili per la select
$team_list = [];
if ($ruoloUtente === 'amministratore') {
    $res_team = $conn->query("SELECT id, nome_team FROM team ORDER BY nome_team ASC");
    while($t = $res_team->fetch_assoc()) {
        $team_list[] = $t;
    }
}
// 1. Controllo Sessione e Sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

$logged_in_user_id = intval($_SESSION['user_id']);

// Recupero Ruolo e Dati dell'utente loggato
$stmt_me = $conn->prepare("SELECT role, team_id, nome FROM users WHERE id = ?");
$stmt_me->bind_param("i", $logged_in_user_id);
$stmt_me->execute();
$me_data = $stmt_me->get_result()->fetch_assoc();

$ruoloUtente = strtolower(trim(isset($me_data['role']) ? $me_data['role'] : 'dipendente'));
$nomeUtente = isset($me_data['nome']) ? $me_data['nome'] : 'Utente';
$my_team_id = $me_data['team_id'];

// Se un dipendente prova ad accedere forzando l'URL, lo blocchiamo
if ($ruoloUtente === 'dipendente') {
    header("Location: dashboard.php");
    exit();
}

// 2. Logica di Visualizzazione (Toggle Dipendenti/Coordinatori per Admin)
$view = isset($_GET['view']) && $_GET['view'] === 'coordinatori' ? 'coordinatori' : 'dipendenti';

// I Coordinatori non possono vedere il tab "coordinatori"
if ($ruoloUtente === 'coordinatore') {
    $view = 'dipendenti'; 
}

// 3. Query Ottimizzata per estrarre la lista del personale
$utenti_lista = [];
if ($ruoloUtente === 'amministratore') {
    if ($view === 'coordinatori') {
        $sql = "SELECT u.id, u.nome, u.cognome, u.codice_identificativo, t.nome_team 
                FROM users u LEFT JOIN team t ON u.team_id = t.id 
                WHERE u.role = 'coordinatore' ORDER BY u.nome ASC";
        $stmt_lista = $conn->prepare($sql);
    } else {
        // Estrae Dipendenti + Cerca il nome del loro rispettivo coordinatore tramite subquery
        $sql = "SELECT u.id, u.nome, u.cognome, u.codice_identificativo, t.nome_team,
                (SELECT CONCAT(nome, ' ', cognome) FROM users WHERE role='coordinatore' AND team_id = u.team_id LIMIT 1) as nome_coord
                FROM users u LEFT JOIN team t ON u.team_id = t.id 
                WHERE u.role = 'dipendente' ORDER BY u.nome ASC";
        $stmt_lista = $conn->prepare($sql);
    }
} else {
    // Il Coordinatore vede SOLO i dipendenti del suo team
    $sql = "SELECT u.id, u.nome, u.cognome, u.codice_identificativo, t.nome_team 
            FROM users u LEFT JOIN team t ON u.team_id = t.id 
            WHERE u.role = 'dipendente' AND u.team_id = ? ORDER BY u.nome ASC";
    $stmt_lista = $conn->prepare($sql);
    $stmt_lista->bind_param("i", $my_team_id);
}

$stmt_lista->execute();
$res_lista = $stmt_lista->get_result();
while ($row = $res_lista->fetch_assoc()) {
    $utenti_lista[] = $row;
}

// 4. Statistiche Attività (Destra) - Calcolo prenotazioni ultima settimana
$attivita = [];
$sql_act = "SELECT u.nome, u.cognome, COUNT(p.id) as tot_prenotazioni
            FROM users u
            LEFT JOIN prenotazioni p ON u.id = p.user_id AND p.data_prenotazione >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            WHERE " . ($ruoloUtente === 'coordinatore' ? "u.team_id = $my_team_id AND " : "") . " u.role = ?
            GROUP BY u.id ORDER BY tot_prenotazioni DESC LIMIT 6";
            
$ruolo_cercato = ($view === 'coordinatori') ? 'coordinatore' : 'dipendente';
$stmt_act = $conn->prepare($sql_act);
$stmt_act->bind_param("s", $ruolo_cercato);
$stmt_act->execute();
$res_act = $stmt_act->get_result();
while ($r = $res_act->fetch_assoc()) {
    $attivita[] = $r;
}

// Massimo valore per scalare la progress bar in %
$max_act = !empty($attivita) ? max(array_column($attivita, 'tot_prenotazioni')) : 1;
if ($max_act == 0) $max_act = 1;

// Configurazione Tema Navbar
$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]']
];
$roleTheme = $themeColors[$ruoloUtente];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Personale - LubooZucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1400px] flex flex-col gap-6 relative">
        
        <header class="relative z-50">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    
                    <div class="<?php echo $roleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md self-start mb-0.5 shadow-sm" 
                            style="background-color: <?php echo $roleTheme['badge_bg']; ?>; color: <?php echo $roleTheme['badge_text']; ?>;">
                            <?php echo htmlspecialchars($ruoloUtente); ?>
                        </span>
                        <span class="font-bold text-lg leading-none drop-shadow-md text-white mt-1">
                            Ciao <?php echo htmlspecialchars($nomeUtente); ?>!
                        </span>
                    </div>

                </div>

                <nav class="flex items-center gap-2 bg-[#0A2338]/40 p-1.5 rounded-[20px] border border-white/10 overflow-x-auto custom-scrollbar">
                    <a href="dipendenti.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">Dipendenti</a>
                    <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">DashBoard</a>
                    <a href="gestisci.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Gestisci</a>
                        <?php if ($ruoloUtente === 'amministratore'): ?>
                            <button onclick="document.getElementById('modalNuovoUtente').classList.remove('hidden')" class="bg-[#36A482] text-white px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">
                                + Nuovo Dipendente
                            </button>
                        <?php endif; ?>                
                </nav>

                <div class="hidden md:flex items-center gap-3 text-[#BFD6E8] text-xs font-semibold mr-2">
                    <a href="gestisci.php" class="hover:text-white transition-colors uppercase">Modifica</a>
                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                    <a href="loginhandle.php?action=logout" class="hover:text-white transition-colors uppercase">Cambia utente</a>
                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                    <a href="loginhandle.php?action=logout" class="text-[#FF8A8A] hover:text-[#FFB3B3] transition-colors uppercase">Esci</a>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch min-h-[70vh]">
            
            <div class="lg:col-span-8 ui-panel glass-panel p-8 rounded-[24px] shadow-2xl flex flex-col relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 z-10 gap-4">
                    <h2 class="text-3xl font-black text-white uppercase tracking-wider drop-shadow-md">
                        <?php echo $ruoloUtente === 'coordinatore' ? 'Il Mio Team' : 'Gestione Personale'; ?>
                    </h2>

                    <?php if ($ruoloUtente === 'amministratore'): ?>
                    <div class="bg-[#0A2338]/60 p-1.5 rounded-xl border border-white/10 flex items-center shadow-inner">
                        <a href="?view=dipendenti" class="px-6 py-2 rounded-lg text-sm font-bold transition-all <?php echo $view === 'dipendenti' ? 'bg-white/10 text-[#00f2ff] shadow-sm' : 'text-white/50 hover:text-white'; ?>">Dipendenti</a>
                        <a href="?view=coordinatori" class="px-6 py-2 rounded-lg text-sm font-bold transition-all <?php echo $view === 'coordinatori' ? 'bg-white/10 text-[#00f2ff] shadow-sm' : 'text-white/50 hover:text-white'; ?>">Coordinatori</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 z-10 overflow-y-auto custom-scrollbar pr-2 h-full content-start">
                    <?php if (empty($utenti_lista)): ?>
                        <div class="col-span-full text-center py-10 text-white/50 font-medium">Nessun utente trovato in questa sezione.</div>
                    <?php endif; ?>

                    <?php foreach ($utenti_lista as $u): 
                        // Gradiente differenziato in base alla view
                        $bg_grad = $view === 'dipendenti' 
                            ? 'bg-gradient-to-br from-[#6d6d15] to-[#8a8a1c]' 
                            : 'bg-gradient-to-br from-[#3f3b8c] to-[#2d2a63]';
                        $label_coord = isset($u['nome_coord']) ? $u['nome_coord'] : 'Non Assegnato';
                    ?>
                    <div class="<?php echo $bg_grad; ?> rounded-[20px] p-6 shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[160px] border border-white/10 group transition-transform hover:-translate-y-1">
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl group-hover:bg-white/20 transition-all pointer-events-none"></div>
                        
                        <div>
                            <span class="text-[10px] text-white/70 uppercase tracking-widest block mb-0.5">Nome e Cognome</span>
                            <h3 class="text-xl font-black text-white leading-tight truncate"><?php echo htmlspecialchars($u['nome'] . ' ' . $u['cognome']); ?></h3>
                        </div>

                        <div class="flex items-end justify-between mt-6 relative z-10">
                            <div class="flex flex-col">
                                <?php if ($view === 'dipendenti' && $ruoloUtente === 'amministratore'): ?>
                                    <span class="text-[9px] text-white/60 uppercase tracking-widest">Coordinatore Ref.</span>
                                    <span class="text-xs font-bold text-white/90 truncate max-w-[120px]"><?php echo htmlspecialchars($label_coord); ?></span>
                                <?php else: ?>
                                    <span class="text-[9px] text-white/60 uppercase tracking-widest">Team</span>
                                    <span class="text-xs font-bold text-white/90 truncate max-w-[120px]"><?php echo htmlspecialchars(isset($u['nome_team']) ? $u['nome_team'] : 'Nessun Team'); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="gestisci.php?id=<?php echo $u['id']; ?>" class="bg-gradient-to-r from-[#00f2ff] to-[#00b4d8] text-[#0A2338] font-black px-6 py-2 rounded-full text-xs uppercase tracking-wider hover:brightness-110 transition-all shadow-md">
                                Gestisci
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-4 bg-[#0B2136] glass-panel p-8 rounded-[24px] shadow-2xl flex flex-col border border-white/5 relative overflow-hidden">
                <div class="flex items-center justify-between mb-8 z-10">
                    <h2 class="text-xl font-bold text-white tracking-wide">Attività Utenti</h2>
                    <div class="bg-white/5 px-3 py-1 rounded-md text-[10px] font-bold text-[#00f2ff] uppercase border border-white/10">Settimana</div>
                </div>

                <div class="flex-grow flex flex-col gap-6 z-10">
                    <?php if (empty($attivita)): ?>
                        <div class="text-center py-10 text-white/50 text-sm">Nessuna attività registrata.</div>
                    <?php endif; ?>

                    <?php foreach ($attivita as $act): 
                        $perc = ($act['tot_prenotazioni'] / $max_act) * 100;
                    ?>
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-16 text-xs font-bold text-white/90 truncate text-right">
                            <?php echo htmlspecialchars($act['nome']); ?>
                        </div>
                        <div class="flex-grow h-7 bg-[#05111D] rounded-full overflow-hidden border border-white/5 p-1 relative shadow-inner">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#00b4d8] to-[#00f2ff] flex items-center justify-end pr-3 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(0,242,255,0.4)]" 
                                 style="width: <?php echo max(15, $perc); ?>%;">
                                <span class="text-[#0A2338] text-[10px] font-black"><?php echo $act['tot_prenotazioni']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 pt-6 border-t border-white/10 z-10 text-center">
                    <p class="text-[10px] text-white/50 uppercase tracking-widest">Basato sulle prenotazioni (Ultimi 7 gg)</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>