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

// ARRAY SEZIONI CON SVG INCLUSI DINAMICAMENTE
// Utilizzo @ per sopprimere warning se il file non è ancora stato creato nel percorso corretto
$sezioni = [
    'base' => ['icon' => @file_get_contents('src/PostazioneBase.svg'), 'nome' => 'Postazione Base', 'desc' => 'Scrivania + Cassettiera'],
    'tech' => ['icon' => @file_get_contents('src/PostazioneTech.svg'), 'nome' => 'Postazione Tech', 'desc' => 'Scrivania + Monitor Extra'],
    'meeting' => ['icon' => @file_get_contents('src/Riunioni.svg'), 'nome' => 'Sala Riunioni', 'desc' => 'Sala attrezzata'],
    'parking' => ['icon' => @file_get_contents('src/PostoAuto.svg'), 'nome' => 'Posto Auto', 'desc' => 'Parcheggio interrato']
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione - LubooZucchetti</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: ui-rounded, 'SF Pro Rounded', 'Nunito', system-ui, sans-serif; letter-spacing: -0.01em; }
        .bg-main { background: linear-gradient(135deg, #071B2B 0%, #0E2F47 40%, #2E6F9E 100%); }
        .bg-navbar { background: linear-gradient(to right, #143C5B, #1C537A); }
        .bg-nav-btn { background: linear-gradient(to bottom, #4C67A8, #5F7EC4); }
        .bg-nav-btn-active { background: linear-gradient(to bottom, #6E8FD6, #4F6EB5); }
        .glass-panel { backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        
        .ui-panel { background: rgba(0, 51, 128, 0.24); border-radius: 34px; border: 1px solid rgba(255,255,255,0.05); }
        .input-bg { background: rgba(99, 144, 180, 0.84); }
        .card-info { background: rgba(192, 199, 255, 0.55); }
        
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        
        /* Assicura che gli SVG caricati si adattino e prendano il colore del testo genitore */
        .svg-icon-wrapper svg { width: 100%; height: 100%; fill: currentColor; }
    </style>
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
            
            <div class="lg:col-span-4 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
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
                       class="relative p-4 rounded-2xl transition-all flex items-center gap-4 <?php echo $bg_btn; ?>">
                        
                        <div class="w-10 h-10 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                            <?php echo $info['icon'] ?: 'SVG'; ?>
                        </div>

                        <div class="flex-1">
                            <div class="text-white font-bold text-md leading-tight"><?php echo $info['nome']; ?></div>
                            <div class="text-white/80 text-xs mt-0.5"><?php echo $info['desc']; ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-auto bg-[#071B2B]/30 p-4 rounded-2xl border border-white/5">
                    <h3 class="text-sm font-bold text-white mb-3">Legenda</h3>
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#36A482] rounded-full"></div><span class="text-white/90">Disponibile</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#ef4444] rounded-full"></div><span class="text-white/90">Occupato</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-white rounded-full"></div><span class="text-white/90">Selezionato</span></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 ui-panel glass-panel p-6 flex flex-col shadow-2xl">
                <h2 class="text-xl font-bold text-white mb-6">Dettagli Prenotazione</h2>
                
                <form id="booking-form" action="salva_prenotazione.php" method="POST" class="flex flex-col flex-grow gap-6 max-w-2xl">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Asset</label>
                            <div class="input-bg rounded-xl px-4 py-4 border border-white/10 shadow-inner text-center">
                                <span class="text-white font-black text-xl" id="display-seat">-</span>
                                <input type="hidden" name="asset_id" id="input-asset-id" required>
                            </div>
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Locker</label>
                            <div class="bg-[rgba(99,165,180,0.84)] rounded-xl px-4 py-4 border border-white/10 shadow-inner text-center">
                                <span class="text-white font-black text-xl" id="display-locker">-</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Data</label>
                        <input type="date" name="data" id="input-data" value="<?php echo $oggi; ?>" required 
                               class="w-full input-bg border border-white/10 rounded-xl px-5 py-4 text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Inizio</label>
                            <input type="time" name="inizio" id="input-inizio" value="<?php echo $ora_inizio; ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-5 py-4 text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Fine</label>
                            <input type="time" name="fine" id="input-fine" value="<?php echo $ora_fine; ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-5 py-4 text-white text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                    </div>

                    <div class="card-info rounded-xl p-5 border border-white/20 mt-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 flex items-center justify-center text-white drop-shadow-md svg-icon-wrapper">
                                <?php echo $sezioni[$mappa_attiva]['icon'] ?: 'SVG'; ?>
                            </div>
                            <div>
                                <div class="text-white font-bold text-lg leading-tight"><?php echo $sezioni[$mappa_attiva]['nome']; ?></div>
                                <div class="text-white/90 text-xs mt-1"><?php echo $sezioni[$mappa_attiva]['desc']; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-6">
                        <button type="submit" id="btn-submit" 
                                class="w-full py-5 px-4 rounded-xl font-black text-white text-lg uppercase tracking-wider transition-all border border-white/10 shadow-xl bg-[rgba(180,123,99,0.95)] hover:brightness-110 hover:scale-[1.02]">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputData = document.getElementById('input-data');
            const inputInizio = document.getElementById('input-inizio');
            const inputFine = document.getElementById('input-fine');

            function ricaricaDisponibilita() {
                const params = new URLSearchParams(window.location.search);
                params.set('mappa', '<?php echo $mappa_attiva; ?>');
                params.set('data', inputData.value);
                params.set('inizio', inputInizio.value);
                params.set('fine', inputFine.value);
                window.location.href = window.location.pathname + '?' + params.toString();
            }

            inputData.addEventListener('change', ricaricaDisponibilita);
            inputInizio.addEventListener('change', ricaricaDisponibilita);
            inputFine.addEventListener('change', ricaricaDisponibilita);
        });
    </script>
</body>
</html>