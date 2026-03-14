<?php
session_start();
require_once 'db.php';

// Controllo Login
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

$logged_in_user_id = $_SESSION['user_id'];

// 1. RECUPERO IL RUOLO REALE DELL'UTENTE LOGGATO PER I PERMESSI
$stmt_me = $conn->prepare("SELECT role, team_id FROM users WHERE id = ?");
$stmt_me->bind_param("i", $logged_in_user_id);
$stmt_me->execute();
$me_data = $stmt_me->get_result()->fetch_assoc();
$logged_in_role = strtolower(trim(isset($me_data['role']) ? $me_data['role'] : 'dipendente'));
$logged_in_team_id = isset($me_data['team_id']) ? $me_data['team_id'] : null;

// Determino quale utente stiamo visualizzando (se stesso o un altro se permesso)
$target_user_id = isset($_GET['id']) ? intval($_GET['id']) : $logged_in_user_id;

// Controllo Permessi: un Dipendente non può visualizzare la pagina di un altro utente
if ($target_user_id !== $logged_in_user_id && !in_array($logged_in_role, ['amministratore', 'coordinatore'])) {
    header("Location: gestisci.php");
    exit();
}

// 2. GESTIONE AZIONI SUL PROFILO UTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $n_nome = trim($_POST['nome']);
    $n_cognome = trim($_POST['cognome']);
    $n_data_nascita = trim($_POST['data_nascita']);

    // Se admin, aggiorno anche ruolo e team
    if ($logged_in_role === 'amministratore') {
        $n_ruolo = isset($_POST['ruolo']) ? trim($_POST['ruolo']) : '';
        $n_team = empty($_POST['team_id']) ? NULL : intval($_POST['team_id']);
        $stmt_up = $conn->prepare("UPDATE users SET nome=?, cognome=?, data_nascita=?, role=?, team_id=? WHERE id=?");
        $stmt_up->bind_param("ssssii", $n_nome, $n_cognome, $n_data_nascita, $n_ruolo, $n_team, $target_user_id);
    } else {
        $stmt_up = $conn->prepare("UPDATE users SET nome=?, cognome=?, data_nascita=? WHERE id=?");
        $stmt_up->bind_param("sssi", $n_nome, $n_cognome, $n_data_nascita, $target_user_id);
    }

    if ($stmt_up->execute()) {
        if ($target_user_id === $logged_in_user_id) $_SESSION['user_nome'] = $n_nome;
        
        // Salva il Log dell'Azione per l'amministratore
        if ($logged_in_role === 'amministratore') {
            $stmt_log = $conn->prepare("INSERT INTO logs (user_id, azione, dettagli) VALUES (?, 'Modifica Profilo', 'Modificato utente ID: $target_user_id')");
            $stmt_log->bind_param("i", $logged_in_user_id);
            $stmt_log->execute();
        }

        header("Location: gestisci.php?id=" . $target_user_id . "&success=1");
        exit();
    }
}
// 3. GESTIONE AZIONI SULLE PRENOTAZIONI (ELIMINA E MODIFICA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $pren_id = intval($_POST['prenotazione_id']);

    if ($action === 'delete_prenotazione') {
        $stmt_del_p = $conn->prepare("DELETE FROM prenotazioni WHERE id = ? AND user_id = ?");
        $stmt_del_p->bind_param("ii", $pren_id, $target_user_id);
        $stmt_del_p->execute();
        header("Location: gestisci.php?id=" . $target_user_id . "&msg=pren_deleted");
        exit();
    }

    if ($action === 'edit_prenotazione') {
        $n_data = $_POST['nuova_data'];
        $n_inizio = $_POST['nuovo_inizio'];
        $n_fine = $_POST['nuova_fine'];

        if (strtotime($n_inizio) >= strtotime($n_fine)) {
            header("Location: gestisci.php?id=" . $target_user_id . "&err=time");
            exit();
        }

        // Recupera l'asset associato per controllare sovrapposizioni
        $stmt_get = $conn->prepare("SELECT asset_id FROM prenotazioni WHERE id = ? AND user_id = ?");
        $stmt_get->bind_param("ii", $pren_id, $target_user_id);
        $stmt_get->execute();
        $res = $stmt_get->get_result();
        
        if ($res->num_rows > 0) {
            $asset_id = $res->fetch_assoc()['asset_id'];
            
            // Controlla che il nuovo orario non si sovrapponga con altre prenotazioni (escludendo se stessa)
            $stmt_check = $conn->prepare("
                SELECT id FROM prenotazioni 
                WHERE asset_id = ? AND data_prenotazione = ? AND stato != 'annullata' AND id != ?
                AND (ora_inizio < ? AND ora_fine > ?)
            ");
            $stmt_check->bind_param("isiss", $asset_id, $n_data, $pren_id, $n_fine, $n_inizio);
            $stmt_check->execute();
            
            if ($stmt_check->get_result()->num_rows > 0) {
                header("Location: gestisci.php?id=" . $target_user_id . "&err=overlap");
                exit();
            } else {
                $stmt_upd_p = $conn->prepare("UPDATE prenotazioni SET data_prenotazione=?, ora_inizio=?, ora_fine=? WHERE id=?");
                $stmt_upd_p->bind_param("sssi", $n_data, $n_inizio, $n_fine, $pren_id);
                $stmt_upd_p->execute();
                header("Location: gestisci.php?id=" . $target_user_id . "&msg=pren_updated");
                exit();
            }
        }
    }
}

// 3.5 GESTIONE ELIMINAZIONE UTENTE (Admin o Coordinatore per dipendenti del suo team)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $can_delete = false;
    
    if ($logged_in_role === 'amministratore') {
        $can_delete = true;
    } elseif ($logged_in_role === 'coordinatore' && $target_role === 'dipendente' && $user_data['team_id'] == $logged_in_team_id) {
        $can_delete = true;
    }
    
    if (!$can_delete) {
        header("Location: gestisci.php?id=" . $target_user_id . "&err=no_permission");
        exit();
    }
    
    // Non permettere di eliminare se stesso
    if ($target_user_id === $logged_in_user_id) {
        header("Location: gestisci.php?id=" . $target_user_id . "&err=self_delete");
        exit();
    }

    // Elimina prima le prenotazioni dell'utente
    $stmt_del_pren = $conn->prepare("DELETE FROM prenotazioni WHERE user_id = ?");
    $stmt_del_pren->bind_param("i", $target_user_id);
    $stmt_del_pren->execute();

    // Elimina i log dell'utente
    $stmt_del_logs = $conn->prepare("DELETE FROM logs WHERE user_id = ?");
    $stmt_del_logs->bind_param("i", $target_user_id);
    $stmt_del_logs->execute();

    // Elimina l'utente
    $stmt_del_user = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt_del_user->bind_param("i", $target_user_id);
    if ($stmt_del_user->execute()) {
        // Log dell'azione
        $stmt_log = $conn->prepare("INSERT INTO logs (user_id, azione, dettagli) VALUES (?, 'Eliminazione Utente', 'Eliminato utente ID: $target_user_id')");
        $stmt_log->bind_param("i", $logged_in_user_id);
        $stmt_log->execute();

        header("Location: dipendenti.php?msg=user_deleted");
        exit();
    } else {
        header("Location: gestisci.php?id=" . $target_user_id . "&err=delete_failed");
        exit();
    }
}

// 4. RECUPERO DATI DELL'UTENTE VISUALIZZATO
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $target_user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

if (!$user_data) {
    header("Location: dashboard.php");
    exit();
}

$nomeUtente = isset($user_data['nome']) ? $user_data['nome'] : '';
$cognomeUtente = isset($user_data['cognome']) ? $user_data['cognome'] : ''; 
$target_role = strtolower(trim(isset($user_data['role']) ? $user_data['role'] : 'dipendente'));
$aziendaUtente = 'LubooZucchetti'; 
$dataNascita = isset($user_data['data_nascita']) ? $user_data['data_nascita'] : '';

$codiceIdentificativo = 'N/A';
if (isset($user_data['codice_identificativo']) && !empty($user_data['codice_identificativo'])) {
    $codiceIdentificativo = $user_data['codice_identificativo'];
} else {
    $codiceIdentificativo = 'LZ-' . $user_data['id'];
}

$eta = '-';
if (!empty($dataNascita)) {
    $birthDate = new DateTime($dataNascita);
    $today = new DateTime('today');
    $eta = $birthDate->diff($today)->y . ' anni';
}

$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]'],   
    'dipendente' => ['badge_bg' => '#6aa70f', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#4D7C0F_0%,#6AA70F_100%)]']      
];
$roleTheme = array_key_exists($target_role, $themeColors) ? $themeColors[$target_role] : $themeColors['dipendente'];
$myRoleTheme = array_key_exists($logged_in_role, $themeColors) ? $themeColors[$logged_in_role] : $themeColors['dipendente'];

// 5. RECUPERO PRENOTAZIONI
$stmt_prenotazioni = $conn->prepare("
    SELECT p.id, p.data_prenotazione, p.ora_inizio, p.ora_fine, p.stato, a.nome AS asset_nome, a.tipo AS asset_tipo
    FROM prenotazioni p
    JOIN asset a ON p.asset_id = a.id
    WHERE p.user_id = ? AND p.stato != 'annullata'
    ORDER BY p.data_prenotazione DESC, p.ora_inizio DESC
");
$stmt_prenotazioni->bind_param("i", $target_user_id);
$stmt_prenotazioni->execute();
$result_prenotazioni = $stmt_prenotazioni->get_result();

$tutte_prenotazioni = [];
$in_arrivo = [];
$completate = [];
$now = new DateTime(); 

while ($row = $result_prenotazioni->fetch_assoc()) {
    $tutte_prenotazioni[] = $row;
    $prenotazione_datetime = new DateTime($row['data_prenotazione'] . ' ' . $row['ora_fine']);
    
    if ($prenotazione_datetime > $now) {
        $in_arrivo[] = $row;
    } else {
        $completate[] = $row;
    }
}

function getAssetIcon($tipo) {
    $path = "src/Icone/";
    $icons = ['base' => 'PostazioneBase.svg', 'tech' => 'PostazioneTech.svg', 'meeting' => 'Riunioni.svg', 'parking' => 'PostoAuto.svg'];
    $filename = $path . (isset($icons[$tipo]) ? $icons[$tipo] : 'PostazioneBase.svg');
    return file_exists($filename) ? file_get_contents($filename) : '<div class="text-white/50 text-xs">N/A</div>';
}

function renderPrenotazioneCard($p) {
    $icon = getAssetIcon($p['asset_tipo']);
    $data_formattata = date('d/m/Y', strtotime($p['data_prenotazione']));
    $inizio_formattato = date('H:i', strtotime($p['ora_inizio']));
    $fine_formattato = date('H:i', strtotime($p['ora_fine']));
    
    // Preparo i dati per passarli al JS del modale
    $asset_nome_js = htmlspecialchars(addslashes($p['asset_nome']));
    
    return '
    <div class="bg-[rgba(255,255,255,0.05)] border border-white/10 rounded-2xl p-4 flex flex-col gap-4 hover:bg-white/10 transition-all shadow-md">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 flex items-center justify-center text-[#36A482] drop-shadow-md bg-white/5 rounded-xl border border-white/5">
                ' . $icon . '
            </div>
            <div class="flex-1">
                <h4 class="text-white font-bold text-sm uppercase tracking-wide">' . htmlspecialchars($p['asset_nome']) . '</h4>
                <div class="text-white/70 text-xs mt-0.5">' . $data_formattata . ' | ' . $inizio_formattato . ' - ' . $fine_formattato . '</div>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-2">
            <button type="button" onclick="apriModalModifica('.$p['id'].', \''.$p['data_prenotazione'].'\', \''.$p['ora_inizio'].'\', \''.$p['ora_fine'].'\', \''.$asset_nome_js.'\')" class="flex-1 bg-green-600/20 text-green-400 border border-green-500/30 hover:bg-green-600/40 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                Modifica
            </button>
            
            <button type="button" onclick="apriModalEliminaPrenotazione('.$p['id'].')" class="flex-1 bg-red-600/20 text-red-400 border border-red-500/30 hover:bg-red-600/40 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                Elimina
            </button>
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestisci - LubooZucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
    <style>
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; filter: invert(1); opacity: 0.6; transition: 0.2s; }
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
    </style>
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF] relative">

    <div class="w-full max-w-[1400px] flex flex-col gap-6 pt-24">
        
        <?php if(isset($_GET['err'])): ?>
            <div class="absolute top-4 left-1/2 -translate-x-1/2 bg-red-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-2xl z-50 animate-bounce">
                <?php 
                if($_GET['err'] == 'overlap') echo "Errore: La postazione è già occupata in questo nuovo orario.";
                elseif($_GET['err'] == 'time') echo "Errore: L'orario di fine deve essere successivo a quello di inizio.";
                elseif($_GET['err'] == 'self_delete') echo "Errore: Non puoi eliminare il tuo stesso account.";
                elseif($_GET['err'] == 'delete_failed') echo "Errore: Impossibile eliminare l'utente. Riprova.";
                elseif($_GET['err'] == 'no_permission') echo "Errore: Non hai i permessi per eliminare questo utente.";
                ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) || isset($_GET['success'])): ?>
            <div class="absolute top-4 left-1/2 -translate-x-1/2 bg-[#36A482] text-white px-6 py-2 rounded-xl text-sm font-bold shadow-2xl z-50">
                <?php 
                if(isset($_GET['success'])) echo "Profilo aggiornato con successo!";
                elseif($_GET['msg'] == 'pren_updated') echo "Prenotazione aggiornata con successo!";
                elseif($_GET['msg'] == 'pren_deleted') echo "Prenotazione eliminata con successo!";
                ?>
            </div>
        <?php endif; ?>

        <header class="fixed top-0 left-0 right-0 z-50">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    
                    <div class="<?php echo $myRoleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md self-start mb-0.5 shadow-sm" 
                            style="background-color: <?php echo $myRoleTheme['badge_bg']; ?>; color: <?php echo $myRoleTheme['badge_text']; ?>;">
                            <?php echo htmlspecialchars($logged_in_role); ?>
                        </span>
                        <span class="font-bold text-lg leading-none drop-shadow-md text-white mt-1">
                            Ciao <?php echo htmlspecialchars(isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Utente'); ?>!
                        </span>
                    </div>                     
                </div>

                <nav class="flex items-center gap-2 bg-[#0A2338]/40 p-1.5 rounded-[20px] border border-white/10 overflow-x-auto custom-scrollbar">
                    <?php if (in_array($logged_in_role, ['amministratore', 'coordinatore'])): ?>
                    <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                    <?php endif; ?>
                    
                    <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">DashBoard</a>
                    
                    <a href="gestisci.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">Gestisci</a>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <div class="lg:col-span-8 ui-panel glass-panel p-6 shadow-2xl rounded-[24px] flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

                <form method="POST" action="gestisci.php?id=<?php echo $target_user_id; ?>" class="flex flex-col gap-4 z-10 w-full">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2 items-center">
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($nomeUtente); ?>" class="bg-transparent text-3xl font-black text-white uppercase tracking-wider outline-none hover:bg-white/5 focus:bg-white/10 rounded px-2 py-1 transition-colors w-auto max-w-[200px]" placeholder="Nome" required>
                                <input type="text" name="cognome" value="<?php echo htmlspecialchars($cognomeUtente); ?>" class="bg-transparent text-3xl font-black text-white uppercase tracking-wider outline-none hover:bg-white/5 focus:bg-white/10 rounded px-2 py-1 transition-colors w-auto max-w-[200px]" placeholder="Cognome" required>
                            </div>
                            <input type="text" value="<?php echo htmlspecialchars($aziendaUtente); ?>" class="bg-transparent text-[#BFD6E8] text-sm font-bold uppercase tracking-widest mt-1 outline-none rounded px-2 py-1 w-full max-w-md cursor-default pointer-events-none" readonly>
                        </div>
                        
                        <div class="flex flex-col items-end gap-3">
                            <div class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg border border-white/10" 
                                 style="background-color: <?php echo $roleTheme['badge_bg']; ?>; color: <?php echo $roleTheme['badge_text']; ?>;">
                                <?php echo htmlspecialchars($target_role); ?>
                            </div>

                            <?php if ($logged_in_role === 'amministratore'): ?>
                                <?php $res_team = $conn->query("SELECT id, nome_team FROM team ORDER BY nome_team ASC"); ?>
                                <select name="ruolo" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[10px] font-bold text-[#BFD6E8] focus:outline-none focus:border-[#4FA8C7] appearance-none cursor-pointer text-right hover:bg-white/10 transition-colors shadow-sm uppercase tracking-widest w-full max-w-[160px]">
                                    <option value="dipendente" <?php echo $target_role == 'dipendente' ? 'selected' : ''; ?> class="text-black">Dipendente</option>
                                    <option value="coordinatore" <?php echo $target_role == 'coordinatore' ? 'selected' : ''; ?> class="text-black">Coordinatore</option>
                                    <option value="amministratore" <?php echo $target_role == 'amministratore' ? 'selected' : ''; ?> class="text-black">Amministratore</option>
                                </select>
                                
                                <select name="team_id" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-[10px] font-bold text-[#BFD6E8] focus:outline-none focus:border-[#4FA8C7] appearance-none cursor-pointer text-right hover:bg-white/10 transition-colors shadow-sm uppercase tracking-widest w-full max-w-[160px]">
                                    <option value="" class="text-black">Nessun Team</option>
                                    <?php while($t = $res_team->fetch_assoc()): ?>
                                        <option value="<?php echo $t['id']; ?>" <?php echo (isset($user_data['team_id']) && $user_data['team_id'] == $t['id']) ? 'selected' : ''; ?> class="text-black">
                                            Team <?php echo htmlspecialchars($t['nome_team']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            <?php endif; ?>

                            <button type="submit" class="bg-[#36A482] hover:bg-[#2b8569] text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider shadow-lg transition-all border border-[#36A482]/50">
                                Salva Modifiche
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="bg-white/5 p-4 rounded-xl border border-white/10 transition-colors hover:border-white/20 relative">
                            <span class="block text-xs font-bold text-[#BFD6E8] uppercase mb-1 px-2">Data di nascita</span>
                            <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($dataNascita); ?>" class="bg-transparent text-lg font-black text-white outline-none focus:bg-white/5 rounded px-2 py-1 transition-colors w-full cursor-pointer appearance-none" required>
                        </div>
                        <div class="bg-white/5 p-4 rounded-xl border border-white/10 flex flex-col justify-center">
                            <span class="block text-xs font-bold text-[#BFD6E8] uppercase mb-1 px-2">Età</span>
                            <span class="text-lg font-black text-white px-2"><?php echo htmlspecialchars($eta); ?></span>
                        </div>
                    </div>
                </form>

                <?php if (in_array($logged_in_role, ['amministratore', 'coordinatore']) && $target_user_id !== $logged_in_user_id): ?>
                <div class="z-10 mt-4 md:mt-0 flex-shrink-0 self-end">
                    <button type="button" onclick="apriModalEliminaUtente()" class="bg-red-600/80 hover:bg-red-500 text-white px-6 py-3 rounded-xl font-bold uppercase tracking-wider shadow-lg border border-red-400/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Elimina Utente
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-4 bg-bookings glass-panel p-6 shadow-2xl rounded-[24px] flex flex-col justify-center items-center relative overflow-hidden border border-white/10">
                <div class="text-center z-10 w-full">
                    <h3 class="text-sm font-bold text-[#BFD6E8] uppercase tracking-widest mb-4">Codice Identificativo</h3>
                    <div class="bg-[#0A2338] w-full py-6 rounded-2xl border border-white/10 shadow-inner">
                        <span class="text-3xl font-black text-[#36A482] tracking-widest drop-shadow-md">
                            <?php echo htmlspecialchars($codiceIdentificativo); ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch mt-2">
            
            <div class="ui-panel glass-panel p-6 rounded-[24px] shadow-2xl flex flex-col h-[500px]">
                <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider border-b border-white/10 pb-4 text-center">
                    Tutte le prenotazioni (<span class="text-[#36A482]"><?php echo count($tutte_prenotazioni); ?></span>)
                </h3>
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 flex flex-col gap-4">
                    <?php 
                        if (empty($tutte_prenotazioni)) { echo '<div class="text-center text-white/50 text-sm mt-4">Nessuna prenotazione trovata.</div>'; } 
                        else { foreach ($tutte_prenotazioni as $p) echo renderPrenotazioneCard($p); }
                    ?>
                </div>
            </div>

            <div class="ui-panel glass-panel p-6 rounded-[24px] shadow-2xl flex flex-col h-[500px]">
                <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider border-b border-white/10 pb-4 text-center">
                    In arrivo (<span class="text-[#FFA500]"><?php echo count($in_arrivo); ?></span>)
                </h3>
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 flex flex-col gap-4">
                    <?php 
                        if (empty($in_arrivo)) { echo '<div class="text-center text-white/50 text-sm mt-4">Nessuna prenotazione in arrivo.</div>'; } 
                        else { foreach ($in_arrivo as $p) echo renderPrenotazioneCard($p); }
                    ?>
                </div>
            </div>

            <div class="bg-bookings glass-panel p-6 rounded-[24px] shadow-2xl flex flex-col h-[500px] border border-white/5">
                <h3 class="text-lg font-bold text-white mb-6 uppercase tracking-wider border-b border-white/10 pb-4 text-center">
                    Completate (<span class="text-[#BFD6E8]"><?php echo count($completate); ?></span>)
                </h3>
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 flex flex-col gap-4 opacity-80">
                    <?php 
                        if (empty($completate)) { echo '<div class="text-center text-white/50 text-sm mt-4">Nessuna prenotazione passata.</div>'; } 
                        else { foreach ($completate as $p) echo renderPrenotazioneCard($p); }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <div id="modal-modifica" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#071B2B]/80 backdrop-blur-sm transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-modifica')"></div>
        <div class="bg-main border border-white/10 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative modal-content">
            <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-wide">Modifica Prenotazione</h3>
            <p id="modal-asset-nome" class="text-[#36A482] text-sm mb-6 font-bold uppercase tracking-widest border-b border-white/10 pb-4"></p>
            
            <form method="POST" action="gestisci.php?id=<?php echo $target_user_id; ?>" class="flex flex-col gap-5">
                <input type="hidden" name="action" value="edit_prenotazione">
                <input type="hidden" name="prenotazione_id" id="modal-pren-id">
                
                <div class="bg-white/5 p-4 rounded-xl border border-white/10">
                    <label class="block text-xs font-bold text-[#BFD6E8] uppercase mb-2">Nuova Data</label>
                    <input type="date" name="nuova_data" id="modal-data" class="w-full bg-transparent text-lg font-black text-white outline-none cursor-pointer" required>
                </div>
                
                <div class="flex gap-4">
                    <div class="flex-1 bg-white/5 p-4 rounded-xl border border-white/10">
                        <label class="block text-xs font-bold text-[#BFD6E8] uppercase mb-2">Ora Inizio</label>
                        <input type="time" name="nuovo_inizio" id="modal-inizio" class="w-full bg-transparent text-lg font-black text-[#36A482] outline-none cursor-pointer text-center" required>
                    </div>
                    <div class="flex-1 bg-white/5 p-4 rounded-xl border border-white/10">
                        <label class="block text-xs font-bold text-[#BFD6E8] uppercase mb-2">Ora Fine</label>
                        <input type="time" name="nuova_fine" id="modal-fine" class="w-full bg-transparent text-lg font-black text-[#36A482] outline-none cursor-pointer text-center" required>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-4 pt-4 border-t border-white/10">
                    <button type="button" onclick="chiudiModal('modal-modifica')" class="flex-1 py-3.5 rounded-xl border border-white/20 text-white hover:bg-white/10 transition-all font-bold uppercase tracking-wider text-sm">Annulla</button>
                    <button type="submit" class="flex-1 bg-[#36A482] hover:bg-[#2b8569] py-3.5 rounded-xl text-white shadow-lg transition-all font-bold uppercase tracking-wider text-sm border border-[#36A482]/50">Salva</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-elimina-pren" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-red-900/30 backdrop-blur-sm transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-elimina-pren')"></div>
        <div class="bg-main border border-red-500/30 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-16 h-16 mx-auto bg-red-500/10 rounded-full flex items-center justify-center mb-4 text-red-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h3 class="text-xl font-black text-white mb-2 uppercase tracking-wide">Elimina Prenotazione</h3>
            <p class="text-[#BFD6E8] text-sm mb-8">Sei sicuro di voler annullare questa prenotazione? L'azione è irreversibile.</p>
            
            <form method="POST" action="gestisci.php?id=<?php echo $target_user_id; ?>">
                <input type="hidden" name="action" value="delete_prenotazione">
                <input type="hidden" name="prenotazione_id" id="modal-del-pren-id">
                
                <div class="flex gap-3">
                    <button type="button" onclick="chiudiModal('modal-elimina-pren')" class="flex-1 py-3.5 rounded-xl border border-white/20 text-white hover:bg-white/10 transition-all font-bold uppercase tracking-wider text-sm">Annulla</button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-500 py-3.5 rounded-xl text-white shadow-lg transition-all font-bold uppercase tracking-wider text-sm border border-red-500/50">Elimina</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-elimina-utente" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-red-900/30 backdrop-blur-sm transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-elimina-utente')"></div>
        <div class="bg-main border border-red-500/30 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-16 h-16 mx-auto bg-red-500/10 rounded-full flex items-center justify-center mb-4 text-red-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-black text-white mb-2 uppercase tracking-wide">Eliminazione Definitiva</h3>
            <p class="text-[#BFD6E8] text-sm mb-8">Stai per eliminare in via definitiva l'utente <strong><?php echo htmlspecialchars($nomeUtente . ' ' . $cognomeUtente); ?></strong>. Tutti i suoi dati e prenotazioni verranno persi. Vuoi procedere?</p>
            
            <form method="POST" action="gestisci.php?id=<?php echo $target_user_id; ?>">
                <input type="hidden" name="delete_user" value="1">
                
                <div class="flex gap-3">
                    <button type="button" onclick="chiudiModal('modal-elimina-utente')" class="flex-1 py-3.5 rounded-xl border border-white/20 text-white hover:bg-white/10 transition-all font-bold uppercase tracking-wider text-sm">Annulla</button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-500 py-3.5 rounded-xl text-white shadow-lg transition-all font-bold uppercase tracking-wider text-sm border border-red-500/50">Sì, Elimina Utente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function apriModalModifica(id, data, inizio, fine, nomeAsset) {
            document.getElementById('modal-pren-id').value = id;
            document.getElementById('modal-data').value = data;
            document.getElementById('modal-inizio').value = inizio.substring(0, 5);
            document.getElementById('modal-fine').value = fine.substring(0, 5);
            document.getElementById('modal-asset-nome').innerText = nomeAsset;
            mostraModal('modal-modifica');
        }

        function apriModalEliminaPrenotazione(id) {
            document.getElementById('modal-del-pren-id').value = id;
            mostraModal('modal-elimina-pren');
        }

        function apriModalEliminaUtente() {
            mostraModal('modal-elimina-utente');
        }

        function mostraModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function chiudiModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');

            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    </script>
</body>
</html>