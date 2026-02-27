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
$mappa_attiva = isset($_GET['mappa']) ? $_GET['mappa'] : 'base'; 

// FUNZIONE OTTIMIZZATA PER CARICARE GLI SVG VELOCEMENTE
function getSvgRapido($path) {
    return file_exists($path) ? file_get_contents($path) : '<div class="text-xs text-white/50">N/A</div>';
}

$sezioni = [
    'base' => ['icon' => getSvgRapido('src/Icone/PostazioneBase.svg'), 'nome' => 'Postazione Base', 'desc' => 'Scrivania + Cassettiera'],
    'tech' => ['icon' => getSvgRapido('src/Icone/PostazioneTech.svg'), 'nome' => 'Postazione Tech', 'desc' => 'Scrivania + Monitor Extra'],
    'meeting' => ['icon' => getSvgRapido('src/Icone/Riunioni.svg'), 'nome' => 'Sala Riunioni', 'desc' => 'Sala attrezzata'],
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
        /* CSS per Mappa Interattiva integrato senza rompere Tailwind */
        .svg-slot { fill: #36A482; cursor: pointer; transition: fill 0.2s; } /* Verde - Disponibile */
        .svg-slot:hover { filter: brightness(1.2); }
        .svg-slot.occupato { fill: #ef4444; pointer-events: none; } /* Rosso - Occupato */
        .svg-slot.selezionato { fill: #ffffff; } /* Bianco - Selezionato (come da tua legenda) */

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
                    <a href="gestisci.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Gestisci</a>
                </nav>

                <div class="hidden md:flex items-center gap-3 text-[#BFD6E8] text-xs font-semibold mr-2">
                    <a href="#" class="hover:text-white transition-colors uppercase">Modifica</a>
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
                        $is_active = ($mappa_attiva === $id);
                        $bg_btn = $is_active 
                            ? "bg-gradient-to-r from-[#3f8718] to-[#5fa831] shadow-lg scale-[1.02] border border-white/20" 
                            : "bg-[rgba(255,255,255,0.05)] border border-white/5 hover:bg-[rgba(255,255,255,0.1)]";
                    ?>
                    <a href="prenotazione.php?mappa=<?php echo $id; ?>&data=<?php echo $oggi; ?>&inizio=<?php echo $ora_inizio; ?>&fine=<?php echo $ora_fine; ?>" 
                       class="relative p-4 rounded-2xl transition-all flex items-center gap-3 <?php echo $bg_btn; ?>">
                        
                        <div class="w-8 h-8 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                            <?php echo $info['icon']; ?>
                        </div>

                        <div class="flex-1">
                            <div class="text-white font-bold text-sm leading-tight"><?php echo $info['nome']; ?></div>
                            <div class="text-white/80 text-[10px] mt-0.5 leading-none"><?php echo $info['desc']; ?></div>
                        </div>
                    </a>
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
                            <option value="base" class="text-black" <?php echo ($mappa_attiva === 'base' || $mappa_attiva === 'meeting') ? 'selected' : ''; ?>>📍 Piano 1 (Base/Sale)</option>
                            <option value="tech" class="text-black" <?php echo ($mappa_attiva === 'tech') ? 'selected' : ''; ?>>📍 Piano 2 (Tech)</option>
                            <option value="parking" class="text-black" <?php echo ($mappa_attiva === 'parking') ? 'selected' : ''; ?>>🚗 Piano Interrato (Parcheggi)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <h2 class="text-xl font-black text-white drop-shadow-md text-right"><?php echo $sezioni[$mappa_attiva]['nome']; ?></h2>
                </div>

                <div class="flex-grow w-full rounded-[20px] bg-[#071B2B]/40 border border-white/5 relative overflow-hidden flex justify-center items-center">
                    
                    <?php if ($mappa_attiva === 'base' || $mappa_attiva === 'meeting'): ?>
                        <img src="Piano Primo.png" class="absolute w-full h-full object-contain opacity-30" alt="Mappa Piano 1">
                        
                        <div class="absolute" style="top: 30%; left: 40%;">
                            <svg width="100" height="100" viewBox="0 0 100 100">
                                <rect width="100" height="100" rx="15" ry="15" fill="#808080" />
                                <circle cx="25" cy="25" r="12" class="svg-slot risorsa-item" data-id="desk_base_1" data-slot="1" data-tipo="base"/>
                                <circle cx="75" cy="25" r="12" class="svg-slot risorsa-item" data-id="desk_base_1" data-slot="2" data-tipo="base"/>
                                <circle cx="25" cy="75" r="12" class="svg-slot risorsa-item" data-id="desk_base_1" data-slot="3" data-tipo="base"/>
                                <circle cx="75" cy="75" r="12" class="svg-slot risorsa-item" data-id="desk_base_1" data-slot="4" data-tipo="base"/>
                            </svg>
                        </div>

                        <div class="absolute" style="top: 60%; left: 20%;">
                            <div class="png-risorsa risorsa-item" data-id="sala_1" data-tipo="meeting" style="width: 80px; height: 60px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                        </div>

                    <?php elseif ($mappa_attiva === 'tech'): ?>
                        <img src="Piano 2.png" class="absolute w-full h-full object-contain opacity-30" alt="Mappa Piano 2">
                        
                        <div class="absolute" style="top: 40%; left: 35%;">
                            <svg width="160" height="100" viewBox="0 0 160 100">
                                <rect width="160" height="100" rx="15" ry="15" fill="#FFA500" />
                                <circle cx="25" cy="25" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="1" data-tipo="tech"/>
                                <circle cx="62" cy="25" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="2" data-tipo="tech"/>
                                <circle cx="98" cy="25" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="3" data-tipo="tech"/>
                                <circle cx="135" cy="25" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="4" data-tipo="tech"/>
                                <circle cx="25" cy="75" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="5" data-tipo="tech"/>
                                <circle cx="62" cy="75" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="6" data-tipo="tech"/>
                                <circle cx="98" cy="75" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="7" data-tipo="tech"/>
                                <circle cx="135" cy="75" r="10" class="svg-slot risorsa-item" data-id="desk_tech_1" data-slot="8" data-tipo="tech"/>
                            </svg>
                        </div>

                    <?php elseif ($mappa_attiva === 'parking'): ?>
                        <div class="parcheggio-grid">
                            <?php
                            for ($fila = 1; $fila <= 5; $fila++) {
                                for ($posto = 1; $posto <= 5; $posto++) {
                                    $id_posto = "park_{$fila}_{$posto}";
                                    $is_disabile = ($posto >= 4); // Ultime 2 posizioni disabili
                                    $mask_url = $is_disabile ? 'src/AssetMappa/PostoAutoDisabili.png' : 'src/AssetMappa/PostoAuto.png';
                                    
                                    echo "<div class='png-risorsa risorsa-item' data-id='{$id_posto}' data-tipo='parking' style='width:35px; height:60px; -webkit-mask-image: url(\"{$mask_url}\"); mask-image: url(\"{$mask_url}\");'></div>";
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
                <h2 class="text-xl font-bold text-white mb-6">Dettagli</h2>
                
                <form id="booking-form" action="salva_prenotazione.php" method="POST" class="flex flex-col flex-grow gap-4">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Asset</label>
                            <div class="input-bg rounded-xl px-3 py-3 border border-white/10 shadow-inner text-center">
                                <span class="text-white font-black text-lg" id="display-seat">-</span>
                                <input type="hidden" name="asset_id" id="input-asset-id" required>
                                <input type="hidden" name="slot_id" id="input-slot-id">
                            </div>
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Locker</label>
                            <div class="bg-[rgba(99,165,180,0.84)] rounded-xl px-3 py-3 border border-white/10 shadow-inner text-center">
                                <span class="text-white font-black text-lg" id="display-locker">-</span>
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

                    <div class="card-info rounded-xl p-4 border border-white/20 mt-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                                <?php echo $sezioni[$mappa_attiva]['icon']; ?>
                            </div>
                            <div>
                                <div class="text-white font-bold text-sm leading-tight"><?php echo $sezioni[$mappa_attiva]['nome']; ?></div>
                                <div class="text-white/90 text-[10px] mt-0.5"><?php echo $sezioni[$mappa_attiva]['desc']; ?></div>
                            </div>
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

            // Logica Selezione Risorse
            document.querySelectorAll('.risorsa-item').forEach(item => {
                item.addEventListener('click', function() {
                    if(this.classList.contains('occupato')) return; // Non cliccare se occupato

                    // Rimuove selezione precedente
                    document.querySelectorAll('.risorsa-item.selezionato').forEach(el => el.classList.remove('selezionato'));
                    
                    // Applica selezione
                    this.classList.add('selezionato');

                    // Prendi i dati
                    const r_id = this.getAttribute('data-id');
                    const r_slot = this.getAttribute('data-slot') || '';
                    
                    // Aggiorna Form Laterale
                    document.getElementById('input-asset-id').value = r_id;
                    document.getElementById('input-slot-id').value = r_slot;
                    
                    let displayTesto = r_id;
                    if(r_slot) displayTesto += ` - Slot ${r_slot}`;
                    document.getElementById('display-seat').innerText = displayTesto;
                });
            });
        });
    </script>
</body>
</html>