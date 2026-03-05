<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Home - LUBOO BookSystem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Tipografia e Sfondo Base */
        @font-face { 
            font-family: 'SF Pro Rounded'; 
            src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2'); 
        }

        body {
            font-family: 'SF Pro Rounded', 'Nunito', sans-serif;
            background-color: #070A0E;
            color: white;
            margin: 0;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* ================================
           CUSTOM SCROLLBAR
           ================================ */
        ::-webkit-scrollbar {
            width: 12px;
        }

        ::-webkit-scrollbar-track {
            background: #070A0E; /* Colore di fondo del sito */
            border-left: 1px solid rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #14364F, #30A9FF); /* Gradiente blu */
            border-radius: 20px;
            border: 3px solid #070A0E; /* Crea un effetto di padding intorno al cursore */
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #30A9FF, #FFB247); /* Diventa azzurro/arancio al passaggio del mouse */
        }

        /* Sfondo della sezione Hero */
        .hero-section {
            background: linear-gradient(135deg, #30A9FF 0%, #14364F 50%, #070A0E 100%);
            position: relative;
            padding-bottom: 5rem;
        }

        /* NAVBAR FLUTTUANTE STONDATA (Meno trasparente per maggior contrasto) */
        .glass-nav {
            background: rgba(15, 28, 46, 0.85); 
            backdrop-filter: blur(20px) saturate(120%);
            -webkit-backdrop-filter: blur(20px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 999px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.5), inset 0 2px 5px rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }

        /* CARD DEI SERVIZI (Molto più stonde) */
        .glass-card {
            background: rgba(43, 95, 173, 0.15);
            border-radius: 40px; 
            padding: 40px 30px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), background 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            background: rgba(43, 95, 173, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4), inset 0 2px 15px rgba(255,255,255,0.1);
        }

        /* Testo LUBOO */
        .luboo-title {
            font-size: clamp(5rem, 15vw, 9rem); 
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0.02em;
            background: linear-gradient(to bottom right, #ffffff, #88c8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            filter: drop-shadow(0px 10px 20px rgba(0,0,0,0.5));
            margin-bottom: 5px;
        }

        /* CONTENITORE ICONE (Bolla di vetro per far risaltare il colore originale) */
        .icon-bubble {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 50%; /* Cerchio perfetto */
            display: flex; justify-content: center; align-items: center;
            margin: 0 auto 24px auto;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2), inset 0 4px 10px rgba(255,255,255,0.2);
        }

        /* Icone con i loro colori originali */
        .feature-icon {
            width: 45px; height: 45px; object-fit: contain;
            filter: drop-shadow(0px 4px 8px rgba(0,0,0,0.3)); 
            transition: transform 0.3s ease;
        }
        
        .glass-card:hover .feature-icon {
            transform: scale(1.15);
        }

        /* BOTTONI */
        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            background: linear-gradient(135deg, #FFB247 0%, #FF9020 100%); 
            padding: 16px 40px; color: white; font-weight: 800; font-size: 1.1rem; 
            border-radius: 999px; text-decoration: none; 
            box-shadow: 0 10px 25px rgba(255, 144, 32, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); 
            transition: all 0.3s; text-transform: uppercase; letter-spacing: 1.5px;
        }
        
        .btn-primary:hover { 
            transform: translateY(-4px) scale(1.02); 
            box-shadow: 0 15px 35px rgba(255, 144, 32, 0.6); 
        }

        .btn-secondary {
            display: inline-flex; justify-content: center; align-items: center;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);
            padding: 16px 40px; color: white; font-weight: 700; font-size: 1.1rem;
            border-radius: 999px; text-decoration: none; transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-4px);
        }

        /* Step counter per la sezione "Come funziona" */
        .step-bubble {
            width: 70px; height: 70px; 
            background: linear-gradient(135deg, #30A9FF, #14364F);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 900; color: white; margin: 0 auto 1.5rem auto;
            box-shadow: 0 15px 30px rgba(48, 169, 255, 0.4), inset 0 3px 8px rgba(255,255,255,0.3);
            border: 2px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 w-full z-50 pt-4 px-4 md:px-8">
        <header class="glass-nav max-w-7xl mx-auto flex justify-between items-center py-3 px-6 md:px-8">
            <div class="flex items-center gap-3">
                <img src="src/Logo.png" alt="Logo" class="h-9 object-contain drop-shadow-lg" onerror="this.style.display='none'">
                <span class="font-bold text-lg md:text-xl tracking-wider text-white">Luboo</span>
            </div>
            <nav class="hidden md:flex gap-8 text-sm font-bold uppercase tracking-wide text-white/80">
                <a href="#servizi" class="hover:text-white hover:scale-105 transition-all">Servizi</a>
                <a href="#come-funziona" class="hover:text-white hover:scale-105 transition-all">Come Funziona</a>
            </nav>
            <div>
                <a href="front-page.php" class="btn-primary py-2 px-6 text-xs md:text-sm">Login</a>
            </div>
        </header>
    </div>

    <section class="hero-section min-h-[90vh] flex flex-col items-center justify-center text-center px-4 pt-32">
        <div class="luboo-title animate-[fadeIn_1s_ease-out]">LUBOO</div>
        <h1 class="text-3xl md:text-5xl font-bold text-blue-100 mb-6 tracking-wide drop-shadow-md max-w-4xl mx-auto">
            L'hub per organizzare i tuoi spazi lavorativi.
        </h1>
        <p class="text-lg md:text-xl text-white/80 max-w-2xl mb-12 leading-relaxed font-medium">
            Semplifica la tua giornata. Prenota postazioni, sale riunioni e posti auto in un unico portale aziendale sicuro, comodo e accessibile ovunque.
        </p>
        <div class="flex flex-col sm:flex-row gap-5">
            <a href="front-page.php" class="btn-primary">
                Accedi al Portale
            </a>
            <a href="#servizi" class="btn-secondary">
                Scopri di più
            </a>
        </div>
    </section>

    <section id="servizi" class="py-24 px-6 md:px-12 max-w-7xl mx-auto relative z-10 -mt-10">
        <div class="text-center mb-16">
            <h2 class="text-sm font-black text-[#30A9FF] uppercase tracking-[0.2em] mb-2">Cosa Offriamo</h2>
            <h3 class="text-3xl md:text-4xl font-bold">Tutto lo spazio di cui hai bisogno</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/PostazioneBase.svg" alt="Postazioni Desk" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-2xl font-bold mb-4 tracking-wide">Postazioni Desk</h4>
                <p class="text-white/75 leading-relaxed font-medium">
                    Prenota in anticipo la tua scrivania. Scegli tra postazioni base o postazioni tech dotate di monitor aggiuntivi per il massimo della produttività.
                </p>
            </div>

            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/Riunioni.svg" alt="Sale Riunioni" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-2xl font-bold mb-4 tracking-wide">Sale Riunioni</h4>
                <p class="text-white/75 leading-relaxed font-medium">
                    Organizza meeting perfetti. Verifica la capienza della sala e riservala per il tempo necessario, invitando il tuo team senza accavallamenti.
                </p>
            </div>

            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/PostoAuto.svg" alt="Posti Auto" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-2xl font-bold mb-4 tracking-wide">Posti Auto</h4>
                <p class="text-white/75 leading-relaxed font-medium">
                    Non perdere tempo a cercare parcheggio. Riserva il tuo posto auto standard o per disabili direttamente dal comodo portale interattivo.
                </p>
            </div>
        </div>
    </section>

    <section id="come-funziona" class="py-24 px-6 bg-[#14364F]/20 relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-sm font-black text-[#FFB247] uppercase tracking-[0.2em] mb-2">Processo Semplificato</h2>
                <h3 class="text-3xl md:text-4xl font-bold">Come funziona il sistema?</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="flex flex-col items-center text-center">
                    <div class="step-bubble">1</div>
                    <h4 class="font-bold text-xl mb-3">Accedi</h4>
                    <p class="text-white/60 font-medium">Effettua il login al portale in modo sicuro con le tue credenziali aziendali protette.</p>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="step-bubble">2</div>
                    <h4 class="font-bold text-xl mb-3">Scegli Data</h4>
                    <p class="text-white/60 font-medium">Seleziona dal calendario il giorno in cui sarai in sede e la fascia oraria di interesse.</p>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="step-bubble">3</div>
                    <h4 class="font-bold text-xl mb-3">Esplora Mappa</h4>
                    <p class="text-white/60 font-medium">Visualizza la planimetria interattiva degli uffici o del parcheggio e clicca lo spazio libero.</p>
                </div>
                <div class="flex flex-col items-center text-center">
                    <div class="step-bubble">4</div>
                    <h4 class="font-bold text-xl mb-3">Conferma</h4>
                    <p class="text-white/60 font-medium">Con un solo clic la prenotazione è registrata. Troverai il riepilogo nella tua dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 text-center bg-gradient-to-t from-[#070A0E] to-transparent">
        <h3 class="text-3xl md:text-4xl font-bold mb-8">Pronto per organizzare la tua giornata?</h3>
        <a href="front-page.php" class="btn-primary text-lg px-10 py-5">Vai al Login del sistema</a>
    </section>

    <footer class="bg-[#070A0E] py-14 px-6 md:px-12 border-t border-white/10 text-white/50 text-sm">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
            
            <div class="flex flex-col items-center md:items-start gap-2">
                <div class="font-bold text-white/80 text-xl tracking-wider mb-1 flex items-center gap-2">
                    <img src="src/Logo.png" alt="Logo" class="h-6 object-contain opacity-80" onerror="this.style.display='none'">
                    Luboo
                </div>
                <p>&copy; 2026 Tutti i diritti riservati.</p>
            </div>

            <div class="text-center md:text-right flex flex-col items-center md:items-end gap-1 bg-white/5 p-4 rounded-3xl border border-white/5">
                <span class="uppercase tracking-[0.15em] text-xs font-bold text-[#FFB247]">Developed for</span>
                <span class="text-xl font-black text-white uppercase tracking-widest drop-shadow-md">Azienda Z Volta</span>
                <p class="text-xs mt-1 text-white/40">Gestione Spazi Aziendali e Smart Working</p>
            </div>

        </div>
    </footer>

</body>
</html>