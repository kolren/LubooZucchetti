<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

require_once 'db.php';

// =========================================================================
// 1. ENDPOINT AJAX: RECUPERA GLI OCCUPATI SENZA RICARICARE LA PAGINA
// =========================================================================
if (isset($_GET['ajax_map'])) {
    header('Content-Type: application/json');
    $d = $conn->real_escape_string($_GET['data']);
    $i = $conn->real_escape_string($_GET['inizio']);
    $f = $conn->real_escape_string($_GET['fine']);
    $occ = [];
    
    $query_occ = "SELECT a.codice_univoco, u.id as user_id, u.nome, u.cognome 
                  FROM prenotazioni p 
                  JOIN asset a ON p.asset_id = a.id
                  JOIN users u ON p.user_id = u.id
                  WHERE p.data_prenotazione = '$d' AND p.stato != 'annullata' 
                  AND ((p.ora_inizio < '$f' AND p.ora_fine > '$i'))";
    $res_occ = $conn->query($query_occ);
    if ($res_occ) {
        while ($row = $res_occ->fetch_assoc()) {
            $occ[$row['codice_univoco']] = [
                'id' => $row['user_id'],
                'nome_completo' => $row['nome'] . ' ' . $row['cognome']
            ];
        }
    }
    echo json_encode($occ);
    exit();
}

// =========================================================================
// 2. CARICAMENTO NORMALE DELLA PAGINA (PHP INIZIALE)
// =========================================================================
$utente_id = $_SESSION['user_id'];
$nomeUtente = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Utente';

$stmt_me = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_me->bind_param("i", $utente_id);
$stmt_me->execute();
$me_data = $stmt_me->get_result()->fetch_assoc();
$ruoloUtente = strtolower(trim(isset($me_data['role']) ? $me_data['role'] : 'dipendente'));

$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]'],   
    'dipendente' => ['badge_bg' => '#6aa70f', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#4D7C0F_0%,#6AA70F_100%)]']      
];
$roleTheme = array_key_exists($ruoloUtente, $themeColors) ? $themeColors[$ruoloUtente] : $themeColors['dipendente'];

// Parametri iniziali
$oggi = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$ora_inizio = isset($_GET['inizio']) ? $_GET['inizio'] : '09:00';
$ora_fine = isset($_GET['fine']) ? $_GET['fine'] : '18:00';
$mappa_attiva = isset($_GET['mappa']) ? $_GET['mappa'] : 'piano1'; 

// Prevenzione Backend Domenica su Load
$dataObj = new DateTime($oggi);
if ($dataObj->format('w') == 0) {
    $dataObj->modify('+1 day');
    $oggi = $dataObj->format('Y-m-d');
}

// Recupero Assets
$assets_info = [];
$query_assets = "SELECT id, codice_univoco, armadietto, nome FROM asset";
$res_assets = $conn->query($query_assets);
if ($res_assets) {
    while ($row = $res_assets->fetch_assoc()) {
        $assets_info[$row['codice_univoco']] = [
            'id' => $row['id'],
            'armadietto' => !empty($row['armadietto']) ? $row['armadietto'] : 'N/A',
            'nome' => $row['nome']
        ];
    }
}

// Occupati iniziali (al Load della pagina)
$occupati = [];
$d = $conn->real_escape_string($oggi);
$i = $conn->real_escape_string($ora_inizio);
$f = $conn->real_escape_string($ora_fine);
$query_occ = "SELECT a.codice_univoco, u.id as user_id, u.nome, u.cognome 
              FROM prenotazioni p 
              JOIN asset a ON p.asset_id = a.id
              JOIN users u ON p.user_id = u.id
              WHERE p.data_prenotazione = '$d' AND p.stato != 'annullata' 
              AND ((p.ora_inizio < '$f' AND p.ora_fine > '$i'))";
$res_occ = $conn->query($query_occ);
if ($res_occ) {
    while ($row = $res_occ->fetch_assoc()) {
        $occupati[$row['codice_univoco']] = [
            'id' => $row['user_id'],
            'nome_completo' => $row['nome'] . ' ' . $row['cognome']
        ];
    }
}

// Conteggio Limiti iniziali
$prenotazioni_oggi = ['base' => 0, 'tech' => 0, 'meeting' => 0, 'parking' => 0];
$stmt_oggi = $conn->prepare("
    SELECT a.tipo, COUNT(*) as conteggio 
    FROM prenotazioni p JOIN asset a ON p.asset_id = a.id 
    WHERE p.user_id = ? AND p.data_prenotazione = ? AND p.stato != 'annullata' GROUP BY a.tipo
");
$stmt_oggi->bind_param("is", $utente_id, $oggi);
$stmt_oggi->execute();
$res_oggi = $stmt_oggi->get_result();
while ($row = $res_oggi->fetch_assoc()) {
    $prenotazioni_oggi[strtolower($row['tipo'])] = (int)$row['conteggio'];
}

$limiti_ruolo = ['amministratore' => 3, 'coordinatore' => 2, 'dipendente' => 1];
$limite_max = isset($limiti_ruolo[$ruoloUtente]) ? $limiti_ruolo[$ruoloUtente] : 1;
$totale_prenotazioni_oggi = array_sum($prenotazioni_oggi);
$limite_raggiunto = ($totale_prenotazioni_oggi >= $limite_max);

function getSvgRapido($path) { return file_exists($path) ? file_get_contents($path) : '<div class="text-xs text-white/50">N/A</div>'; }
$sezioni = [
    'meeting' => ['icon' => getSvgRapido('src/Icone/Riunioni.svg'), 'nome' => 'Sale Riunioni', 'desc' => 'Sala attrezzata'],
    'base' => ['icon' => getSvgRapido('src/Icone/PostazioneBase.svg'), 'nome' => 'Scrivania Base', 'desc' => 'Scrivania + Cassettiera'],
    'tech' => ['icon' => getSvgRapido('src/Icone/PostazioneTech.svg'), 'nome' => 'Scrivania Tech', 'desc' => 'Scrivania + Monitor Extra'],
    'parking' => ['icon' => getSvgRapido('src/Icone/PostoAuto.svg'), 'nome' => 'Posto Auto', 'desc' => 'Parcheggio interrato']
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione - LubooZucchetti</title>
    <style>
        .svg-slot { fill: #36A482; cursor: pointer; transform-origin: center; transform-box: fill-box; transition: all 0.2s ease-in-out; transform: translate3d(0,0,0); } 
        .svg-slot:hover:not(.occupato):not(.occupato-arancione) { filter: brightness(1.3) drop-shadow(0px 0px 6px rgba(54, 164, 130, 0.7)); transform: translate3d(0,0,0) scale(1.1); }
        .svg-slot.occupato { fill: #ef4444; cursor: help; pointer-events: auto !important; } 
        .svg-slot.selezionato { fill: #ffffff; filter: drop-shadow(0 0 8px rgba(255,255,255,0.8)); } 
        
        /* LA NUOVA CLASSE ARANCIONE PER LA POSTAZIONE SELEZIONATA MA OCCUPATA */
        .svg-slot.occupato-arancione { fill: #FFA500 !important; cursor: help; pointer-events: auto !important; filter: drop-shadow(0 0 8px rgba(255,165,0,0.8)); }

        .png-risorsa { background-color: #36A482; cursor: pointer; mask-size: contain; -webkit-mask-size: contain; mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; transform-origin: center; transition: all 0.2s ease-in-out; transform: translate3d(0,0,0); }
        .png-risorsa:hover:not(.occupato):not(.occupato-arancione) { filter: brightness(1.3) drop-shadow(0px 0px 6px rgba(54, 164, 130, 0.7)); transform: translate3d(0,0,0) scale(1.1); }
        .png-risorsa.occupato { background-color: #ef4444; cursor: help; pointer-events: auto !important; }
        .png-risorsa.selezionato { background-color: #ffffff; filter: drop-shadow(0 0 8px rgba(255,255,255,0.8)); }
        
        /* PNG ARANCIONE */
        .png-risorsa.occupato-arancione { background-color: #FFA500 !important; cursor: help; pointer-events: auto !important; filter: drop-shadow(0 0 8px rgba(255,165,0,0.8)); }

        .risorsa-vietata { opacity: 0.4; filter: blur(2px) grayscale(50%); cursor: not-allowed !important; }
        .risorsa-vietata:hover { filter: blur(0px) grayscale(0%); transform: none; }
        .parcheggio-grid { display: grid; grid-template-columns: repeat(5, 35px); gap: 15px; justify-content: center; align-items: center; width: 100%; height: 100%; }
        
        input[type="time"]::-webkit-calendar-picker-indicator, input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; filter: invert(1); }
        input[type="time"]::-webkit-calendar-picker-indicator:hover, input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        #occupante-popup { animation: fadeIn 0.2s ease-out; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); z-index: 1000; }
        @keyframes fadeIn { from { opacity: 0; transform: translate(-50%, -90%); } to { opacity: 1; transform: translate(-50%, -100%); } }
        
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
        
        .chat-scroll::-webkit-scrollbar { width: 8px; }
        .chat-scroll::-webkit-scrollbar-track { background: rgba(7, 27, 43, 0.6); border-radius: 4px; }
        .chat-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #36A482 0%, #1D7F75 100%);
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.1);
        }
        .chat-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #51E0B8 0%, #36A482 100%);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.15), 0 0 8px rgba(81, 224, 184, 0.5);
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF] relative">

    <div class="w-full max-w-[1400px] flex flex-col gap-6 pt-24">
        
        <header class="fixed top-0 left-0 right-0 z-50">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4 mx-4 md:mx-6 lg:mx-8 mt-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    <a href="messaggistica.php" class="relative flex items-center justify-center text-[#BFD6E8] hover:text-white transition-colors bg-white/5 p-2.5 rounded-xl border border-white/10 hover:bg-[#36A482]/20 hover:border-[#36A482]/50 group shadow-md" title="Messaggi">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </a>
                    <div class="<?php echo $roleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10 hidden sm:flex">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md self-start mb-0.5 shadow-sm" style="background-color: <?php echo $roleTheme['badge_bg']; ?>; color: <?php echo $roleTheme['badge_text']; ?>;">
                            <?php echo htmlspecialchars($ruoloUtente); ?>
                        </span>
                        <span class="font-bold text-lg leading-none drop-shadow-md text-white mt-1">Ciao <?php echo htmlspecialchars($nomeUtente); ?>!</span>
                    </div>
                </div>

                <nav class="flex items-center gap-2 bg-[#0A2338]/40 p-1.5 rounded-[20px] border border-white/10 overflow-x-auto custom-scrollbar">
                    <?php if (in_array($ruoloUtente, ['amministratore', 'coordinatore'])): ?>
                    <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                    <?php endif; ?>
                    <a href="prenotazione.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">DashBoard</a>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch min-h-[650px]">
            
            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold bg-gradient-to-r from-white to-[#ADD0FF] bg-clip-text text-transparent">Prenotazioni disponibili</h2>
                </div>
                
                <div class="flex flex-col gap-3 mb-8">
                    <?php foreach ($sezioni as $id => $info): 
                        $is_vietato = ($ruoloUtente === 'dipendente' && $id === 'meeting');
                        $opacita = $is_vietato ? 'opacity-40 blur-[1px]' : '';
                    ?>
                    <div data-cat-id="<?php echo $id; ?>" class="sez-item relative p-4 rounded-2xl transition-all flex items-center gap-3 bg-[rgba(255,255,255,0.05)] border border-white/5 <?php echo $opacita; ?>">
                        <div class="w-8 h-8 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper"><?php echo $info['icon']; ?></div>
                        <div class="flex-1">
                            <div class="text-white font-bold text-sm leading-tight"><?php echo $info['nome']; ?></div>
                            <div class="text-white/80 text-[10px] mt-0.5 leading-none"><?php echo $info['desc']; ?></div>
                        </div>
                        <?php if($is_vietato): ?><div class="absolute inset-0 z-10" onclick="mostraErrore('Permesso Negato', 'Non sei autorizzato a prenotare Sale Riunioni.')" style="cursor: not-allowed;"></div><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mb-4 bg-[#0A2338]/50 p-4 rounded-xl border border-white/5">
                    <div class="flex justify-between items-center mb-1 text-sm font-bold text-white">
                        <span>Prenotazioni per il <span class="text-[#36A482]" id="display-date-text"><?php echo date('d/m', strtotime($oggi)); ?></span>:</span>
                        <span id="prenotazioni-count" class="<?php echo $limite_raggiunto ? 'text-red-400' : 'text-[#36A482]'; ?> text-lg">
                            <?php echo $totale_prenotazioni_oggi; ?> / <?php echo $limite_max; ?>
                        </span>
                    </div>
                    <div id="limite-raggiunto-msg" class="text-xs text-red-400 bg-red-500/10 mt-2 p-2 rounded-lg border border-red-500/20 text-center shadow-inner <?php echo !$limite_raggiunto ? 'hidden' : ''; ?>">
                        Limite giornaliero raggiunto per il tuo ruolo.
                    </div>
                </div>

                <div class="mt-auto bg-[#071B2B]/30 p-4 rounded-2xl border border-white/5">
                    <h3 class="text-sm font-bold text-white mb-3">Legenda</h3>
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#36A482] rounded-full"></div><span class="text-white/90">Disponibile</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#ef4444] rounded-full"></div><span class="text-white/90">Occupata</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-white rounded-full border border-gray-400" style="filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));"></div><span class="text-white/90">Selezionata</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#FFA500] rounded-full" style="filter: drop-shadow(0 0 5px rgba(255,165,0,0.8));"></div><span class="text-[#FFA500] font-bold">Selezionata ma Occupata</span></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 ui-panel glass-panel p-6 flex flex-col relative overflow-hidden shadow-2xl">
                
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="bg-white/5 px-4 py-2 rounded-xl border border-white/10 flex items-center gap-3 shadow-sm">
                        <div class="text-[#BFD6E8] text-xs font-bold uppercase tracking-wide">Disponibili:</div>
                        <div class="text-xl font-black text-white"><span id="posti_disponibili" class="text-[#36A482]">-</span> / <span id="posti_totali" class="text-white">-</span></div>
                    </div>

                    <div class="relative flex items-center group cursor-pointer bg-[#0A2338]/80 rounded-xl px-2 border border-white/10">
                        <select id="select-piano" class="appearance-none bg-transparent border-none py-2 text-lg font-black text-white cursor-pointer pr-8 pl-4 focus:ring-0 outline-none text-right drop-shadow-md tracking-wide">
                            <option value="piano1" class="text-black" <?php echo ($mappa_attiva === 'piano1') ? 'selected' : ''; ?>>Piano 1</option>
                            <option value="piano2" class="text-black" <?php echo ($mappa_attiva === 'piano2') ? 'selected' : ''; ?>>Piano 2</option>
                            <option value="parking" class="text-black" <?php echo ($mappa_attiva === 'parking') ? 'selected' : ''; ?>>Parcheggio</option>
                        </select>
                        <div class="pointer-events-none absolute right-3 flex items-center text-[#36A482]">
                            <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-grow w-full rounded-[20px] bg-[#071B2B]/40 border border-white/5 relative overflow-hidden flex justify-center items-center">
                    
                    <?php if ($mappa_attiva === 'piano1'): ?>
                        <div class="absolute text-center z-20 pointer-events-none" style="top: 2%; left: 50%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#36A482]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/Riunioni.svg" class="w-4 h-4"> Sale Riunioni</div>
                        </div>
                        <div class="absolute flex items-center justify-center gap-4" style="top: 8%; left: 50%; transform: translateX(-50%); width: 80%; max-width: 450px; height: 70px; background-color: rgba(30,58,138,0.4); border-radius: 15px;">
                            <?php for($i=1; $i<=5; $i++): 
                                $cid = "room-$i"; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $vietato_class = ($ruoloUtente === 'dipendente') ? 'risorsa-vietata' : ''; 
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class . ' ' . $vietato_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" data-occupante-nome="<?php echo $occ_nome; ?>" data-occupante-id="<?php echo $occ_id; ?>" style="width: 45px; height: 35px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute text-center z-20 pointer-events-none" style="top: 25%; left: 50%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#FFA500]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/PostazioneTech.svg" class="w-4 h-4"> Scrivanie Tech</div>
                        </div>
                        <div class="absolute" style="top: 32%; left: 50%; transform: translateX(-50%);">
                            <svg width="400" height="100" viewBox="0 0 400 100">
                                <rect width="400" height="100" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php 
                                $tech_slots = []; for($i=1; $i<=6; $i++) $tech_slots[] = ['cx'=> 40 + ($i-1)*65, 'cy'=>30, 'id'=>"desk-t-$i"]; for($i=7; $i<=12; $i++) $tech_slots[] = ['cx'=> 40 + ($i-7)*65, 'cy'=>70, 'id'=>"desk-t-$i"];
                                foreach($tech_slots as $slot) {
                                    $cid = $slot['id']; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                    $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                    $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='11' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='tech' data-locker='{$locker}' data-nome='{$nome}' data-occupante-nome='{$occ_nome}' data-occupante-id='{$occ_id}'/>";
                                } ?>
                            </svg>
                        </div>

                        <div class="absolute text-center z-20 pointer-events-none" style="top: 53%; left: 50%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#36A482]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/PostazioneBase.svg" class="w-4 h-4"> Scrivanie Base</div>
                        </div>
                        <div class="absolute" style="top: 60%; left: 50%; transform: translateX(-50%);">
                            <svg width="400" height="100" viewBox="0 0 400 100">
                                <rect width="400" height="100" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php 
                                $base_slots = []; for($i=1; $i<=6; $i++) $base_slots[] = ['cx'=> 40 + ($i-1)*65, 'cy'=>30, 'id'=>"desk-b-$i"]; for($i=7; $i<=12; $i++) $base_slots[] = ['cx'=> 40 + ($i-7)*65, 'cy'=>70, 'id'=>"desk-b-$i"];
                                foreach($base_slots as $slot) {
                                    $cid = $slot['id']; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                    $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                    $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='11' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='base' data-locker='{$locker}' data-nome='{$nome}' data-occupante-nome='{$occ_nome}' data-occupante-id='{$occ_id}'/>";
                                } ?>
                            </svg>
                        </div>

                    <?php elseif ($mappa_attiva === 'piano2'): ?>
                        <div class="absolute text-center z-20 pointer-events-none" style="top: 2%; left: 40%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#36A482]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/Riunioni.svg" class="w-4 h-4"> Sale Riunioni</div>
                        </div>
                        <div class="absolute flex items-center justify-center gap-3" style="top: 8%; left: 40%; transform: translateX(-50%); width: 70%; max-width: 350px; height: 70px; background-color: rgba(30,58,138,0.4); border-radius: 15px;">
                            <?php for($i=6; $i<=10; $i++): 
                                $cid = "room-$i"; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $vietato_class = ($ruoloUtente === 'dipendente') ? 'risorsa-vietata' : ''; 
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class . ' ' . $vietato_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" data-occupante-nome="<?php echo $occ_nome; ?>" data-occupante-id="<?php echo $occ_id; ?>" style="width: 45px; height: 35px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute text-center z-20 pointer-events-none" style="top: 25%; left: 40%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#FFA500]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/PostazioneTech.svg" class="w-4 h-4"> Scrivanie Tech</div>
                        </div>
                        <div class="absolute" style="top: 32%; left: 40%; transform: translateX(-50%);">
                            <svg width="400" height="100" viewBox="0 0 400 100">
                                <rect width="400" height="100" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php 
                                $tech_slots_p2 = []; for($i=13; $i<=18; $i++) $tech_slots_p2[] = ['cx'=> 40 + ($i-13)*65, 'cy'=>30, 'id'=>"desk-t-$i"]; for($i=19; $i<=24; $i++) $tech_slots_p2[] = ['cx'=> 40 + ($i-19)*65, 'cy'=>70, 'id'=>"desk-t-$i"];
                                foreach($tech_slots_p2 as $slot) {
                                    $cid = $slot['id']; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                    $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                    $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='11' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='tech' data-locker='{$locker}' data-nome='{$nome}' data-occupante-nome='{$occ_nome}' data-occupante-id='{$occ_id}'/>";
                                } ?>
                            </svg>
                        </div>

                        <div class="absolute text-center z-20 pointer-events-none" style="top: 8%; right: 2%;">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#36A482]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/PostazioneBase.svg" class="w-4 h-4"> Scrivanie Base</div>
                        </div>
                        <div class="absolute" style="top: 15%; right: 5%;">
                            <svg width="70" height="400" viewBox="0 0 70 400">
                                <rect width="70" height="400" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php 
                                $base_slots = []; for($i=25; $i<=30; $i++) $base_slots[] = ['cx'=> 35, 'cy'=> 40 + ($i-25)*65, 'id'=>"desk-b-$i"];
                                foreach($base_slots as $slot) {
                                    $cid = $slot['id']; $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : ''; $is_occupato = isset($occupati[$cid]);
                                    $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$cid]['nome_completo'], ENT_QUOTES) : '';
                                    $occ_id = $is_occupato ? $occupati[$cid]['id'] : ''; $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A'; $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='11' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='base' data-locker='{$locker}' data-nome='{$nome}' data-occupante-nome='{$occ_nome}' data-occupante-id='{$occ_id}'/>";
                                } ?>
                            </svg>
                        </div>

                    <?php elseif ($mappa_attiva === 'parking'): ?>
                        <div class="absolute text-center z-20 pointer-events-none" style="top: 2%; left: 50%; transform: translateX(-50%);">
                            <div class="flex items-center gap-2 text-white font-bold text-sm bg-[#071B2B]/80 px-4 py-1 rounded-full border border-[#36A482]/50 shadow-lg backdrop-blur-sm"><img src="src/Icone/PostoAuto.svg" class="w-4 h-4"> Posti Auto</div>
                        </div>
                        <div class="parcheggio-grid z-10 w-full h-full p-8 relative top-4">
                            <?php 
                            $contatore = 1;
                            for ($fila = 1; $fila <= 5; $fila++) {
                                for ($posto = 1; $posto <= 5; $posto++) {
                                    $id_posto = "park-" . $contatore; $is_disabile = ($posto >= 4); $mask_url = $is_disabile ? 'src/AssetMappa/PostoAutoDisabili.png' : 'src/AssetMappa/PostoAuto.png';
                                    $db_id = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['id'] : ''; $is_occupato = isset($occupati[$id_posto]);
                                    $occ_class = $is_occupato ? 'occupato' : ''; $occ_nome = $is_occupato ? htmlspecialchars($occupati[$id_posto]['nome_completo'], ENT_QUOTES) : '';
                                    $occ_id = $is_occupato ? $occupati[$id_posto]['id'] : ''; $locker = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['armadietto'] : 'N/A'; $nome = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['nome'] : $id_posto;
                                    echo "<div class='png-risorsa risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$id_posto}' data-tipo='parking' data-locker='{$locker}' data-nome='{$nome}' data-occupante-nome='{$occ_nome}' data-occupante-id='{$occ_id}' style='width:100%; height:100%; -webkit-mask-image: url(\"{$mask_url}\"); mask-image: url(\"{$mask_url}\");'></div>";
                                    $contatore++;
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl relative">
                <form id="form-prenotazione" action="salva_prenotazione.php" method="POST" class="flex flex-col h-full relative z-10">
                    <input type="hidden" id="input-asset" name="asset_id">
                    <input type="hidden" name="mappa_corrente" value="<?php echo htmlspecialchars($mappa_attiva); ?>">
                    
                    <h2 class="text-xl font-bold bg-gradient-to-r from-[#36A482] to-[#51E0B8] bg-clip-text text-transparent mb-6 text-center">Dettagli Prenotazione</h2>
                    
                    <div class="bg-[#071B2B]/40 p-4 rounded-xl border border-white/5 mb-4 shadow-inner">
                        <div class="text-[10px] text-[#BFD6E8] uppercase tracking-wider mb-1 opacity-80">Risorsa Selezionata</div>
                        <div id="display-asset" class="text-lg font-black text-white truncate">-</div>
                        <div class="flex items-center gap-2 mt-2 pt-2 border-t border-white/10">
                            <svg class="w-3.5 h-3.5 text-[#36A482]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <span class="text-xs text-[#BFD6E8] font-semibold">Armadietto: <span id="display-locker" class="text-white">-</span></span>
                        </div>
                    </div>

                    <div class="space-y-4 flex-grow">
                        <div class="bg-[#0A2338]/50 p-3 rounded-xl border border-white/5 flex items-center justify-between group hover:border-white/10 transition-colors">
                            <label class="text-xs font-bold text-[#BFD6E8] uppercase flex items-center gap-2"><svg class="w-4 h-4 text-[#36A482]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>Data</label>
                            <input type="date" id="input-data" name="data" value="<?php echo htmlspecialchars($oggi); ?>" min="<?php echo date('Y-m-d'); ?>" required class="bg-transparent focus:outline-none text-right font-semibold text-white cursor-pointer w-32">
                        </div>
                        <div class="bg-[#0A2338]/50 p-3 rounded-xl border border-white/5 flex items-center justify-between group hover:border-white/10 transition-colors">
                            <label class="text-xs font-bold text-[#BFD6E8] uppercase flex items-center gap-2"><svg class="w-4 h-4 text-[#36A482]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Inizio</label>
                            <input type="time" name="inizio" value="<?php echo htmlspecialchars($ora_inizio); ?>" min="08:00" max="18:00" required class="bg-transparent focus:outline-none text-center w-24">
                        </div>
                        <div class="bg-[#0A2338]/50 p-3 rounded-xl border border-white/5 flex items-center justify-between group hover:border-white/10 transition-colors">
                            <label class="text-xs font-bold text-[#BFD6E8] uppercase flex items-center gap-2"><svg class="w-4 h-4 text-[#36A482]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Fine</label>
                            <input type="time" name="fine" value="<?php echo htmlspecialchars($ora_fine); ?>" min="08:00" max="18:00" required class="bg-transparent focus:outline-none text-center w-24">
                        </div>
                    </div>

                    <div class="mt-auto pt-4 flex flex-col items-center">
                        <div id="icona-selezionata-container" class="mb-4 h-16 w-16 bg-white/5 rounded-2xl flex items-center justify-center text-white/30 border border-white/10 transition-all shadow-inner"><span class="text-xs">N/A</span></div>
                        <button type="submit" id="btn-submit" disabled class="w-full py-4 px-4 rounded-xl font-black text-white text-md uppercase tracking-wider transition-all border border-white/10 shadow-xl bg-[#36A482] hover:brightness-110 hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">Conferma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-chat" class="fixed inset-0 z-[200] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#04101A]/80 backdrop-blur-sm" onclick="chiudiChat()"></div>
        <div class="bg-[#0A2338] border border-[#36A482]/50 rounded-2xl shadow-2xl z-10 w-full max-w-md h-[500px] flex flex-col relative overflow-hidden transform transition-all">
            <div class="bg-[#071B2B] p-4 border-b border-[#BFD6E8]/10 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#36A482] rounded-full flex items-center justify-center text-white font-bold shadow-inner"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                    <div><h3 class="text-white font-bold leading-tight" id="chat-user-name">Utente</h3><span class="text-[10px] text-[#36A482] uppercase tracking-wider font-semibold">Messaggio Diretto</span></div>
                </div>
                <button onclick="chiudiChat()" class="text-[#BFD6E8] hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div id="chat-messages" class="flex-grow p-4 overflow-y-auto chat-scroll flex flex-col gap-2"></div>
            <div class="p-3 border-t border-[#BFD6E8]/10 bg-[#071B2B]">
                <form id="form-chat" onsubmit="inviaMessaggioChat(event)" class="flex gap-2 relative">
                    <input type="text" id="chat-input" placeholder="Scrivi un messaggio..." autocomplete="off" class="flex-grow bg-[#04101A] border border-[#BFD6E8]/20 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-[#36A482] transition-colors">
                    <button type="submit" class="bg-[#36A482] hover:bg-[#2c8569] text-white rounded-xl px-4 flex items-center justify-center transition-colors"><svg class="w-5 h-5 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-errore" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#071B2B]/80 backdrop-blur-md transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-errore')"></div>
        <div class="bg-main border border-red-500/30 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-16 h-16 mx-auto bg-red-500/10 rounded-full flex items-center justify-center mb-4 text-red-500"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            <h3 id="errore-titolo" class="text-xl font-black text-white mb-2 uppercase tracking-wide">Attenzione</h3>
            <p id="errore-msg" class="text-[#BFD6E8] text-sm"></p>
            <button onclick="chiudiModal('modal-errore')" class="mt-6 w-full py-3 bg-red-500 hover:bg-red-400 text-white font-bold rounded-xl transition-colors">Chiudi</button>
        </div>
    </div>

    <div id="modal-successo" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#071B2B]/80 backdrop-blur-md transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-successo')"></div>
        <div class="bg-main border border-green-500/30 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-16 h-16 mx-auto bg-green-500/10 rounded-full flex items-center justify-center mb-4 text-green-500"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <h3 id="successo-titolo" class="text-xl font-black text-white mb-2 uppercase tracking-wide">Successo</h3>
            <p id="successo-msg" class="text-[#BFD6E8] text-sm"></p>
            <button onclick="chiudiModal('modal-successo')" class="mt-6 w-full py-3 bg-green-500 hover:bg-green-400 text-white font-bold rounded-xl transition-colors">Ottimo!</button>
        </div>
    </div>

    <script>
        const iconeMappa = <?php echo json_encode(array_map(function($s){ return $s['icon']; }, $sezioni)); ?>;
        
        let selezioneAttuale = null;
        let maxPrenotazioni = <?php echo $limite_max; ?>;
        let prenotazioniEffettuate = <?php echo $totale_prenotazioni_oggi; ?>;

        // =======================================================================
        // IL MOTORE AJAX: AGGIORNA LIMITI E MAPPA MANTENENDO LA SELEZIONE
        // =======================================================================
        async function aggiornaDatiSenzaReload() {
            const dataInput = document.getElementById('input-data').value;
            const inizio = document.querySelector('input[name="inizio"]').value;
            const fine = document.querySelector('input[name="fine"]').value;

            // 1. Controllo Domenica
            const dataSelezionata = new Date(dataInput);
            if (dataSelezionata.getDay() === 0) {
                mostraErrore('Giorno Festivo', 'Non è possibile effettuare prenotazioni di Domenica.');
                const oggi = new Date();
                if (oggi.getDay() === 0) oggi.setDate(oggi.getDate() + 1);
                document.getElementById('input-data').value = oggi.toISOString().split('T')[0];
                return;
            }

            if (inizio >= fine) {
                mostraErrore('Orario non valido', 'L\'orario di fine deve essere successivo all\'orario di inizio.');
                return;
            }

            try {
                // 2. Fetch Conteggio e Limiti
                const countRes = await fetch(`api_prenotazioni_count.php?data=${dataInput}`);
                const countData = await countRes.json();
                
                prenotazioniEffettuate = countData.totale;
                maxPrenotazioni = countData.limite_max;
                
                document.getElementById('prenotazioni-count').innerHTML = `${prenotazioniEffettuate} / ${maxPrenotazioni}`;
                const splitData = dataInput.split('-');
                document.getElementById('display-date-text').innerText = `${splitData[2]}/${splitData[1]}`;
                
                const limitMsg = document.getElementById('limite-raggiunto-msg');
                const countSpan = document.getElementById('prenotazioni-count');
                
                let limitReached = countData.limite_raggiunto;
                if (limitReached) {
                    limitMsg.classList.remove('hidden');
                    countSpan.classList.remove('text-[#36A482]');
                    countSpan.classList.add('text-red-400');
                    document.getElementById('btn-submit').disabled = true;
                } else {
                    limitMsg.classList.add('hidden');
                    countSpan.classList.add('text-[#36A482]');
                    countSpan.classList.remove('text-red-400');
                }

                // 3. Fetch Map Data (Risorse Occupate)
                const occRes = await fetch(`prenotazione.php?ajax_map=1&data=${dataInput}&inizio=${inizio}&fine=${fine}`);
                const occData = await occRes.json();

                // 4. Aggiorna Graficamente la Mappa
                document.querySelectorAll('.risorsa-item').forEach(item => {
                    const cid = item.getAttribute('data-id');
                    const isSelected = (selezioneAttuale === item);
                    const isOccupied = occData[cid] !== undefined;

                    // Pulizia Classi
                    item.classList.remove('occupato', 'occupato-arancione');
                    item.removeAttribute('data-occupante-nome');
                    item.removeAttribute('data-occupante-id');

                    if (isOccupied) {
                        item.setAttribute('data-occupante-nome', occData[cid].nome_completo);
                        item.setAttribute('data-occupante-id', occData[cid].id);
                        
                        if (isSelected) {
                            // SELEZIONATA MA DIVENTATA OCCUPATA -> Diventa Arancione e Blocca Conferma
                            item.classList.add('occupato-arancione');
                            document.getElementById('btn-submit').disabled = true;
                        } else {
                            item.classList.add('occupato'); // Solo rossa
                        }
                    } else {
                        if (isSelected) {
                            // Ritorna/Resta Bianca e Attivabile
                            if (!limitReached) {
                                document.getElementById('btn-submit').disabled = false;
                            }
                        }
                    }
                });

                // Update UI dei numeri in alto alla mappa
                const totale = document.querySelectorAll('.risorsa-item:not(.risorsa-vietata)').length;
                const occs = document.querySelectorAll('.risorsa-item.occupato').length + document.querySelectorAll('.risorsa-item.occupato-arancione').length;
                document.getElementById('posti_totali').textContent = totale;
                document.getElementById('posti_disponibili').textContent = Math.max(0, totale - occs);

            } catch(e) { console.error("Errore AJAX:", e); }
        }

        // Listener che attivano l'AJAX invece del caricamento di pagina
        document.getElementById('input-data').addEventListener('change', aggiornaDatiSenzaReload);
        document.querySelector('input[name="inizio"]').addEventListener('change', aggiornaDatiSenzaReload);
        document.querySelector('input[name="fine"]').addEventListener('change', aggiornaDatiSenzaReload);

        // IL CAMBIO DEL PIANO INVECE RICHIEDE IL RELOAD (Per ricostruire gli SVG giusti)
        document.getElementById('select-piano').addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('mappa', this.value);
            params.set('data', document.getElementById('input-data').value);
            params.set('inizio', document.querySelector('input[name="inizio"]').value);
            params.set('fine', document.querySelector('input[name="fine"]').value);
            window.location.href = window.location.pathname + '?' + params.toString();
        });

        // =======================================================================
        // GESTIONE DEI CLICK SULLE POSTAZIONI NELLA MAPPA
        // =======================================================================
        document.querySelectorAll('.risorsa-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Se è occupata o arancione (già occupata e tenti di cliccarci)
                if (this.classList.contains('occupato') || this.classList.contains('occupato-arancione')) {
                    e.stopPropagation();
                    const nomeOcc = this.getAttribute('data-occupante-nome');
                    const idOcc = this.getAttribute('data-occupante-id');
                    if(nomeOcc && idOcc) mostraPopupOccupante(nomeOcc, idOcc, e.clientX, e.clientY);
                    return;
                }
                
                if (this.classList.contains('risorsa-vietata')) return;
                
                if (prenotazioniEffettuate >= maxPrenotazioni) { 
                    e.preventDefault(); 
                    mostraErrore('Limite Raggiunto', `Hai già raggiunto il limite di ${maxPrenotazioni} prenotazioni per questa data.`); 
                    return; 
                }

                // Rimuovi la selezione da quella vecchia
                if (selezioneAttuale) {
                    selezioneAttuale.classList.remove('selezionato', 'occupato-arancione');
                    // Se la postazione vecchia era occupata in questa data, deve tornare Rossa
                    if (selezioneAttuale.hasAttribute('data-occupante-nome')) {
                        selezioneAttuale.classList.add('occupato');
                    }
                }
                
                // Assegna la selezione a quella nuova
                this.classList.add('selezionato');
                selezioneAttuale = this;

                // Popola il form
                document.getElementById('input-asset').value = this.getAttribute('data-dbid');
                document.getElementById('display-asset').textContent = this.getAttribute('data-nome');
                document.getElementById('display-locker').textContent = this.getAttribute('data-locker');
                document.getElementById('btn-submit').disabled = false;
                
                const tipo = this.getAttribute('data-tipo');
                document.getElementById('icona-selezionata-container').innerHTML = `<div class="w-10 h-10 flex items-center justify-center text-[#36A482] filter drop-shadow-md svg-icon-wrapper scale-110">${iconeMappa[tipo] || iconeMappa['base']}</div>`;
            });
        });

        // =======================================================================
        // GESTIONE POPUP OCCUPANTE E CHAT
        // =======================================================================
        document.addEventListener('click', (e) => {
            const popup = document.getElementById('occupante-popup');
            if (popup && !popup.contains(e.target) && !e.target.closest('.occupato') && !e.target.closest('.occupato-arancione')) popup.remove();
        });

        function mostraPopupOccupante(nome, uid, x, y) {
            let existing = document.getElementById('occupante-popup');
            if(existing) existing.remove();
            
            const popup = document.createElement('div');
            popup.id = 'occupante-popup';
            popup.className = 'fixed bg-[#0A2338] border border-[#36A482] rounded-xl p-4 shadow-2xl text-white z-[100] flex flex-col gap-2 transform -translate-x-1/2 -translate-y-full mt-[-10px]';
            popup.style.left = `${x}px`;
            popup.style.top = `${y}px`;
            
            const safeName = nome.replace(/'/g, "\\'");
            popup.innerHTML = `
                <div class="text-[10px] text-[#BFD6E8] uppercase tracking-wide font-bold">Postazione Occupata</div>
                <div class="font-black text-lg leading-tight mb-1 text-white">${nome}</div>
                <button onclick="apriChat(${uid}, '${safeName}')" class="bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)] hover:brightness-110 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md mt-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    Invia Messaggio
                </button>
            `;
            document.body.appendChild(popup);
        }

        let chatTargetId = null;
        let chatPolling = null;

        function apriChat(uid, nome) {
            chatTargetId = uid;
            document.getElementById('chat-user-name').textContent = nome;
            const popup = document.getElementById('occupante-popup');
            if(popup) popup.remove();
            
            const modal = document.getElementById('modal-chat');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => document.getElementById('chat-input').focus(), 100);

            caricaMessaggi();
            if(chatPolling) clearInterval(chatPolling);
            chatPolling = setInterval(caricaMessaggi, 2000);
        }

        function chiudiChat() {
            const modal = document.getElementById('modal-chat');
            modal.classList.add('hidden'); modal.classList.remove('flex');
            chatTargetId = null;
            if(chatPolling) clearInterval(chatPolling);
        }

        async function caricaMessaggi() {
            if(!chatTargetId) return;
            try {
                const response = await fetch(`api_messaggi.php?partner_id=${chatTargetId}`);
                const html = await response.text();
                const container = document.getElementById('chat-messages');
                const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 20;
                container.innerHTML = html;
                if(isScrolledToBottom) container.scrollTop = container.scrollHeight;
            } catch(e) {}
        }

        async function inviaMessaggioChat(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const testo = input.value.trim();
            if(!testo || !chatTargetId) return;
            input.value = '';
            
            const formData = new FormData();
            formData.append('destinatario_id', chatTargetId);
            formData.append('testo', testo);
            try {
                await fetch('api_messaggi.php', { method: 'POST', body: formData });
                await caricaMessaggi();
                setTimeout(() => { document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight; }, 50);
            } catch(e) {}
        }

        // =======================================================================
        // FUNZIONI DI SUPPORTO E MESSAGGI GLOBALI
        // =======================================================================
        function mostraErrore(titolo, msg) {
            document.getElementById('errore-titolo').textContent = titolo;
            document.getElementById('errore-msg').textContent = msg;
            const modal = document.getElementById('modal-errore');
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => { backdrop.classList.add('opacity-100'); content.classList.add('scale-100', 'opacity-100'); }, 10);
        }

        function mostraSuccesso(titolo, msg) {
            document.getElementById('successo-titolo').textContent = titolo;
            document.getElementById('successo-msg').textContent = msg;
            const modal = document.getElementById('modal-successo');
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            setTimeout(() => { backdrop.classList.add('opacity-100'); content.classList.add('scale-100', 'opacity-100'); }, 10);
        }

        function chiudiModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            backdrop.classList.remove('opacity-100'); content.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
        }

        // Setup UI Iniziale Pura
        const totale = document.querySelectorAll('.risorsa-item:not(.risorsa-vietata)').length;
        const occs = document.querySelectorAll('.risorsa-item.occupato').length;
        document.getElementById('posti_totali').textContent = totale;
        document.getElementById('posti_disponibili').textContent = Math.max(0, totale - occs);

        // Controllo Alert Generati dal Backend su Submit Iniziale
        window.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const errType = params.get('err');
            const successMsg = params.get('success');
            
            if (errType === 'domenica') mostraErrore('Giorno Festivo', 'Impossibile prenotare. La giornata selezionata è di Domenica.');
            else if (errType === 'limite_raggiunto') mostraErrore('Limite Raggiunto', 'Hai superato il numero massimo di prenotazioni consentite per questa data.');
            else if (errType === 'occupato') mostraErrore('Risorsa Occupata', 'Questa risorsa è già stata prenotata negli orari selezionati.');
            
            if (successMsg === '1') mostraSuccesso('Ottimo!', 'Prenotazione confermata e registrata con successo nel sistema.');
        });
    </script>
</body>
</html>