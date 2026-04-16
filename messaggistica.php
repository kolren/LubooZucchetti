<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: front-page.php"); exit(); }
require_once 'db.php';

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

// Recupero contatti ordinati per data ultimo messaggio
$contatti = [];
$stmt_utenti = $conn->prepare("
    SELECT u.id, u.nome, u.cognome, u.role, COALESCE(MAX(m.data_invio), '1970-01-01') as ultima_data
    FROM users u
    LEFT JOIN messaggi m ON (
        (m.mittente_id = u.id AND m.destinatario_id = ?) 
        OR (m.mittente_id = ? AND m.destinatario_id = u.id)
    )
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY ultima_data DESC
");
$stmt_utenti->bind_param("iii", $utente_id, $utente_id, $utente_id);
$stmt_utenti->execute();
$res_utenti = $stmt_utenti->get_result();
while($row = $res_utenti->fetch_assoc()){ $contatti[] = $row; }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Messaggistica - LubooZucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
    <style>
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
        
        /* Stili dinamici per la UI dei messaggi */
        .contatto-attivo { 
            background: linear-gradient(135deg, rgba(54, 164, 130, 0.15) 0%, rgba(19, 140, 143, 0.15) 100%);
            border-color: rgba(54, 164, 130, 0.5); 
            box-shadow: inset 4px 0 0 #36A482;
        }
        
        .chat-pattern {
            background-image: radial-gradient(rgba(255,255,255,0.03) 2px, transparent 2px);
            background-size: 30px 30px;
        }
        
        /* Scrollbar customizzata */
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
    </style>
</head>
<body class="min-h-screen bg-main overflow-hidden flex justify-center text-[#F1F6FF] relative">
    
    <header class="fixed top-0 left-0 right-0 z-50">
        <div class="bg-navbar glass-panel rounded-[29px] p-4 lg:p-5 flex items-center justify-between flex-wrap gap-4 mx-4 md:mx-6 lg:mx-8 mt-4">
            <div class="flex items-center gap-4 lg:gap-6">
                <img src="src/Logo.png" alt="LubooZucchetti" class="h-10 object-contain ml-2">
                
                <a href="messaggistica.php" class="relative flex items-center justify-center text-white bg-[#36A482]/20 border border-[#36A482]/50 p-2.5 rounded-xl group shadow-[0_0_15px_rgba(54,164,130,0.3)]" title="Messaggi">
                    <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </a>

                <div class="<?php echo $roleTheme['box_grad']; ?> rounded-[18px] px-5 py-2.5 flex flex-col justify-center shadow-lg border border-white/10 hidden sm:flex">
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md self-start mb-0.5 shadow-sm" style="background-color: <?php echo $roleTheme['badge_bg']; ?>; color: <?php echo $roleTheme['badge_text']; ?>;">
                        <?php echo htmlspecialchars($ruoloUtente); ?>
                    </span>
                    <span class="font-bold text-lg leading-none drop-shadow-md text-white mt-1">Ciao <?php echo htmlspecialchars($nomeUtente); ?>!</span>
                </div>
            </div>

            <nav class="flex items-center gap-2 bg-[#0A2338]/40 p-1.5 rounded-[20px] border border-white/10 overflow-x-auto">
                <?php if (in_array($ruoloUtente, ['amministratore', 'coordinatore'])): ?>
                <a href="dipendenti.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Dipendenti</a>
                <?php endif; ?>
                <a href="prenotazione.php" class="bg-nav-btn text-[#F1F6FF] px-5 py-2.5 rounded-[14px] text-sm font-bold shadow-md hover:brightness-110 transition-all whitespace-nowrap">Prenota</a>
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

    <div class="w-full max-w-[1400px] mt-32 px-3 sm:px-4 md:px-8 grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6 h-[calc(100vh-150px)] pb-8">
        
        <div class="lg:col-span-1 ui-panel glass-panel flex flex-col shadow-2xl h-full border border-white/5 rounded-[24px] overflow-hidden">
            <div class="p-4 md:p-5 border-b border-white/10 bg-[#0A2338]/80 backdrop-blur-md z-10 flex flex-col gap-3">
                <h2 class="text-xl md:text-2xl font-black text-white tracking-wide">Messaggi</h2>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#BFD6E8]/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search-contacts" onkeyup="filtraContatti()" placeholder="Cerca..." class="w-full bg-[#04101A] border border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:border-[#36A482] transition-colors shadow-inner">
                </div>
            </div>
            
            <div class="flex-grow overflow-y-auto p-2 md:p-3 flex flex-col gap-2 custom-scrollbar" id="contacts-list">
                <?php foreach($contatti as $c): ?>
                    <div data-id="<?php echo $c['id']; ?>" data-nome="<?php echo strtolower($c['nome'] . ' ' . $c['cognome']); ?>" 
                         onclick="apriChat(<?php echo $c['id']; ?>, '<?php echo addslashes($c['nome'] . ' ' . $c['cognome']); ?>', this)" 
                         class="contatto-item p-2.5 md:p-3 rounded-lg border border-transparent bg-transparent hover:bg-white/5 cursor-pointer transition-all flex items-center gap-3">
                        <div class="relative shrink-0">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-[#36A482] to-[#1D7F75] flex items-center justify-center font-bold text-sm md:text-lg text-white shadow-lg">
                                <?php echo strtoupper(substr($c['nome'], 0, 1) . substr($c['cognome'], 0, 1)); ?>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-[#0A2338] rounded-full"></span>
                        </div>
                        <div class="flex-grow truncate min-w-0">
                            <div class="font-bold text-white text-sm md:text-[15px] truncate"><?php echo htmlspecialchars($c['nome'] . ' ' . $c['cognome']); ?></div>
                            <div class="text-[10px] md:text-[11px] text-[#BFD6E8]/60 font-semibold uppercase tracking-widest mt-0.5 truncate"><?php echo htmlspecialchars($c['role']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="no-contacts-found" class="hidden text-center text-[#BFD6E8]/50 text-sm py-4">Nessun collega trovato.</div>
            </div>
        </div>

        <div class="lg:col-span-3 ui-panel glass-panel flex flex-col shadow-2xl h-full border border-white/5 rounded-[24px] overflow-hidden bg-[#04101A]/90 relative">
            
            <div id="no-chat-selected" class="absolute inset-0 flex flex-col items-center justify-center text-[#BFD6E8]/30 z-10 bg-chat-pattern">
                <div class="w-20 h-20 md:w-24 md:h-24 bg-white/5 rounded-full flex items-center justify-center mb-4 md:mb-6 border border-white/10 shadow-inner">
                    <svg class="w-10 h-10 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <h3 class="text-lg md:text-xl font-bold text-white mb-2">I tuoi messaggi</h3>
                <p class="text-xs md:text-sm px-4">Seleziona un collega dalla lista per iniziare a chattare.</p>
            </div>

            <div id="chat-container" class="hidden flex-col h-full z-20">
                <div class="bg-[#0A2338]/90 backdrop-blur-md px-4 md:px-6 py-3 md:py-4 border-b border-white/10 flex justify-between items-center z-30 shadow-md">
                    <div class="flex items-center gap-3 md:gap-4 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#36A482] to-[#138C8F] flex items-center justify-center font-bold text-white shadow-inner shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-grow">
                            <h2 id="chat-title" class="text-base md:text-lg font-black text-white leading-tight truncate">Nome Cognome</h2>
                            <div class="text-[10px] md:text-[11px] text-[#36A482] font-bold uppercase tracking-widest flex items-center gap-1.5 mt-0.5">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Online
                            </div>
                        </div>
                    </div>
                </div>

                <div id="chat-messages" class="flex-grow p-4 md:p-6 overflow-y-auto flex flex-col gap-3 chat-pattern custom-scrollbar">
                </div>

                <div class="p-3 md:p-4 bg-[#0A2338]/90 border-t border-white/10 backdrop-blur-md">
                    <form onsubmit="inviaMessaggioChat(event)" class="flex gap-2 md:gap-3 items-end">
                        <div class="flex-grow relative">
                            <input type="text" id="chat-input" placeholder="Scrivi un messaggio..." autocomplete="off" class="w-full bg-[#04101A] border border-white/10 rounded-lg pl-4 md:pl-5 pr-4 py-2.5 md:py-3 text-sm md:text-[15px] text-white focus:outline-none focus:border-[#36A482] transition-colors shadow-inner">
                        </div>
                        <button type="submit" class="w-10 h-10 md:w-12 md:h-12 shrink-0 bg-[#36A482] hover:bg-[#2c8569] text-white rounded-lg md:rounded-[16px] flex items-center justify-center transition-all shadow-lg hover:shadow-[#36A482]/40 hover:-translate-y-0.5">
                            <svg class="w-5 h-5 transform rotate-45 -mt-1 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        let chatTargetId = null;
        let chatPolling = null;

        // Funzione per la ricerca dei contatti per nome
        function filtraContatti() {
            const query = document.getElementById('search-contacts').value.toLowerCase();
            const contatti = document.querySelectorAll('.contatto-item');
            const noFoundMsg = document.getElementById('no-contacts-found');
            let trovati = 0;

            contatti.forEach(c => {
                const nome = c.getAttribute('data-nome');
                if (nome.includes(query)) {
                    c.classList.remove('hidden');
                    c.classList.add('flex');
                    trovati++;
                } else {
                    c.classList.remove('flex');
                    c.classList.add('hidden');
                }
            });

            if(trovati === 0 && query.length > 0) {
                noFoundMsg.classList.remove('hidden');
                noFoundMsg.textContent = 'Nessun collega trovato.';
            } else {
                noFoundMsg.classList.add('hidden');
            }
        }

        function apriChat(uid, nome, elemento) {
            chatTargetId = uid;
            document.getElementById('chat-title').textContent = nome;
            
            document.getElementById('no-chat-selected').classList.add('hidden');
            document.getElementById('chat-container').classList.remove('hidden');
            document.getElementById('chat-container').classList.add('flex');
            
            // Gestione Stile Selezione
            document.querySelectorAll('.contatto-item').forEach(el => el.classList.remove('contatto-attivo'));
            elemento.classList.add('contatto-attivo');

            caricaMessaggi();
            if(chatPolling) clearInterval(chatPolling);
            chatPolling = setInterval(caricaMessaggi, 2000);
            document.getElementById('chat-input').focus();
        }

        async function caricaMessaggi() {
            if(!chatTargetId) return;
            try {
                const response = await fetch(`api_messaggi.php?partner_id=${chatTargetId}`);
                const html = await response.text();
                const container = document.getElementById('chat-messages');
                
                // Controlla se l'utente ha scrollato in alto per evitare salti improvvisi
                const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 30;
                
                container.innerHTML = html;
                
                // Scendi giù in automatico solo se era già in fondo
                if(isScrolledToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch(e) { console.error("Errore caricamento", e); }
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
                setTimeout(() => {
                    const c = document.getElementById('chat-messages');
                    c.scrollTo({ top: c.scrollHeight, behavior: 'smooth' });
                }, 50);
            } catch(e) { console.error("Errore invio", e); }
        }
    </script>
</body>
</html>