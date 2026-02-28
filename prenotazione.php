<?php
session_start();

// Controllo sicurezza
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

require_once 'db.php';

// RECUPERO DATI SESSIONE
$nomeUtente = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Utente';
$ruoloUtente = strtolower(trim(isset($_SESSION['user_ruolo']) ? $_SESSION['user_ruolo'] : 'dipendente'));

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

// --- INTEGRAZIONE DB BACKEND E RISOLUZIONE ERRORE ---
$assets_info = [];
$occupati = [];

// Recupera informazioni su tutti gli asset compreso l'ID numerico reale
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

// Controlla prenotazioni già esistenti (JOIN per recuperare il codice univoco invece dell'ID)
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
// -----------------------------

// FUNZIONE OTTIMIZZATA PER CARICARE GLI SVG VELOCEMENTE
function getSvgRapido($path) {
    return file_exists($path) ? file_get_contents($path) : '<div class="text-xs text-white/50">N/A</div>';
}

$sezioni = [
    'meeting' => ['icon' => getSvgRapido('src/Icone/Riunioni.svg'), 'nome' => 'Sale Riunioni', 'desc' => 'Sala attrezzata'],
    'base' => ['icon' => getSvgRapido('src/Icone/PostazioneBase.svg'), 'nome' => 'Scrivania Base', 'desc' => 'Scrivania + Cassettiera'],
    'tech' => ['icon' => getSvgRapido('src/Icone/PostazioneTech.svg'), 'nome' => 'Scrivania Tech', 'desc' => 'Scrivania + Monitor Extra'],
    'parking' => ['icon' => getSvgRapido('src/Icone/PostoAuto.svg'), 'nome' => 'Posto Auto', 'desc' => 'Parcheggio interrato']
];

$nomi_mappe = ['piano1' => 'Piano 1', 'piano2' => 'Piano 2', 'parking' => 'Parcheggio'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione - LubooZucchetti</title>
    <style>
        /* CSS per Mappa Interattiva integrato senza rompere Tailwind */
        .svg-slot { fill: #36A482; cursor: pointer; transition: fill 0.2s; } /* Verde - Disponibile */
        .svg-slot:hover { filter: brightness(1.2); }
        .svg-slot.occupato { fill: #ef4444; pointer-events: none; } /* Rosso - Occupato */
        .svg-slot.selezionato { fill: #ffffff; } /* Bianco - Selezionato */

        .png-risorsa { background-color: #36A482; cursor: pointer; transition: 0.2s; mask-size: contain; -webkit-mask-size: contain; mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; }
        .png-risorsa:hover { filter: brightness(1.2); }
        .png-risorsa.occupato { background-color: #ef4444; pointer-events: none; }
        .png-risorsa.selezionato { background-color: #ffffff; }

        .parcheggio-grid { display: grid; grid-template-columns: repeat(5, 40px); gap: 10px; justify-content: center; align-items: center; width: 100%; height: 100%; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1400px] flex flex-col gap-6">
        
        <header class="relative mb-2">
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
                </nav>

                <div class="hidden md:flex items-center gap-3 text-[#BFD6E8] text-xs font-semibold mr-2">
                    <a href="#" class="hover:text-white transition-colors uppercase">Modifica</a>
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
                    <?php foreach ($sezioni as $id => $info): ?>
                    <div data-cat-id="<?php echo $id; ?>" class="sez-item relative p-4 rounded-2xl transition-all flex items-center gap-3 bg-[rgba(255,255,255,0.05)] border border-white/5 cursor-default">
                        
                        <div class="w-8 h-8 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                            <?php echo $info['icon']; ?>
                        </div>

                        <div class="flex-1">
                            <div class="text-white font-bold text-sm leading-tight"><?php echo $info['nome']; ?></div>
                            <div class="text-white/80 text-[10px] mt-0.5 leading-none"><?php echo $info['desc']; ?></div>
                        </div>
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
                    <div class="relative">
                        <select id="select-piano" onchange="cambiaPiano(this.value)" class="appearance-none pl-5 pr-10 py-2 bg-[rgba(198,101,213,0.6)] border border-white/20 rounded-[19px] text-white font-bold text-sm hover:bg-[rgba(198,101,213,0.8)] transition-colors shadow-lg cursor-pointer outline-none focus:ring-2 focus:ring-white/50">
                            <option value="piano1" class="text-black" <?php echo ($mappa_attiva === 'piano1') ? 'selected' : ''; ?>>📍 Piano 1 (Base/Tech/Sale)</option>
                            <option value="piano2" class="text-black" <?php echo ($mappa_attiva === 'piano2') ? 'selected' : ''; ?>>📍 Piano 2 (Tech/Sale/Base)</option>
                            <option value="parking" class="text-black" <?php echo ($mappa_attiva === 'parking') ? 'selected' : ''; ?>>🅿️ Piano Interrato (Parcheggi)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <h2 class="text-xl font-black text-white drop-shadow-md text-right"><?php echo isset($nomi_mappe[$mappa_attiva]) ? $nomi_mappe[$mappa_attiva] : 'Planimetria'; ?></h2>
                </div>

                <div class="flex-grow w-full rounded-[20px] bg-[#071B2B]/40 border border-white/5 relative overflow-hidden flex justify-center items-center">
                    
                    <?php if ($mappa_attiva === 'piano1'): ?>
                        
                        <div class="absolute flex items-center justify-center gap-6" style="top: 10%; left: 50%; transform: translateX(-50%); width: 85%; height: 90px; background-color: rgba(30,58,138,0.4); border-radius: 15px;">
                            <?php for($i=1; $i<=5; $i++): 
                                $cid = "room-$i";
                                $db_id = isset($assets_info[$cid]) ? $assets_info[$cid]['id'] : '';
                                $occ_class = in_array($cid, $occupati) ? 'occupato' : '';
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" style="width: 70px; height: 55px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute" style="top: 35%; left: 50%; transform: translateX(-50%);">
                            <svg width="280" height="130" viewBox="0 0 280 130">
                                <rect width="280" height="130" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php
                                $tech_slots = [
                                    ['cx'=>50, 'cy'=>40, 'id'=>'desk-t-1'], ['cx'=>140, 'cy'=>40, 'id'=>'desk-t-2'], ['cx'=>230, 'cy'=>40, 'id'=>'desk-t-3'],
                                    ['cx'=>50, 'cy'=>90, 'id'=>'desk-t-4'], ['cx'=>140, 'cy'=>90, 'id'=>'desk-t-5'], ['cx'=>230, 'cy'=>90, 'id'=>'desk-t-6']
                                ];
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
                            <svg width="420" height="80" viewBox="0 0 420 80">
                                <rect width="420" height="80" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=1; $i<=5; $i++) $base_slots[] = ['cx'=> 45 + ($i-1)*82, 'cy'=>40, 'id'=>"desk-b-$i"];
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
                                $locker = isset($assets_info[$cid]) ? $assets_info[$cid]['armadietto'] : 'N/A';
                                $nome = isset($assets_info[$cid]) ? $assets_info[$cid]['nome'] : "Sala $i";
                            ?>
                            <div class="png-risorsa risorsa-item <?php echo $occ_class; ?>" data-dbid="<?php echo $db_id; ?>" data-id="<?php echo $cid; ?>" data-tipo="meeting" data-locker="<?php echo $locker; ?>" data-nome="<?php echo $nome; ?>" style="width: 60px; height: 50px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                            <?php endfor; ?>
                        </div>

                        <div class="absolute" style="top: 40%; left: 45%; transform: translateX(-50%);">
                            <svg width="280" height="90" viewBox="0 0 280 90">
                                <rect width="280" height="90" rx="15" ry="15" fill="#FFA500" opacity="0.8" />
                                <?php
                                $tech_slots = [
                                    ['cx'=>50, 'cy'=>45, 'id'=>'desk-t-7'], ['cx'=>140, 'cy'=>45, 'id'=>'desk-t-8'], ['cx'=>230, 'cy'=>45, 'id'=>'desk-t-9']
                                ];
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
                            <svg width="400" height="80" viewBox="0 0 400 80">
                                <rect width="400" height="80" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=6; $i<=10; $i++) $base_slots[] = ['cx'=> 40 + ($i-6)*80, 'cy'=>40, 'id'=>"desk-b-$i"];
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
                            <svg width="80" height="340" viewBox="0 0 80 340">
                                <rect width="80" height="340" rx="15" ry="15" fill="#36A482" opacity="0.8" />
                                <?php
                                $base_slots = [];
                                for($i=11; $i<=14; $i++) $base_slots[] = ['cx'=> 40, 'cy'=> 50 + ($i-11)*80, 'id'=>"desk-b-$i"];
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
                                    $is_disabile = ($posto >= 4); // Assicura la distinzione per disabili graficamente
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
                <h2 class="text-xl font-bold text-white mb-4">Dettagli</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-500/80 text-white p-3 rounded-xl mb-4 text-center font-bold text-sm shadow">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-[#36A482] text-white p-3 rounded-xl mb-4 text-center font-bold text-sm shadow">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <form id="booking-form" action="salva_prenotazione.php" method="POST" class="flex flex-col flex-grow gap-4">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Asset</label>
                            <div class="input-bg rounded-xl px-2 py-3 border border-white/10 shadow-inner text-center overflow-hidden">
                                <span class="text-white font-black text-sm whitespace-nowrap" id="display-seat">-</span>
                                <input type="hidden" name="asset_id" id="input-asset-id" required>
                                <input type="hidden" name="tipo_risorsa" id="input-tipo-risorsa">
                            </div>
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Locker</label>
                            <div class="bg-[rgba(99,165,180,0.84)] rounded-xl px-2 py-3 border border-white/10 shadow-inner text-center overflow-hidden">
                                <span class="text-white font-black text-sm whitespace-nowrap" id="display-locker">-</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Data</label>
                        <input type="date" name="data" id="input-data" value="<?php echo $oggi; ?>" required 
                               class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white text-md font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Inizio</label>
                            <input type="time" name="inizio" id="input-inizio" value="<?php echo $ora_inizio; ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white text-md font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Fine</label>
                            <input type="time" name="fine" id="input-fine" value="<?php echo $ora_fine; ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white text-md font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                    </div>

                    <div class="mt-auto pt-4">
                        <button type="submit" id="btn-submit" 
                                class="w-full py-4 px-4 rounded-xl font-black text-white text-md uppercase tracking-wider transition-all border border-white/10 shadow-xl bg-[rgba(180,123,99,0.95)] hover:brightness-110 hover:scale-[1.02]">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function cambiaPiano(valoreMappa) {
            const params = new URLSearchParams(window.location.search);
            params.set('mappa', valoreMappa);
            params.set('data', document.getElementById('input-data').value);
            params.set('inizio', document.getElementById('input-inizio').value);
            params.set('fine', document.getElementById('input-fine').value);
            window.location.href = window.location.pathname + '?' + params.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const inputData = document.getElementById('input-data');
            const inputInizio = document.getElementById('input-inizio');
            const inputFine = document.getElementById('input-fine');

            function ricaricaDisponibilita() {
                cambiaPiano('<?php echo $mappa_attiva; ?>');
            }

            inputData.addEventListener('change', ricaricaDisponibilita);
            inputInizio.addEventListener('change', ricaricaDisponibilita);
            inputFine.addEventListener('change', ricaricaDisponibilita);

            // Logica Selezione Risorse e Caricamento Dinamico
            document.querySelectorAll('.risorsa-item').forEach(item => {
                item.addEventListener('click', function() {
                    if(this.classList.contains('occupato')) return; // Non cliccare se occupato
                    
                    const r_dbid = this.getAttribute('data-dbid');
                    if(!r_dbid) {
                        alert("ERRORE: Questa postazione visuale non è ancora stata creata all'interno del Database SQL. Impossibile prenotare.");
                        return;
                    }

                    // Rimuove selezione precedente dalla mappa
                    document.querySelectorAll('.risorsa-item.selezionato').forEach(el => el.classList.remove('selezionato'));
                    // Applica selezione
                    this.classList.add('selezionato');

                    // Prendi i dati dai data-attributes caricati dal PHP
                    const r_id = this.getAttribute('data-id');
                    const r_nome = this.getAttribute('data-nome') || r_id;
                    const r_locker = this.getAttribute('data-locker') || 'N/A';
                    const r_tipo = this.getAttribute('data-tipo') || '';
                    
                    // 1. Sincronizza l'interfaccia a sinistra (Sidebar attiva dinamicamente)
                    document.querySelectorAll('.sez-item').forEach(el => {
                        el.classList.remove('bg-gradient-to-r', 'from-[#3f8718]', 'to-[#5fa831]', 'shadow-lg', 'scale-[1.02]', 'border-white/20');
                        el.classList.add('bg-[rgba(255,255,255,0.05)]', 'border-white/5');
                    });
                    const activeSidebarItem = document.querySelector(`.sez-item[data-cat-id="${r_tipo}"]`);
                    if(activeSidebarItem) {
                        activeSidebarItem.classList.remove('bg-[rgba(255,255,255,0.05)]', 'border-white/5');
                        activeSidebarItem.classList.add('bg-gradient-to-r', 'from-[#3f8718]', 'to-[#5fa831]', 'shadow-lg', 'scale-[1.02]', 'border-white/20');
                    }

                    // 2. Aggiorna Input Nascosti per la POST di salva_prenotazione.php (Manda l'ID Reale!)
                    document.getElementById('input-asset-id').value = r_dbid;
                    if(document.getElementById('input-tipo-risorsa')) {
                        document.getElementById('input-tipo-risorsa').value = r_tipo;
                    }
                    
                    // Mostra all'utente a Schermo
                    document.getElementById('display-seat').innerText = r_nome;
                    document.getElementById('display-locker').innerText = r_locker;
                });
            });
        });
    </script>
</body>
</html>