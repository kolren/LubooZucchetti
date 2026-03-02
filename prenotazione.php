<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

require_once 'db.php';

// RECUPERO DATI SESSIONE
$utente_id = $_SESSION['user_id'];
$nomeUtente = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Utente';

// Ruolo sicuro dal DB
$stmt_me = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_me->bind_param("i", $utente_id);
$stmt_me->execute();
$me_data = $stmt_me->get_result()->fetch_assoc();
$ruoloUtente = strtolower(trim($me_data['role'] ?? 'dipendente'));

// Configurazione Stili Dinamici Navbar
$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]'],   
    'dipendente' => ['badge_bg' => '#6aa70f', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#4D7C0F_0%,#6AA70F_100%)]']      
];
$roleTheme = array_key_exists($ruoloUtente, $themeColors) ? $themeColors[$ruoloUtente] : $themeColors['dipendente'];

// DATI PRENOTAZIONE DI DEFAULT O DA GET
$oggi = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$ora_inizio = isset($_GET['inizio']) ? $_GET['inizio'] : '09:00';
$ora_fine = isset($_GET['fine']) ? $_GET['fine'] : '18:00';
$mappa_attiva = isset($_GET['mappa']) ? $_GET['mappa'] : 'piano1'; 

// --- INTEGRAZIONE DB BACKEND ---
$assets_info = [];
$occupati = [];

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

// Controlla prenotazioni altrui per colorare di rosso
if (!empty($oggi) && !empty($ora_inizio) && !empty($ora_fine)) {
    $d = $conn->real_escape_string($oggi);
    $i = $conn->real_escape_string($ora_inizio);
    $f = $conn->real_escape_string($ora_fine);
    
    $query_occ = "SELECT a.codice_univoco FROM prenotazioni p 
                  JOIN asset a ON p.asset_id = a.id
                  WHERE p.data_prenotazione = '$d' AND p.stato != 'annullata' 
                  AND ((p.ora_inizio < '$f' AND p.ora_fine > '$i'))";
    $res_occ = $conn->query($query_occ);
    if ($res_occ) {
        while ($row = $res_occ->fetch_assoc()) {
            $occupati[] = $row['codice_univoco'];
        }
    }
}

// CONTEGGIO PRENOTAZIONI ODIERNE DELL'UTENTE (per logica Frontend No-Refresh)
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
        .svg-slot { fill: #36A482; cursor: pointer; transition: all 0.2s; } 
        .svg-slot:hover { filter: brightness(1.2); transform: scale(1.05); }
        .svg-slot.occupato { fill: #ef4444; pointer-events: none; } 
        .svg-slot.selezionato { fill: #ffffff; } 

        .png-risorsa { background-color: #36A482; cursor: pointer; transition: all 0.2s; mask-size: contain; -webkit-mask-size: contain; mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; }
        .png-risorsa:hover { filter: brightness(1.2); transform: scale(1.05); }
        .png-risorsa.occupato { background-color: #ef4444; pointer-events: none; }
        .png-risorsa.selezionato { background-color: #ffffff; }

        /* Stile specifico per limitazioni dipendenti (blur UI) */
        .risorsa-vietata { opacity: 0.4; filter: blur(2px) grayscale(50%); cursor: not-allowed !important; }
        .risorsa-vietata:hover { filter: blur(0px) grayscale(0%); transform: none; }

        .parcheggio-grid { display: grid; grid-template-columns: repeat(5, 40px); gap: 10px; justify-content: center; align-items: center; width: 100%; height: 100%; }
        
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; filter: invert(1);}
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF] relative">

    <div class="w-full max-w-[1400px] flex flex-col gap-6">
        
        <header class="relative">
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
                    <?php if (in_array($ruoloUtente, ['amministratore', 'coordinatore'])): ?>
                    <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                    <?php endif; ?>
                    
                    <a href="prenotazione.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">DashBoard</a>
                    <a href="gestisci.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Gestisci</a>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch min-h-[650px]">
            
            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-bold bg-gradient-to-r from-white to-[#ADD0FF] bg-clip-text text-transparent">Prenotazioni disponibili</h2>
                </div>
                
                <div class="flex flex-col gap-3 mb-8">
                    <?php foreach ($sezioni as $id => $info): 
                        // Sfumatura UI per le sezioni vietate
                        $is_vietato = ($ruoloUtente === 'dipendente' && $id === 'meeting');
                        $opacita = $is_vietato ? 'opacity-40 blur-[1px]' : '';
                    ?>
                    <div data-cat-id="<?php echo $id; ?>" class="sez-item relative p-4 rounded-2xl transition-all flex items-center gap-3 bg-[rgba(255,255,255,0.05)] border border-white/5 <?php echo $opacita; ?>">
                        
                        <div class="w-8 h-8 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                            <?php echo $info['icon']; ?>
                        </div>

                        <div class="flex-1">
                            <div class="text-white font-bold text-sm leading-tight"><?php echo $info['nome']; ?></div>
                            <div class="text-white/80 text-[10px] mt-0.5 leading-none"><?php echo $info['desc']; ?></div>
                        </div>
                        
                        <?php if($is_vietato): ?>
                            <div class="absolute inset-0 z-10" onclick="mostraErrore('Permesso Negato', 'Il tuo ruolo da Dipendente non ti consente di prenotare le Sale Riunioni.')" style="cursor: not-allowed;"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-auto bg-[#071B2B]/30 p-4 rounded-2xl border border-white/5">
                    <h3 class="text-sm font-bold text-white mb-3">Legenda</h3>
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#36A482] rounded-full"></div><span class="text-white/90">Disponibile</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#ef4444] rounded-full"></div><span class="text-white/90">Occupato</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-white rounded-full border border-gray-400"></div><span class="text-white/90">Selezionato</span></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 ui-panel glass-panel p-6 flex flex-col relative overflow-hidden shadow-2xl">
                
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="bg-white/5 px-4 py-2 rounded-xl border border-white/10 flex items-center gap-3 shadow-sm">
                        <div class="text-[#BFD6E8] text-xs font-bold uppercase tracking-wide">Disponibili:</div>
                        <div class="text-xl font-black text-white">
                            <span id="posti_disponibili" class="text-[#36A482]">-</span> / <span id="posti_totali" class="text-white">-</span>
                        </div>
                    </div>

                    <div class="relative flex items-center group cursor-pointer">
                        <select id="select-piano" onchange="cambiaPiano(this.value)" class="appearance-none bg-transparent border-none text-xl font-black text-white cursor-pointer pr-6 focus:ring-0 outline-none text-right drop-shadow-md tracking-wide">
                            <option value="piano1" class="text-black" <?php echo ($mappa_attiva === 'piano1') ? 'selected' : ''; ?>>Piano 1</option>
                            <option value="piano2" class="text-black" <?php echo ($mappa_attiva === 'piano2') ? 'selected' : ''; ?>>Piano 2</option>
                            <option value="parking" class="text-black" <?php echo ($mappa_attiva === 'parking') ? 'selected' : ''; ?>>Parcheggio</option>
                        </select>
                        <div class="pointer-events-none absolute right-0 flex items-center text-white">
                            <svg class="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-grow w-full rounded-[20px] bg-[#071B2B]/40 border border-white/5 relative overflow-hidden flex justify-center items-center">
                    
                    <?php if ($mappa_attiva === 'piano1'): ?>
                        
                        <div class="absolute flex items-center justify-center gap-6" style="top: 10%; left: 50%; transform: translateX(-50%); width: 85%; height: 90px; background-color: rgba(30,58,138,0.4); border-radius: 15px;">
                            <?php for($i=1; $i<=5; $i++): 
                                $cid = "room-$i";
                                $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                $vietato_class = ($ruoloUtente === 'dipendente') ? 'risorsa-vietata' : '';
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class . ' ' . $vietato_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" style="width: 70px; height: 55px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute" style="top: 35%; left: 50%; transform: translateX(-50%);">
                            <svg width="480" height="130" viewBox="0 0 480 130">
                                <rect width="480" height="130" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php
                                $tech_slots = [];
                                for($i=1; $i<=6; $i++) $tech_slots[] = ['cx'=> 40 + ($i-1)*80, 'cy'=>40, 'id'=>"desk-t-$i"];
                                for($i=7; $i<=12; $i++) $tech_slots[] = ['cx'=> 40 + ($i-7)*80, 'cy'=>90, 'id'=>"desk-t-$i"];
                                foreach($tech_slots as $slot) {
                                    $cid = $slot['id'];
                                    $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                    $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='14' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='tech' data-locker='{$locker}' data-nome='{$nome}'/>";
                                }
                                ?>
                            </svg>
                        </div>

                        <div class="absolute" style="top: 75%; left: 50%; transform: translateX(-50%);">
                            <svg width="560" height="130" viewBox="0 0 560 130">
                                <rect width="560" height="130" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=1; $i<=7; $i++) $base_slots[] = ['cx'=> 40 + ($i-1)*80, 'cy'=>40, 'id'=>"desk-b-$i"];
                                for($i=8; $i<=14; $i++) $base_slots[] = ['cx'=> 40 + ($i-8)*80, 'cy'=>90, 'id'=>"desk-b-$i"];
                                foreach($base_slots as $slot) {
                                    $cid = $slot['id'];
                                    $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                    $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='14' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='base' data-locker='{$locker}' data-nome='{$nome}'/>";
                                }
                                ?>
                            </svg>
                        </div>

                    <?php elseif ($mappa_attiva === 'piano2'): ?>

                        <div class="absolute flex items-center justify-center gap-6" style="top: 10%; left: 45%; transform: translateX(-50%); width: 75%; height: 90px; background-color: rgba(30,58,138,0.4); border-radius: 15px;">
                            <?php for($i=6; $i<=10; $i++): 
                                $cid = "room-$i";
                                $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                $vietato_class = ($ruoloUtente === 'dipendente') ? 'risorsa-vietata' : '';
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class . ' ' . $vietato_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" style="width: 60px; height: 50px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute" style="top: 35%; left: 45%; transform: translateX(-50%);">
                            <svg width="340" height="130" viewBox="0 0 340 130">
                                <rect width="340" height="130" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php
                                $tech_slots = [];
                                for($i=13; $i<=16; $i++) $tech_slots[] = ['cx'=> 40 + ($i-13)*86, 'cy'=>40, 'id'=>"desk-t-$i"];
                                for($i=17; $i<=20; $i++) $tech_slots[] = ['cx'=> 40 + ($i-17)*86, 'cy'=>90, 'id'=>"desk-t-$i"];
                                foreach($tech_slots as $slot) {
                                    $cid = $slot['id'];
                                    $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                    $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='14' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='tech' data-locker='{$locker}' data-nome='{$nome}'/>";
                                }
                                ?>
                            </svg>
                        </div>

                        <div class="absolute" style="top: 75%; left: 45%; transform: translateX(-50%);">
                            <svg width="420" height="130" viewBox="0 0 420 130">
                                <rect width="420" height="130" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=15; $i<=19; $i++) $base_slots[] = ['cx'=> 40 + ($i-15)*85, 'cy'=>40, 'id'=>"desk-b-$i"];
                                for($i=20; $i<=24; $i++) $base_slots[] = ['cx'=> 40 + ($i-20)*85, 'cy'=>90, 'id'=>"desk-b-$i"];
                                foreach($base_slots as $slot) {
                                    $cid = $slot['id'];
                                    $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                    $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='14' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='base' data-locker='{$locker}' data-nome='{$nome}'/>";
                                }
                                ?>
                            </svg>
                        </div>

                        <div class="absolute" style="top: 15%; right: 4%;">
                            <svg width="80" height="490" viewBox="0 0 80 490">
                                <rect width="80" height="490" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=25; $i<=30; $i++) $base_slots[] = ['cx'=> 40, 'cy'=> 45 + ($i-25)*80, 'id'=>"desk-b-$i"];
                                foreach($base_slots as $slot) {
                                    $cid = $slot['id'];
                                    $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                    $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : $cid;
                                    echo "<circle cx='{$slot['cx']}' cy='{$slot['cy']}' r='14' class='svg-slot risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$cid}' data-tipo='base' data-locker='{$locker}' data-nome='{$nome}'/>";
                                }
                                ?>
                            </svg>
                        </div>

                    <?php elseif ($mappa_attiva === 'parking'): ?>
                        <div class="parcheggio-grid z-10 w-full h-full p-8 relative top-4">
                            <?php
                            $contatore = 1;
                            for ($fila = 1; $fila <= 5; $fila++) {
                                for ($posto = 1; $posto <= 5; $posto++) {
                                    $id_posto = "park-" . $contatore;
                                    $is_disabile = ($posto >= 4); 
                                    $mask_url = $is_disabile ? 'src/AssetMappa/PostoAutoDisabili.png' : 'src/AssetMappa/PostoAuto.png';
                                    
                                    $db_id = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['id'] : '';
                                    $occ_class = in_array($id_posto, $occupati) ? 'occupato' : '';
                                    $locker = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['armadietto'] : 'N/A';
                                    $nome = isset($assets_info[$id_posto]) ? $assets_info[$id_posto]['nome'] : "Parcheggio {$contatore}";
                                    
                                    echo "<div class='png-risorsa risorsa-item {$occ_class}' data-dbid='{$db_id}' data-id='{$id_posto}' data-tipo='parking' data-locker='{$locker}' data-nome='{$nome}' style='width:38px; height:65px; -webkit-mask-image: url(\"{$mask_url}\"); mask-image: url(\"{$mask_url}\");'></div>";
                                    $contatore++;
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
                <h2 class="text-xl font-bold text-white mb-6 tracking-wide uppercase text-center border-b border-white/10 pb-4">Conferma Prenotazione</h2>
                
                <form id="booking-form" action="salva_prenotazione.php" method="POST" class="flex flex-col flex-grow">
                    
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-[#BFD6E8] mb-1 uppercase tracking-wider">Posizione</label>
                            <div class="w-full bg-[#0A2338] text-white px-4 py-3 rounded-xl border border-white/10 font-semibold text-center overflow-hidden">
                                <span id="display-seat" class="whitespace-nowrap">-</span>
                                <input type="hidden" name="asset_id" id="input-asset-id" required>
                                <input type="hidden" name="tipo_risorsa" id="input-tipo-risorsa">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#BFD6E8] mb-1 uppercase tracking-wider">Armadietto</label>
                            <div class="w-full bg-[#0A2338] text-white px-4 py-3 rounded-xl border border-white/10 font-semibold text-center overflow-hidden">
                                <span id="display-locker" class="whitespace-nowrap">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                        <div class="text-sm font-bold text-[#BFD6E8] mb-2 uppercase tracking-wide">Data e Orario</div>
                        <input type="date" name="data" id="input-data" value="<?php echo $oggi; ?>" required 
                               class="bg-transparent text-white font-black text-center w-full focus:outline-none mb-1 text-lg">
                        <div class="flex items-center justify-center gap-2 text-[#36A482] font-bold text-lg">
                            <input type="time" name="inizio" id="input-inizio" value="<?php echo $ora_inizio; ?>" required class="bg-transparent focus:outline-none text-center w-24">
                            <span>-</span>
                            <input type="time" name="fine" id="input-fine" value="<?php echo $ora_fine; ?>" required class="bg-transparent focus:outline-none text-center w-24">
                        </div>
                    </div>

                    <div class="mt-auto pt-4 flex flex-col items-center">
                        <div id="icona-selezionata-container" class="mb-4 h-16 w-16 bg-white/5 rounded-2xl flex items-center justify-center text-white/30 border border-white/10 transition-all shadow-inner">
                            <span class="text-xs">N/A</span>
                        </div>

                        <button type="submit" id="btn-submit" disabled
                                class="w-full py-4 px-4 rounded-xl font-black text-white text-md uppercase tracking-wider transition-all border border-white/10 shadow-xl bg-[#36A482] hover:brightness-110 hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <div id="modal-errore" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#071B2B]/80 backdrop-blur-md transition-opacity opacity-0 duration-300 modal-backdrop" onclick="chiudiModal('modal-errore')"></div>
        <div class="bg-main border border-red-500/30 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-16 h-16 mx-auto bg-red-500/10 rounded-full flex items-center justify-center mb-4 text-red-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 id="errore-titolo" class="text-xl font-black text-white mb-2 uppercase tracking-wide">Attenzione</h3>
            <p id="errore-msg" class="text-[#BFD6E8] text-sm mb-8 font-medium"></p>
            <button type="button" onclick="chiudiModal('modal-errore')" class="w-full py-3.5 rounded-xl border border-white/20 text-white hover:bg-white/10 transition-all font-bold uppercase tracking-wider text-sm shadow-md">Chiudi</button>
        </div>
    </div>

    <div id="modal-successo" class="fixed inset-0 z-[100] hidden items-center justify-center">
        <div class="absolute inset-0 bg-[#071B2B]/90 backdrop-blur-md transition-opacity opacity-0 duration-300 modal-backdrop"></div>
        <div class="bg-main border border-[#36A482]/50 p-8 rounded-[24px] shadow-2xl z-10 w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative modal-content text-center">
            <div class="w-20 h-20 mx-auto bg-[#36A482]/20 rounded-full flex items-center justify-center mb-6 text-[#36A482] animate-pulse">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-wide">Prenotazione Confermata!</h3>
            <p id="successo-msg" class="text-[#BFD6E8] text-sm mb-8 font-medium">La tua postazione è stata riservata correttamente.</p>
            
            <div class="flex flex-col gap-3">
                <a href="gestisci.php" class="w-full bg-[#36A482] hover:bg-[#2b8569] py-3.5 rounded-xl text-white shadow-lg transition-all font-bold uppercase tracking-wider text-sm border border-[#36A482]/50">Gestisci le tue prenotazioni</a>
                <button type="button" onclick="chiudiModal('modal-successo')" class="w-full py-3.5 rounded-xl border border-white/20 text-white hover:bg-white/10 transition-all font-bold uppercase tracking-wider text-sm shadow-md">Prenota Ancora</button>
            </div>
        </div>
    </div>

    <script>
        const iconeSVG = <?php 
            $icone_js = [];
            foreach($sezioni as $k => $v) { $icone_js[$k] = $v['icon']; }
            echo json_encode($icone_js);
        ?>;
        
        // Dati iniettati dal PHP per i controlli Frontend "No Refresh"
        const prenotaOggi = <?php echo json_encode($prenotazioni_oggi); ?>;
        const ruoloUtente = '<?php echo $ruoloUtente; ?>';

        // GESTIONE MODALI ANIMATI
        function mostraErrore(titolo, messaggio) {
            document.getElementById('errore-titolo').innerText = titolo;
            document.getElementById('errore-msg').innerText = messaggio;
            apriModal('modal-errore');
        }

        function mostraSuccesso(messaggio) {
            if(messaggio) document.getElementById('successo-msg').innerText = messaggio;
            apriModal('modal-successo');
        }

        function apriModal(modalId) {
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
                
                // Se era successo, ripulisci l'URL per pulizia
                if(modalId === 'modal-successo' || modalId === 'modal-errore') {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }, 300);
        }

        // AUTO-TRIGGER MODALI IN BASE A GET URL (ritorno da backend fallback)
        <?php if(isset($_GET['error'])): ?>
            mostraErrore('Errore di validazione', '<?php echo addslashes($_GET['error']); ?>');
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
            mostraSuccesso('Hai prenotato con successo <?php echo addslashes($_GET['asset_nome'] ?? "la risorsa"); ?>.');
        <?php endif; ?>


        function cambiaPiano(valoreMappa) {
            const params = new URLSearchParams(window.location.search);
            params.set('mappa', valoreMappa);
            params.set('data', document.getElementById('input-data').value);
            params.set('inizio', document.getElementById('input-inizio').value);
            params.set('fine', document.getElementById('input-fine').value);
            window.location.href = window.location.pathname + '?' + params.toString();
        }

        function aggiornaContatori(tipoFiltro = null) {
            let totali = 0;
            let occupati = 0;
            document.querySelectorAll('.risorsa-item').forEach(el => {
                if (!tipoFiltro || el.getAttribute('data-tipo') === tipoFiltro) {
                    totali++;
                    if (el.classList.contains('occupato')) occupati++;
                }
            });
            document.getElementById('posti_totali').innerText = totali;
            document.getElementById('posti_disponibili').innerText = totali - occupati;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const inputData = document.getElementById('input-data');
            const inputInizio = document.getElementById('input-inizio');
            const inputFine = document.getElementById('input-fine');

            function ricaricaDisponibilita() { cambiaPiano('<?php echo $mappa_attiva; ?>'); }

            inputData.addEventListener('change', ricaricaDisponibilita);
            inputInizio.addEventListener('change', ricaricaDisponibilita);
            inputFine.addEventListener('change', ricaricaDisponibilita);

            aggiornaContatori();

            // Click su Risorse in Mappa
            document.querySelectorAll('.risorsa-item').forEach(item => {
                item.addEventListener('click', function() {
                    if(this.classList.contains('occupato')) return; 
                    
                    const r_tipo = this.getAttribute('data-tipo') || '';
                    
                    // Controllo click dipendente su meeting
                    if(this.classList.contains('risorsa-vietata') && r_tipo === 'meeting') {
                        mostraErrore('Accesso Negato', 'Il tuo ruolo da Dipendente non ti permette di prenotare le Sale Riunioni.');
                        return;
                    }

                    const r_dbid = this.getAttribute('data-dbid');
                    if(!r_dbid) {
                        alert("ERRORE: Asset mancante nel DB.");
                        return;
                    }

                    document.querySelectorAll('.risorsa-item.selezionato').forEach(el => el.classList.remove('selezionato'));
                    this.classList.add('selezionato');

                    const r_nome = this.getAttribute('data-nome') || this.getAttribute('data-id');
                    const r_locker = this.getAttribute('data-locker') || 'N/A';
                    
                    document.querySelectorAll('.sez-item').forEach(el => {
                        el.classList.remove('bg-gradient-to-r', 'from-[#3f8718]', 'to-[#5fa831]', 'shadow-lg', 'scale-[1.02]', 'border-white/20');
                        el.classList.add('bg-[rgba(255,255,255,0.05)]', 'border-white/5');
                    });
                    const activeSidebarItem = document.querySelector(`.sez-item[data-cat-id="${r_tipo}"]`);
                    if(activeSidebarItem) {
                        activeSidebarItem.classList.remove('bg-[rgba(255,255,255,0.05)]', 'border-white/5');
                        activeSidebarItem.classList.add('bg-gradient-to-r', 'from-[#3f8718]', 'to-[#5fa831]', 'shadow-lg', 'scale-[1.02]', 'border-white/20');
                    }

                    document.getElementById('input-asset-id').value = r_dbid;
                    document.getElementById('input-tipo-risorsa').value = r_tipo;
                    document.getElementById('display-seat').innerText = r_nome;
                    document.getElementById('display-locker').innerText = r_locker;

                    const iconContainer = document.getElementById('icona-selezionata-container');
                    if (iconeSVG[r_tipo]) {
                        iconContainer.innerHTML = `<div class="w-10 h-10 text-[#36A482] fill-current drop-shadow-md flex items-center justify-center">${iconeSVG[r_tipo]}</div>`;
                        iconContainer.classList.remove('text-white/30');
                    }

                    document.getElementById('btn-submit').disabled = false;
                    aggiornaContatori(r_tipo);
                });
            });

            // VALIDAZIONE FRONTEND (NO REFRESH) SUI LIMITI AL SUBMIT DEL FORM
            document.getElementById('booking-form').addEventListener('submit', function(e) {
                const tipo = document.getElementById('input-tipo-risorsa').value;
                const totScrivanieOggi = (prenotaOggi['base'] || 0) + (prenotaOggi['tech'] || 0);
                const parcheggiOggi = prenotaOggi['parking'] || 0;
                const meetingOggi = prenotaOggi['meeting'] || 0;

                if (ruoloUtente !== 'amministratore') {
                    // Controlli validi sia per Dipendente che per Coordinatore
                    if ((tipo === 'base' || tipo === 'tech') && totScrivanieOggi >= 1) {
                        e.preventDefault();
                        mostraErrore('Limite Raggiunto', 'Hai già prenotato 1 scrivania per questa giornata. Limite massimo raggiunto.');
                        return;
                    }
                    if (tipo === 'parking' && parcheggiOggi >= 1) {
                        e.preventDefault();
                        mostraErrore('Limite Raggiunto', 'Hai già prenotato 1 posto auto per questa giornata. Limite massimo raggiunto.');
                        return;
                    }

                    // Controlli specifici aggiuntivi
                    if (ruoloUtente === 'dipendente' && tipo === 'meeting') {
                        e.preventDefault();
                        mostraErrore('Accesso Negato', 'Non sei autorizzato a prenotare Sale Riunioni.');
                        return;
                    }

                    if (ruoloUtente === 'coordinatore' && tipo === 'meeting') {
                        if (meetingOggi >= 2) {
                            e.preventDefault();
                            mostraErrore('Limite Raggiunto', 'Hai già raggiunto il limite massimo di 2 Sale Riunioni per questa giornata.');
                            return;
                        }
                    }
                }
            });

        });
    </script>
</body>
</html>