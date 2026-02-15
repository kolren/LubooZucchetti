<?php
session_start();

// Controllo sicurezza: se non c'è user_id, rimanda al login
if (!isset($_SESSION['user_id'])) {
    header("Location: front-page.php");
    exit();
}

// RECUPERO DATI SESSIONE (Usando le chiavi esatte del tuo loginhandle.php)
$nomeUtente = $_SESSION['user_nome'] ?? 'Utente';
$ruoloUtente = strtolower(trim($_SESSION['user_ruolo'] ?? 'dipendente'));

// Dati Simulati (da sostituire con il DB reale)
$stat_totali = 42;
$stat_in_arrivo = 5;
$stat_mese = 12;
$stat_postazione_top = "Postazione 12";

$prenotazioni_in_arrivo = [
    ['tipo' => 'Postazione', 'titolo' => 'Scrivania 04 - Piano 1', 'data' => '24 Ottobre', 'orario' => '09:00 - 18:00', 'is_auto' => false, 'svg' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ['tipo' => 'Riunione', 'titolo' => 'Sala Copernico', 'data' => '25 Ottobre', 'orario' => '14:30 - 16:00', 'is_auto' => false, 'svg' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
];

$attivita_dati = [
    'settimana' => ['Lunedì' => 2, 'Martedì' => 1, 'Mercoledì' => 3, 'Giovedì' => 0, 'Venerdì' => 1],
    'mese' => ['1ª Sett' => 5, '2ª Sett' => 8, '3ª Sett' => 4, '4ª Sett' => 6],
    'anno' => ['Trim 1' => 20, 'Trim 2' => 15, 'Trim 3' => 30, 'Trim 4' => 25]
];

// Colori dinamici in base al ruolo
$themeColors = [
    'amministratore' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF'], 
    'admin' => ['badge_bg' => '#1D7F75', 'badge_text' => '#FFFFFF'], 
    'coordinatore' => ['badge_bg' => '#5C78B8', 'badge_text' => '#FFFFFF'],   
    'dipendente' => ['badge_bg' => '#84cc16', 'badge_text' => '#FFFFFF']      
];
$roleTheme = array_key_exists($ruoloUtente, $themeColors) ? $themeColors[$ruoloUtente] : ['badge_bg' => '#475569', 'badge_text' => '#FFFFFF'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LubooZucchetti</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: ui-rounded, 'SF Pro Rounded', 'Nunito', system-ui, sans-serif; letter-spacing: -0.01em; }
        .bg-main { background: linear-gradient(135deg, #071B2B 0%, #0E2F47 40%, #2E6F9E 100%); }
        .bg-navbar { background: linear-gradient(to right, #143C5B, #1C537A); }
        .bg-user-box { background: linear-gradient(145deg, #0F6E73, #138C8F); }
        .bg-nav-btn { background: linear-gradient(to bottom, #4C67A8, #5F7EC4); }
        .bg-nav-btn-active { background: linear-gradient(to bottom, #6E8FD6, #4F6EB5); }
        
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

        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        
        .glass-panel { backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>

<body class="min-h-screen bg-main p-4 md:p-6 lg:p-8 overflow-x-hidden flex justify-center text-[#F1F6FF]">

    <div class="w-full max-w-[1400px] flex flex-col gap-8">

        <header class="relative">
            <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4 lg:gap-6">
                    <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                    <div class="bg-user-box rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10">
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
                    <?php if (in_array($ruoloUtente, ['amministratore', 'admin', 'coordinatore'])): ?>
                    <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                    <?php endif; ?>
                    
                    <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
                    <a href="dashboard.php" class="bg-nav-btn-active text-white px-5 py-2.5 rounded-[14px] text-sm font-black shadow-lg scale-105 border border-white/20 whitespace-nowrap">DashBoard</a>
                    <a href="gestisci.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Gestisci</a>
                </nav>

                <div class="hidden md:flex items-center gap-3 text-[#BFD6E8] text-xs font-semibold mr-2">
                    <a href="#" class="hover:text-white transition-colors uppercase">Modifica</a>
                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                    <a href="index.php" class="hover:text-white transition-colors uppercase">Cambia utente</a>
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
                <div class="text-2xl font-bold text-white leading-tight drop-shadow-md"><?php echo htmlspecialchars($stat_postazione_top); ?></div>
                <div class="text-[11px] font-bold text-white/70 uppercase tracking-widest mt-2">Postazione più usata</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            
            <div class="lg:col-span-2 bg-bookings glass-panel rounded-[24px] p-8 flex flex-col">
                <h2 class="text-2xl font-bold text-[#F1F6FF] mb-6">Prenotazioni in arrivo</h2>
                <div class="flex-grow overflow-y-auto custom-scrollbar pr-3 space-y-4 max-h-[450px]">
                    <?php foreach ($prenotazioni_in_arrivo as $pren): 
                        $cardBgClass = $pren['is_auto'] ? 'bg-booking-auto' : 'bg-booking-item';
                    ?>
                        <div class="<?php echo $cardBgClass; ?> rounded-2xl p-5 flex items-center gap-5 border border-white/10 shadow-md hover:brightness-110 transition-all">
                            <div class="w-12 h-12 rounded-[14px] bg-white/10 flex items-center justify-center text-white border border-white/10 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $pren['svg']; ?>" /></svg>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-base font-bold text-[#F1F6FF]"><?php echo htmlspecialchars($pren['titolo']); ?></h4>
                                <p class="text-xs font-bold text-[#BFD6E8] uppercase tracking-widest mt-0.5"><?php echo htmlspecialchars($pren['tipo']); ?></p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-[#F1F6FF]"><?php echo htmlspecialchars($pren['data']); ?></div>
                                <div class="text-xs font-bold text-[#F1F6FF] mt-1.5 bg-[#0A1B29]/40 px-3 py-1 rounded-md inline-block border border-white/5">
                                    <?php echo htmlspecialchars($pren['orario']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col gap-8 h-full">
                
                <div class="bg-activity glass-panel rounded-[24px] p-8 flex flex-col flex-grow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8 relative z-20">
                        <h2 class="text-2xl font-bold text-[#F1F6FF]">Attività</h2>
                        <div class="relative inline-block text-left">
                            <button id="btn-dropdown-attivita" type="button" class="flex items-center gap-2 text-xs font-bold text-[#F1F6FF] bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg border border-white/10 transition-colors shadow-sm">
                                <span id="label-dropdown">18/01 - 25/01</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div id="menu-dropdown-attivita" class="hidden absolute right-0 mt-2 w-48 rounded-[16px] shadow-2xl border border-white/20 bg-[rgba(14,47,71,0.95)] backdrop-blur-xl overflow-hidden transform opacity-0 transition-all duration-200 origin-top-right scale-95">
                                <a href="#" data-target="settimana" data-label="18/01 - 25/01" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors">Settimana (18/01 - 25/01)</a>
                                <a href="#" data-target="mese" data-label="Gennaio" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors border-t border-white/5">Mese (Gennaio)</a>
                                <a href="#" data-target="anno" data-label="2026" class="dropdown-item block px-4 py-3 text-xs font-bold text-[#BFD6E8] hover:text-white hover:bg-white/10 transition-colors border-t border-white/5">Anno (2026)</a>
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
                                            <span class="text-white text-xs font-bold"><?php echo $valore; ?></span>
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