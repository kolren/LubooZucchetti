<?php
session_start();
require_once 'db.php';

// Controllo accesso
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nomeUtente = $_SESSION['user_nome'] ?? 'Utente';
$ruoloUtente = strtolower(trim($_SESSION['user_ruolo'] ?? 'dipendente'));

// Parametri data e orari correnti
$oggi = $_GET['data'] ?? date('Y-m-d');
$ora_inizio = $_GET['inizio'] ?? '09:00';
$ora_fine = $_GET['fine'] ?? '18:00';

// RECUPERO PRENOTAZIONI ESISTENTI (Ispirato alla tua logica in mappa.php)
// Controlliamo le risorse già prenotate per questa data e orario per assegnare la classe "occupata"
$stmt = $pdo->prepare("
    SELECT risorsa_id, slot_id 
    FROM prenotazioni 
    WHERE data_prenotazione = ? 
    AND (ora_inizio < ? AND ora_fine > ?)
");
// Assicurati che i nomi colonna corrispondano al tuo DB (data_prenotazione, ora_inizio, ora_fine)
$stmt->execute([$oggi, $ora_fine, $ora_inizio]);
$prenotazioni_esistenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creiamo un array associativo per controllare istantaneamente se una risorsa è occupata
$risorse_occupate = [];
foreach ($prenotazioni_esistenti as $row) {
    $key = $row['risorsa_id'];
    if (!empty($row['slot_id'])) {
        $key .= '_' . $row['slot_id'];
    }
    $risorse_occupate[$key] = true;
}

// Funzione helper per determinare la classe CSS da applicare (Verde = libera, Rosso = occupata)
function getClassStato($id, $slot = null) {
    global $risorse_occupate;
    $key = $id . ($slot ? '_' . $slot : '');
    return isset($risorse_occupate[$key]) ? 'occupata' : 'libera';
}

// Stile navbar in base al ruolo
$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#0F6E73_0%,#138C8F_100%)]'],  
    'coordinatore' => ['badge_bg' => '#4d6dd4', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#2D4485_0%,#4D6DD4_100%)]'],   
    'dipendente' => ['badge_bg' => '#6aa70f', 'badge_text' => '#FFFFFF', 'box_grad' => 'bg-[linear-gradient(135deg,#4D7C0F_0%,#6AA70F_100%)]']      
];
$roleTheme = $themeColors[$ruoloUtente] ?? $themeColors['dipendente'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planimetria Generale - LubooZucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <style>
        /* COLORI STATI (Verde Libero, Rosso Occupato, Blu Selezionato) */
        
        /* Gestione SVG Scrivanie (coloriamo solo i cerchietti, NON il rettangolo di base) */
        .svg-slot { cursor: pointer; transition: fill 0.2s; }
        .svg-slot.libera { fill: #4CAF50; } /* Verde */
        .svg-slot.occupata { fill: #F44336; pointer-events: none; } /* Rosso */
        .svg-slot.selezionata { fill: #2196F3; } /* Blu */

        /* Gestione PNG (Maschere CSS per colorare la sagoma) */
        .png-risorsa { cursor: pointer; transition: background-color 0.2s; mask-size: contain; -webkit-mask-size: contain; mask-repeat: no-repeat; -webkit-mask-repeat: no-repeat; mask-position: center; -webkit-mask-position: center; }
        .png-risorsa.libera { background-color: #4CAF50; } /* Verde */
        .png-risorsa.occupata { background-color: #F44336; pointer-events: none; } /* Rosso */
        .png-risorsa.selezionata { background-color: #2196F3; } /* Blu */

        /* Griglia Parcheggio fissa: 5x5 */
        .parcheggio-grid { display: grid; grid-template-columns: repeat(5, 50px); gap: 15px; justify-content: center; align-items: center; padding: 20px; }

        /* Popup overlay */
        .popup-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .popup-overlay.active { display: flex; }
    </style>
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1500px] flex flex-col gap-6">
        
        <header class="relative mb-2">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    <div class="<?php echo $roleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md self-start mb-0.5 shadow-sm" style="background-color: <?php echo $roleTheme['badge_bg']; ?>; color: <?php echo $roleTheme['badge_text']; ?>;">
                            <?php echo htmlspecialchars($ruoloUtente); ?>
                        </span>
                        <span class="font-bold text-lg leading-none drop-shadow-md text-white mt-1">Ciao <?php echo htmlspecialchars($nomeUtente); ?>!</span>
                    </div>
                </div>
                <nav class="flex items-center gap-2 bg-[#0A2338]/40 p-1.5 rounded-[20px] border border-white/10 overflow-x-auto custom-scrollbar">
                    <a href="prenotazione.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110">DashBoard</a>
                </nav>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start h-[750px]">
            
            <div class="lg:col-span-2 ui-panel glass-panel p-6 flex flex-col shadow-2xl sticky top-0">
                <h3 class="text-lg font-bold text-white mb-4">Navigazione</h3>
                <div class="flex flex-col gap-3 mb-8">
                    <a href="#piano1" class="p-3 bg-white/10 rounded-xl text-center text-sm font-bold hover:bg-white/20 transition-all">Piano 1</a>
                    <a href="#piano2" class="p-3 bg-white/10 rounded-xl text-center text-sm font-bold hover:bg-white/20 transition-all">Piano 2</a>
                    <a href="#parcheggio" class="p-3 bg-white/10 rounded-xl text-center text-sm font-bold hover:bg-white/20 transition-all">Parcheggio</a>
                </div>

                <div class="mt-auto bg-[#071B2B]/30 p-4 rounded-2xl border border-white/5">
                    <h3 class="text-sm font-bold text-white mb-3">Legenda</h3>
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#4CAF50] rounded-full"></div><span>Libero</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#F44336] rounded-full"></div><span>Occupato</span></div>
                        <div class="flex items-center gap-3"><div class="w-4 h-4 bg-[#2196F3] rounded-full"></div><span>Selezionato</span></div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 ui-panel glass-panel p-2 flex flex-col relative shadow-2xl h-full overflow-hidden">
                <div class="h-full overflow-y-auto custom-scrollbar p-4 flex flex-col gap-10 scroll-smooth">
                    
                    <div id="piano1" class="relative w-full h-[500px] bg-black/20 rounded-2xl border border-white/10 overflow-hidden shrink-0 shadow-inner">
                        <h2 class="absolute top-4 left-4 text-2xl font-black text-white/80 z-20 uppercase tracking-widest drop-shadow-md">Piano 1</h2>
                        <img src="src/AssetMappa/Mappa Prototipo da seguire/Piano 1.png" class="absolute w-full h-full object-contain opacity-40 z-0 pointer-events-none">
                        
                        <div class="absolute z-10" style="top: 25%; left: 15%;">
                            <div class="png-risorsa risorsa-item <?php echo getClassStato('sala_riunioni_1'); ?>" 
                                 data-id="sala_riunioni_1" data-tipo="Sala Riunioni"
                                 style="width: 100px; height: 75px; -webkit-mask-image: url('src/AssetMappa/SalaRiunioni.png'); mask-image: url('src/AssetMappa/SalaRiunioni.png');"></div>
                        </div>

                        <div class="absolute z-10" style="top: 50%; left: 45%;">
                            <svg width="120" height="120" viewBox="0 0 100 100">
                                <rect width="100" height="100" rx="15" ry="15" fill="#808080" /> <circle cx="25" cy="25" r="12" class="svg-slot risorsa-item <?php echo getClassStato('desk_base_1', '1'); ?>" data-id="desk_base_1" data-slot="1" data-tipo="Scrivania Base"/>
                                <circle cx="75" cy="25" r="12" class="svg-slot risorsa-item <?php echo getClassStato('desk_base_1', '2'); ?>" data-id="desk_base_1" data-slot="2" data-tipo="Scrivania Base"/>
                                <circle cx="25" cy="75" r="12" class="svg-slot risorsa-item <?php echo getClassStato('desk_base_1', '3'); ?>" data-id="desk_base_1" data-slot="3" data-tipo="Scrivania Base"/>
                                <circle cx="75" cy="75" r="12" class="svg-slot risorsa-item <?php echo getClassStato('desk_base_1', '4'); ?>" data-id="desk_base_1" data-slot="4" data-tipo="Scrivania Base"/>
                            </svg>
                        </div>
                    </div>

                    <div id="piano2" class="relative w-full h-[500px] bg-black/20 rounded-2xl border border-white/10 overflow-hidden shrink-0 shadow-inner">
                        <h2 class="absolute top-4 left-4 text-2xl font-black text-white/80 z-20 uppercase tracking-widest drop-shadow-md">Piano 2</h2>
                        <img src="src/AssetMappa/Mappa Prototipo da seguire/Piano 2.png" class="absolute w-full h-full object-contain opacity-40 z-0 pointer-events-none">
                        
                        <div class="absolute z-10" style="top: 40%; left: 30%;">
                            <svg width="200" height="120" viewBox="0 0 160 100">
                                <rect width="160" height="100" rx="15" ry="15" fill="#FFA500" /> <circle cx="25" cy="25" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '1'); ?>" data-id="desk_tech_1" data-slot="1" data-tipo="Scrivania Tech"/>
                                <circle cx="62" cy="25" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '2'); ?>" data-id="desk_tech_1" data-slot="2" data-tipo="Scrivania Tech"/>
                                <circle cx="98" cy="25" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '3'); ?>" data-id="desk_tech_1" data-slot="3" data-tipo="Scrivania Tech"/>
                                <circle cx="135" cy="25" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '4'); ?>" data-id="desk_tech_1" data-slot="4" data-tipo="Scrivania Tech"/>
                                <circle cx="25" cy="75" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '5'); ?>" data-id="desk_tech_1" data-slot="5" data-tipo="Scrivania Tech"/>
                                <circle cx="62" cy="75" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '6'); ?>" data-id="desk_tech_1" data-slot="6" data-tipo="Scrivania Tech"/>
                                <circle cx="98" cy="75" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '7'); ?>" data-id="desk_tech_1" data-slot="7" data-tipo="Scrivania Tech"/>
                                <circle cx="135" cy="75" r="10" class="svg-slot risorsa-item <?php echo getClassStato('desk_tech_1', '8'); ?>" data-id="desk_tech_1" data-slot="8" data-tipo="Scrivania Tech"/>
                            </svg>
                        </div>
                    </div>

                    <div id="parcheggio" class="relative w-full bg-black/20 rounded-2xl border border-white/10 overflow-hidden shrink-0 shadow-inner p-8">
                        <h2 class="text-2xl font-black text-white/80 z-20 uppercase tracking-widest drop-shadow-md mb-6">Parcheggio Interrato</h2>
                        <div class="parcheggio-grid">
                            <?php
                            for ($fila = 1; $fila <= 5; $fila++) {
                                for ($posto = 1; $posto <= 5; $posto++) {
                                    $id_posto = "park_{$fila}_{$posto}";
                                    // Gli ultimi 2 posti di ogni fila sono disabili
                                    $is_disabile = ($posto >= 4); 
                                    $mask_url = $is_disabile ? 'src/AssetMappa/PostoAutoDisabili.png' : 'src/AssetMappa/PostoAuto.png';
                                    $statoClass = getClassStato($id_posto);
                                    
                                    echo "<div class='png-risorsa risorsa-item {$statoClass}' 
                                               data-id='{$id_posto}' data-tipo='Posto Auto' 
                                               style='width:45px; height:80px; -webkit-mask-image: url(\"{$mask_url}\"); mask-image: url(\"{$mask_url}\");'></div>";
                                }
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="lg:col-span-3 ui-panel glass-panel p-6 flex flex-col shadow-2xl sticky top-0 h-[700px]">
                <h2 class="text-xl font-bold text-white mb-6">Dettagli Prenotazione</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-500/20 border border-red-500 text-red-100 p-3 rounded-xl mb-4 text-sm font-bold text-center">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form id="booking-form" action="salva_prenotazione.php" method="POST" class="flex flex-col flex-grow gap-5">
                    
                    <div class="bg-white/5 rounded-xl px-4 py-4 border border-white/10 shadow-inner text-center min-h-[80px] flex flex-col justify-center">
                        <span class="text-white/60 text-xs font-bold uppercase tracking-wider mb-1">Risorsa Selezionata</span>
                        <span class="text-white font-black text-lg" id="display-seat">Nessuna selezione</span>
                        <input type="hidden" name="asset_id" id="input-asset-id" required>
                        <input type="hidden" name="slot_id" id="input-slot-id">
                        <input type="hidden" name="tipo_risorsa" id="input-tipo">
                    </div>

                    <div>
                        <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Data</label>
                        <input type="date" name="data" id="filtro-data" value="<?php echo htmlspecialchars($oggi); ?>" required 
                               class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Inizio</label>
                            <input type="time" name="inizio" id="filtro-inizio" value="<?php echo htmlspecialchars($ora_inizio); ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="text-white/80 text-xs font-bold mb-1.5 block uppercase tracking-wider">Fine</label>
                            <input type="time" name="fine" id="filtro-fine" value="<?php echo htmlspecialchars($ora_fine); ?>" required 
                                   class="w-full input-bg border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>

                    <div class="mt-auto pt-4">
                        <button type="submit" id="btn-submit" class="w-full py-4 px-4 rounded-xl font-black text-white text-md uppercase tracking-wider transition-all border border-white/10 shadow-xl bg-[rgba(180,123,99,0.95)] hover:brightness-110 hover:scale-[1.02] opacity-50 cursor-not-allowed" disabled>
                            Conferma Prenotazione
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="popup-successo" class="popup-overlay <?php echo isset($_GET['success']) ? 'active' : ''; ?>">
        <div class="bg-navbar glass-panel p-8 rounded-[29px] max-w-md w-full text-center border border-white/20 shadow-2xl">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-2xl font-black text-white mb-2">Prenotazione Confermata!</h2>
            <p class="text-white/80 mb-8">La tua risorsa è stata riservata con successo.</p>
            <div class="flex flex-col gap-3">
                <button onclick="window.location.href='prenotazione.php'" class="w-full py-3 bg-blue-500 text-white rounded-xl font-bold hover:bg-blue-600 transition-colors">Vuoi prenotare ancora?</button>
                <button onclick="window.location.href='dashboard.php'" class="w-full py-3 bg-white/10 text-white rounded-xl font-bold border border-white/20 hover:bg-white/20 transition-colors">Gestisci le tue prenotazioni</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnSubmit = document.getElementById('btn-submit');
            const formInputs = ['filtro-data', 'filtro-inizio', 'filtro-fine'];

            // Se cambia data o ora, ricarica la pagina per aggiornare gli stati DB
            formInputs.forEach(id => {
                document.getElementById(id).addEventListener('change', () => {
                    const data = document.getElementById('filtro-data').value;
                    const inizio = document.getElementById('filtro-inizio').value;
                    const fine = document.getElementById('filtro-fine').value;
                    window.location.href = `prenotazione.php?data=${data}&inizio=${inizio}&fine=${fine}`;
                });
            });

            // Gestione Click sulle risorse nella planimetria
            document.querySelectorAll('.risorsa-item').forEach(item => {
                item.addEventListener('click', function() {
                    if(this.classList.contains('occupata')) return; // Blocca click se rossa

                    // Rimuovi blu da tutti gli altri
                    document.querySelectorAll('.risorsa-item.selezionata').forEach(el => el.classList.remove('selezionata'));
                    
                    // Colora di blu quello attuale
                    this.classList.add('selezionata');

                    const r_id = this.getAttribute('data-id');
                    const r_slot = this.getAttribute('data-slot') || '';
                    const r_tipo = this.getAttribute('data-tipo');

                    // Passa i dati al form di destra
                    document.getElementById('input-asset-id').value = r_id;
                    document.getElementById('input-slot-id').value = r_slot;
                    document.getElementById('input-tipo').value = r_tipo;

                    // Mostra a video
                    let testo = r_tipo + "<br><span class='text-sm text-white/50'>" + r_id + (r_slot ? " - Slot " + r_slot : "") + "</span>";
                    document.getElementById('display-seat').innerHTML = testo;

                    // Abilita bottone conferma
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            });
        });
    </script>
</body>
</html>