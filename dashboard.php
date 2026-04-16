<?php
session_start();
require_once 'db.php';

// Controllo sicurezza: se non c'è user_id, rimanda al login
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// RECUPERO DATI SESSIONE
$nomeUtente = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Utente';
$ruoloUtente = strtolower(trim(isset($_SESSION['user_ruolo']) ? $_SESSION['user_ruolo'] : 'dipendente'));

// --- LOGICA FILTRO GLOBALE ---
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'tutto';
$cond = "1=1";
$cond_p = "1=1";

if ($periodo === 'oggi') {
    $cond = "DATE(data_prenotazione) = CURDATE()";
    $cond_p = "DATE(p.data_prenotazione) = CURDATE()";
} elseif ($periodo === 'settimana') {
    $cond = "YEARWEEK(data_prenotazione, 1) = YEARWEEK(CURDATE(), 1)";
    $cond_p = "YEARWEEK(p.data_prenotazione, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($periodo === 'mese') {
    $cond = "MONTH(data_prenotazione) = MONTH(CURDATE()) AND YEAR(data_prenotazione) = YEAR(CURDATE())";
    $cond_p = "MONTH(p.data_prenotazione) = MONTH(CURDATE()) AND YEAR(p.data_prenotazione) = YEAR(CURDATE())";
} elseif ($periodo === 'anno') {
    $cond = "YEAR(data_prenotazione) = YEAR(CURDATE())";
    $cond_p = "YEAR(p.data_prenotazione) = YEAR(CURDATE())";
}

// --- QUERY PER STATISTICHE (Filtrate) ---

// 1. Prenotazioni Totali
$q_tot = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND $cond");
$stat_totali = $q_tot ? $q_tot->fetch_assoc()['c'] : 0;

// 2. In Arrivo (Data futura o oggi ma non ancora finita)
$q_arr = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND CONCAT(data_prenotazione, ' ', ora_fine) > NOW() AND $cond");
$stat_in_arrivo = $q_arr ? $q_arr->fetch_assoc()['c'] : 0;

// 3. Statistica Dinamica (Card 3)
$label_card_3 = "Questo Mese";
if ($periodo === 'oggi') $label_card_3 = "Oggi";
elseif ($periodo === 'settimana') $label_card_3 = "Questa Settimana";
elseif ($periodo === 'mese') $label_card_3 = "Questo Mese";
elseif ($periodo === 'anno') $label_card_3 = "Quest'Anno";

if ($periodo === 'tutto') {
    $q_3 = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND MONTH(data_prenotazione) = MONTH(CURRENT_DATE()) AND YEAR(data_prenotazione) = YEAR(CURRENT_DATE())");
} else {
    $q_3 = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND $cond");
}
$stat_3 = $q_3 ? $q_3->fetch_assoc()['c'] : 0;

// 4. Postazione Più Usata
$q_top = $conn->query("SELECT a.nome, COUNT(*) as c FROM prenotazioni p JOIN asset a ON p.asset_id = a.id WHERE p.user_id = $user_id AND p.stato != 'annullata' AND $cond_p GROUP BY a.id ORDER BY c DESC LIMIT 1");
$stat_postazione_top = ($q_top && $q_top->num_rows > 0) ? $q_top->fetch_assoc()['nome'] : "Nessuna";

// --- PRENOTAZIONI IN ARRIVO (Lista Filtrata) ---
$mesi_it = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];
$tipi_label = ['base' => 'Scrivania Base', 'tech' => 'Scrivania Tech', 'meeting' => 'Sala Riunioni', 'parking' => 'Posto Auto'];

$prenotazioni_in_arrivo = [];
$q_list = $conn->query("
    SELECT p.data_prenotazione, p.ora_inizio, p.ora_fine, a.nome, a.tipo 
    FROM prenotazioni p 
    JOIN asset a ON p.asset_id = a.id 
    WHERE p.user_id = $user_id AND p.stato != 'annullata' AND CONCAT(p.data_prenotazione, ' ', p.ora_fine) > NOW() AND $cond_p
    ORDER BY p.data_prenotazione ASC, p.ora_inizio ASC 
    LIMIT 5
");

if ($q_list) {
    while($row = $q_list->fetch_assoc()) {
        $ts = strtotime($row['data_prenotazione']);
        $giorno = date('j', $ts);
        $mese = $mesi_it[(int)date('n', $ts)];
        
        $prenotazioni_in_arrivo[] = [
            'tipo_asset' => $row['tipo'],
            'tipo_label' => isset($tipi_label[$row['tipo']]) ? $tipi_label[$row['tipo']] : 'Risorsa',
            'titolo' => $row['nome'],
            'data' => "$giorno $mese",
            'orario' => date('H:i', strtotime($row['ora_inizio'])) . ' - ' . date('H:i', strtotime($row['ora_fine'])),
            'is_auto' => ($row['tipo'] == 'parking')
        ];
    }
}

// Funzione Helper per Icone
function getAssetIconDashboard($tipo) {
    $path = "src/Icone/";
    $icons = ['base' => 'PostazioneBase.svg', 'tech' => 'PostazioneTech.svg', 'meeting' => 'Riunioni.svg', 'parking' => 'PostoAuto.svg'];
    $filename = $path . (isset($icons[$tipo]) ? $icons[$tipo] : 'PostazioneBase.svg');
    return file_exists($filename) ? file_get_contents($filename) : '<div class="text-white/50 text-xs">N/A</div>';
}

// --- ATTIVITÀ (Dati Grafico Dinamici Filtrati) ---
$attivita_dati = [
    'settimana' => ['Lunedì' => 0, 'Martedì' => 0, 'Mercoledì' => 0, 'Giovedì' => 0, 'Venerdì' => 0],
    'mese' => ['1ª Sett' => 0, '2ª Sett' => 0, '3ª Sett' => 0, '4ª Sett' => 0],
    'anno' => ['Gen-Mar' => 0, 'Apr-Giu' => 0, 'Lug-Set' => 0, 'Ott-Dic' => 0]
];

$q_att = $conn->query("SELECT data_prenotazione FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND YEAR(data_prenotazione) = YEAR(CURRENT_DATE()) AND $cond");

$curr_week = date('W');
$curr_month = date('n');

if ($q_att) {
    while ($r = $q_att->fetch_assoc()) {
        $ts = strtotime($r['data_prenotazione']);
        
        // Assegno all'Anno (Trimestri per Mese)
        $m = date('n', $ts);
        if ($m <= 3) $attivita_dati['anno']['Gen-Mar']++;
        elseif ($m <= 6) $attivita_dati['anno']['Apr-Giu']++;
        elseif ($m <= 9) $attivita_dati['anno']['Lug-Set']++;
        else $attivita_dati['anno']['Ott-Dic']++;
        
        // Assegno al Mese Corrente
        if ($m == $curr_month) {
            $day = date('j', $ts);
            if ($day <= 7) $attivita_dati['mese']['1ª Sett']++;
            elseif ($day <= 14) $attivita_dati['mese']['2ª Sett']++;
            elseif ($day <= 21) $attivita_dati['mese']['3ª Sett']++;
            else $attivita_dati['mese']['4ª Sett']++;
        }
        
        // Assegno alla Settimana Corrente
        if (date('W', $ts) == $curr_week) {
            $day_w = date('N', $ts); 
            if ($day_w == 1) $attivita_dati['settimana']['Lunedì']++;
            elseif ($day_w == 2) $attivita_dati['settimana']['Martedì']++;
            elseif ($day_w == 3) $attivita_dati['settimana']['Mercoledì']++;
            elseif ($day_w == 4) $attivita_dati['settimana']['Giovedì']++;
            elseif ($day_w == 5) $attivita_dati['settimana']['Venerdì']++;
        }
    }
}

// Etichette dinamiche per i dropdown
$label_settimana = date('d/m', strtotime('monday this week')) . " - " . date('d/m', strtotime('sunday this week'));
$label_mese = $mesi_it[(int)date('n')];
$label_anno = date('Y');

// Colori e Gradienti dinamici in base al ruolo
$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]'],   
    'dipendente' => ['badge_bg' => '#6aa70f', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#4D7C0F_0%,#6AA70F_100%)]']      
];

$roleTheme = array_key_exists($ruoloUtente, $themeColors) ? $themeColors[$ruoloUtente] : $themeColors['dipendente'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LubooZucchetti</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .bg-card-1 { background: linear-gradient(145deg, #153C59, #1E587A); }
        .bg-card-2 { background: linear-gradient(145deg, #5A4B67, #6F5D7D); }
        .bg-card-3 { background: linear-gradient(145deg, #2E6C68, #3F8C84); }
        .bg-card-4 { background: linear-gradient(145deg, #3A86A6, #4FA8C7); }
        
        .bg-bookings { background: linear-gradient(160deg, #1E4D6B, #183F59); }
        .bg-booking-item { background: linear-gradient(145deg, #2C6B92, #3A7FA8); }
        .bg-booking-auto { background: linear-gradient(145deg, #2F6E58, #3F8C6F); }
        
        .bg-activity { background: linear-gradient(160deg, #184760, #215F7D); }
        .bg-progress-base { background-color: #355F75; }
        .bg-progress-fill { background: linear-gradient(to right, #5E8BFF, #9FA8FF); }
        .bg-btn-prenota { background: linear-gradient(145deg, #B3836A, #C89A80); }
        .bg-btn-gestisci { background: linear-gradient(145deg, #5C78B8, #6C8DD0); }

        .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(7, 27, 43, 0.6); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #36A482 0%, #1D7F75 100%);
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #51E0B8 0%, #36A482 100%);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.15), 0 0 8px rgba(81, 224, 184, 0.5);
        }
        
        /* SCROLLBAR DESIGN ELEGANTE GLOBALE */
        ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        ::-webkit-scrollbar-track {
            background: linear-gradient(180deg, rgba(7, 27, 43, 0.7) 0%, rgba(14, 47, 71, 0.8) 100%);
            border-radius: 10px;
            border: 1px solid rgba(54, 164, 130, 0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #36A482 0%, #1D7F75 50%, #0F6E73 100%);
            border-radius: 10px;
            border: 1px solid rgba(81, 224, 184, 0.3);
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.1), 0 0 8px rgba(54, 164, 130, 0.4);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #51E0B8 0%, #36A482 50%, #1D7F75 100%);
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.15), 0 0 16px rgba(81, 224, 184, 0.6);
            border-color: rgba(81, 224, 184, 0.5);
        }
        ::-webkit-scrollbar-thumb:active {
            background: linear-gradient(180deg, #36A482 0%, #0F6E73 100%);
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.1), 0 0 12px rgba(54, 164, 130, 0.5);
        }
        
        .glass-panel { backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1400px] flex flex-col gap-8 pt-24">

        <header class="fixed top-0 left-0 right-0 z-50">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4 mx-4 md:mx-6 lg:mx-8 mt-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    
                    <a href="messaggistica.php" class="relative flex items-center justify-center text-[#BFD6E8] hover:text-white transition-colors bg-white/5 p-2.5 rounded-xl border border-white/10 hover:bg-[#36A482]/20 hover:border-[#36A482]/50 group shadow-md" title="Messaggi">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-[#0A2338]"></span>
                    </a>

                    <div class="<?php echo $roleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10 hidden sm:flex">
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
                    <?php if (in_array($ruoloUtente, ['amministratore', 'coordinatore'])): ?>
                    <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                    <?php endif; ?>
                    
                    <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">DashBoard</a>
                    <a href="gestisci.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Gestisci</a>
                </nav>

                <div class="hidden xl:flex items-center gap-3 text-[#BFD6E8] text-xs font-semibold mr-2">
                    <a href="gestisci.php" class="hover:text-white transition-colors uppercase">Modifica</a>
                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                    <a href="loginhandle.php?action=logout" class="hover:text-white transition-colors uppercase">Cambia utente</a>
                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                    <a href="loginhandle.php?action=logout" class="text-[#FF8A8A] hover:text-[#FFB3B3] transition-colors uppercase">Esci</a>
                </div>
            </div>
        </header>

        <div class="w-full flex justify-end -mb-4 relative z-40">
            <form method="GET" id="filterForm">
                <select name="periodo" onchange="this.form.submit()" class="bg-[#0A2338]/80 text-[#BFD6E8] text-sm font-bold border border-white/10 rounded-xl px-4 py-2 outline-none shadow-md cursor-pointer hover:bg-white/10 transition-colors appearance-none">
                    <option value="tutto" <?php echo $periodo == 'tutto' ? 'selected' : ''; ?>>Tutto il periodo</option>
                    <option value="oggi" <?php echo $periodo == 'oggi' ? 'selected' : ''; ?>>Oggi</option>
                    <option value="settimana" <?php echo $periodo == 'settimana' ? 'selected' : ''; ?>>Questa Settimana</option>
                    <option value="mese" <?php echo $periodo == 'mese' ? 'selected' : ''; ?>>Questo Mese</option>
                    <option value="anno" <?php echo $periodo == 'anno' ? 'selected' : ''; ?>>Quest'Anno</option>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 w-full mt-2">
            <div class="bg-card-1 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-[#F2F7FF] mb-2 leading-none drop-shadow-md"><?php echo $stat_totali; ?></div>
                <div class="text-[12px] font-bold text-[#CDE3F7] uppercase tracking-wider">Prenotazioni totali</div>
            </div>
            <div class="bg-card-2 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-[#F4F2FF] mb-2 leading-none drop-shadow-md"><?php echo $stat_in_arrivo; ?></div>
                <div class="text-[12px] font-bold text-white/60 uppercase tracking-wider">In arrivo</div>
            </div>
            <div class="bg-card-3 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-white mb-2 leading-none drop-shadow-md"><?php echo $stat_3; ?></div>
                <div class="text-[12px] font-bold text-white/70 uppercase tracking-wider"><?php echo $label_card_3; ?></div>
            </div>
            <div class="bg-card-4 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#D9F1FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <div class="text-2xl font-bold text-white leading-tight drop-shadow-md truncate"><?php echo htmlspecialchars($stat_postazione_top); ?></div>
                <div class="text-[11px] font-bold text-white/70 uppercase tracking-widest mt-2">Postazione più usata</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            
            <div class="lg:col-span-2 bg-bookings glass-panel rounded-[24px] p-8 flex flex-col">
                <h2 class="text-2xl font-bold text-[#F1F6FF] mb-6">Prenotazioni in arrivo</h2>
                <div class="flex-grow overflow-y-auto custom-scrollbar pr-3 space-y-4 max-h-[450px]">
                    <?php if(empty($prenotazioni_in_arrivo)): ?>
                        <div class="text-center text-white/50 py-10">Nessuna prenotazione in arrivo trovata.</div>
                    <?php else: ?>
                        <?php foreach ($prenotazioni_in_arrivo as $pren): 
                            $cardBgClass = $pren['is_auto'] ? 'bg-booking-auto' : 'bg-booking-item';
                        ?>
                            <div class="<?php echo $cardBgClass; ?> rounded-2xl p-5 flex items-center gap-5 border border-white/10 shadow-md hover:brightness-110 transition-all">
                                <div class="w-12 h-12 rounded-[14px] bg-white/10 flex items-center justify-center text-white border border-white/10 shrink-0 [&>svg]:w-6 [&>svg]:h-6 fill-current">
                                    <?php echo getAssetIconDashboard($pren['tipo_asset']); ?>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-base font-bold text-[#F1F6FF]"><?php echo htmlspecialchars($pren['titolo']); ?></h4>
                                    <p class="text-xs font-bold text-[#BFD6E8] uppercase tracking-widest mt-0.5"><?php echo htmlspecialchars($pren['tipo_label']); ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-[#F1F6FF]"><?php echo htmlspecialchars($pren['data']); ?></div>
                                    <div class="text-xs font-bold text-[#F1F6FF] mt-1.5 bg-[#0A1B29]/40 px-3 py-1 rounded-md inline-block border border-white/5">
                                        <?php echo htmlspecialchars($pren['orario']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-col gap-8 h-full">
                
                <div class="bg-activity glass-panel rounded-[24px] p-8 flex flex-col flex-grow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8 relative z-20">
                        <h2 class="text-2xl font-bold text-[#F1F6FF]">Attività</h2>
                        <div class="relative inline-block text-left">
                            <button id="btn-dropdown-attivita" type="button" class="flex items-center gap-2 text-xs font-bold text-[#F1F6FF] bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg border border-white/10 transition-colors shadow-sm">
                                <span id="label-dropdown"><?php echo htmlspecialchars($label_settimana); ?></span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div id="menu-dropdown-attivita" class="hidden absolute right-0 mt-2 w-48 rounded-[16px] shadow-2xl border border-white/20 bg-[rgba(14,47,71,0.95)] backdrop-blur-xl overflow-hidden transform opacity-0 transition-all duration-200 origin-top-right scale-95 z-30">
                                <a href="#" data-target="settimana" data-label="<?php echo htmlspecialchars($label_settimana); ?>" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors">Settimana</a>
                                <a href="#" data-target="mese" data-label="Mese (<?php echo htmlspecialchars($label_mese); ?>)" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors border-t border-white/5">Mese (<?php echo htmlspecialchars($label_mese); ?>)</a>
                                <a href="#" data-target="anno" data-label="Anno (<?php echo htmlspecialchars($label_anno); ?>)" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors border-t border-white/5">Anno (<?php echo htmlspecialchars($label_anno); ?>)</a>
                            </div>
                        </div>
                    </div>

                    <div class="flex-grow flex flex-col justify-center relative min-h-[180px]">
                        <?php foreach ($attivita_dati as $per => $dati_periodo): 
                            $max_val = max($dati_periodo) ?: 1;
                            $hide_class = ($per === 'settimana') ? '' : 'hidden opacity-0';
                        ?>
                            <div id="dati-<?php echo $per; ?>" class="attivita-container flex-col gap-5 absolute inset-0 w-full h-full flex justify-center transition-opacity duration-300 <?php echo $hide_class; ?>">
                                <?php foreach ($dati_periodo as $label => $valore): 
                                    $percentuale = ($valore / $max_val) * 100;
                                ?>
                                <div class="flex items-center gap-4 w-full">
                                    <span class="w-[60px] text-sm font-medium text-white/90 truncate"><?php echo htmlspecialchars($label); ?></span>
                                    <div class="flex-grow h-6 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                        <div class="h-full rounded-full bg-progress-fill flex items-center justify-end pr-2" style="width: <?php echo $percentuale; ?>%; transition: width 0.5s ease-in-out;">
                                            <span class="text-white text-xs font-bold <?php echo ($valore == 0) ? 'hidden' : ''; ?>"><?php echo $valore; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white/5 glass-panel rounded-[24px] p-6 shrink-0">
                    <h2 class="text-lg font-bold text-white mb-4">Opzioni rapide</h2>
                    <div class="flex gap-3">
                        <a href="prenotazione.php" class="flex-1 bg-btn-prenota py-3.5 px-4 rounded-xl text-sm font-bold text-white shadow-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 border border-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg> Prenota
                        </a>
                        <a href="gestisci.php" class="flex-1 bg-btn-gestisci py-3.5 px-4 rounded-xl text-sm font-bold text-white shadow-lg hover:brightness-110 transition-all flex items-center justify-center gap-2 border border-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg> Gestisci
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btn-dropdown-attivita');
            const menu = document.getElementById('menu-dropdown-attivita');
            const label = document.getElementById('label-dropdown');
            const items = document.querySelectorAll('.dropdown-item');
            const containers = document.querySelectorAll('.attivita-container');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (menu.classList.contains('hidden')) {
                    menu.classList.remove('hidden');
                    setTimeout(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else { closeMenu(); }
            });

            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !btn.contains(e.target)) closeMenu();
            });

            function closeMenu() {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95');
                setTimeout(() => menu.classList.add('hidden'), 200);
            }

            items.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    label.innerText = item.getAttribute('data-label');
                    closeMenu();

                    containers.forEach(container => {
                        container.classList.remove('opacity-100', 'z-10');
                        container.classList.add('opacity-0', 'z-0');
                        setTimeout(() => container.classList.add('hidden'), 300);
                    });

                    const activeContainer = document.getElementById('dati-' + item.getAttribute('data-target'));
                    setTimeout(() => {
                        activeContainer.classList.remove('hidden');
                        setTimeout(() => {
                            activeContainer.classList.remove('opacity-0', 'z-0');
                            activeContainer.classList.add('opacity-100', 'z-10');
                        }, 50);
                    }, 300);
                });
            });
            document.getElementById('dati-settimana').classList.remove('hidden', 'opacity-0');
            document.getElementById('dati-settimana').classList.add('opacity-100', 'z-10');
        });
    </script>
</body>
</html>