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

// --- QUERY PER STATISTICHE ---

// 1. Prenotazioni Totali
$q_tot = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata'");
$stat_totali = $q_tot ? $q_tot->fetch_assoc()['c'] : 0;

// 2. In Arrivo (Data futura o oggi ma non ancora finita)
$q_arr = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND CONCAT(data_prenotazione, ' ', ora_fine) > NOW()");
$stat_in_arrivo = $q_arr ? $q_arr->fetch_assoc()['c'] : 0;

// 3. Questo Mese
$q_mese = $conn->query("SELECT COUNT(*) as c FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND MONTH(data_prenotazione) = MONTH(CURRENT_DATE()) AND YEAR(data_prenotazione) = YEAR(CURRENT_DATE())");
$stat_mese = $q_mese ? $q_mese->fetch_assoc()['c'] : 0;

// 4. Postazione Più Usata
$q_top = $conn->query("SELECT a.nome, COUNT(*) as c FROM prenotazioni p JOIN asset a ON p.asset_id = a.id WHERE p.user_id = $user_id AND p.stato != 'annullata' GROUP BY a.id ORDER BY c DESC LIMIT 1");
$stat_postazione_top = ($q_top && $q_top->num_rows > 0) ? $q_top->fetch_assoc()['nome'] : "Nessuna";

// --- PRENOTAZIONI IN ARRIVO (Lista) ---
$mesi_it = [1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre'];
$tipi_label = ['base' => 'Scrivania Base', 'tech' => 'Scrivania Tech', 'meeting' => 'Sala Riunioni', 'parking' => 'Posto Auto'];

$prenotazioni_in_arrivo = [];
$q_list = $conn->query("
    SELECT p.data_prenotazione, p.ora_inizio, p.ora_fine, a.nome, a.tipo 
    FROM prenotazioni p 
    JOIN asset a ON p.asset_id = a.id 
    WHERE p.user_id = $user_id AND p.stato != 'annullata' AND CONCAT(p.data_prenotazione, ' ', p.ora_fine) > NOW() 
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

// --- ATTIVITÀ (Dati Grafico Dinamici) ---
$attivita_dati = [
    'settimana' => ['Lunedì' => 0, 'Martedì' => 0, 'Mercoledì' => 0, 'Giovedì' => 0, 'Venerdì' => 0],
    'mese' => ['1ª Sett' => 0, '2ª Sett' => 0, '3ª Sett' => 0, '4ª Sett' => 0],
    // Raggruppamento per Trimestri esplicito
    'anno' => ['Gen-Mar' => 0, 'Apr-Giu' => 0, 'Lug-Set' => 0, 'Ott-Dic' => 0]
];

$q_att = $conn->query("SELECT data_prenotazione FROM prenotazioni WHERE user_id = $user_id AND stato != 'annullata' AND YEAR(data_prenotazione) = YEAR(CURRENT_DATE())");

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
            $day_w = date('N', $ts); // 1 (Lun) to 7 (Dom)
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
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1400px] flex flex-col gap-8">

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
                    
                    <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">DashBoard</a>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 w-full">
            <div class="bg-card-1 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-[#F2F7FF] mb-2 leading-none drop-shadow-md"><?php echo $stat_totali; ?></div>
                <div class="text-[12px] font-bold text-[#CDE3F7] uppercase tracking-wider">Prenotazioni totali</div>
            </div>
            <div class="bg-card-2 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-[#F4F2FF] mb-2 leading-none drop-shadow-md"><?php echo $stat_in_arrivo; ?></div>
                <div class="text-[12px] font-bold text-white/60 uppercase tracking-wider">In arrivo</div>
            </div>
            <div class="bg-card-3 glass-panel rounded-[24px] p-8 flex flex-col justify-center transition-transform hover:-translate-y-1">
                <div class="text-[48px] font-black text-white mb-2 leading-none drop-shadow-md"><?php echo $stat_mese; ?></div>
                <div class="text-[12px] font-bold text-white/70 uppercase tracking-wider">Questo mese</div>
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
                        <?php foreach ($attivita_dati as $periodo => $dati_periodo): 
                            $max_val = max($dati_periodo) ?: 1;
                            $hide_class = ($periodo === 'settimana') ? '' : 'hidden opacity-0';
                        ?>
                            <div id="dati-<?php echo $periodo; ?>" class="attivita-container flex-col gap-5 absolute inset-0 w-full h-full flex justify-center transition-opacity duration-300 <?php echo $hide_class; ?>">
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