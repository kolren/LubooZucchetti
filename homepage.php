<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Home - LubooZucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Tipografia e Sfondo Base allineati al gestionale */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #04101A; /* Colore base scuro del progetto */
            color: #F1F6FF;
            margin: 0;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* ================================
           CUSTOM SCROLLBAR (Stile App)
           ================================ */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(54, 164, 130, 0.5); }

        /* Sfondo della sezione Hero con gradienti radiali morbidi */
        .hero-section {
            background: radial-gradient(circle at top, #0A2338 0%, #04101A 70%);
            position: relative;
            padding-bottom: 5rem;
        }

        /* Effetti Glassmorphism universali */
        .glass-panel {
            background: rgba(10, 35, 56, 0.6); 
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        /* NAVBAR FLUTTUANTE STONDATA */
        .glass-nav {
            background: rgba(10, 35, 56, 0.85); 
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 29px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        /* CARD DEI SERVIZI */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 24px; 
            padding: 40px 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            transition: transform 0.4s ease, background 0.3s ease, border 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(54, 164, 130, 0.3); /* Verde accento al passaggio */
        }

        /* Testo LUBOO */
        .luboo-title {
            font-size: clamp(5rem, 15vw, 9rem); 
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0.02em;
            background: linear-gradient(135deg, #FFFFFF 0%, #36A482 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            filter: drop-shadow(0px 10px 20px rgba(0,0,0,0.5));
            margin-bottom: 5px;
        }

        /* CONTENITORE ICONE */
        .icon-bubble {
            width: 80px; height: 80px;
            background: rgba(10, 35, 56, 0.8);
            border-radius: 20px; 
            display: flex; justify-content: center; align-items: center;
            margin: 0 auto 24px auto;
            border: 1px solid rgba(54, 164, 130, 0.3);
            box-shadow: inset 0 0 15px rgba(54,164,130,0.1);
        }

        .feature-icon {
            width: 40px; height: 40px; object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .glass-card:hover .feature-icon { transform: scale(1.15); }

        /* BOTTONI (Allineati ai bottoni del gestionale) */
        .btn-primary {
            display: inline-flex; justify-content: center; align-items: center;
            background: #36A482; 
            border: 1px solid rgba(54, 164, 130, 0.5);
            padding: 14px 36px; color: white; font-weight: 800; font-size: 1rem; 
            border-radius: 14px; text-decoration: none; 
            box-shadow: 0 10px 25px rgba(54, 164, 130, 0.3); 
            transition: all 0.3s; text-transform: uppercase; tracking-wider;
        }
        
        .btn-primary:hover { 
            background: #2c8569;
            transform: translateY(-2px); 
            box-shadow: 0 15px 35px rgba(54, 164, 130, 0.5); 
        }

        .btn-secondary {
            display: inline-flex; justify-content: center; align-items: center;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            padding: 14px 36px; color: white; font-weight: 800; font-size: 1rem;
            border-radius: 14px; text-decoration: none; transition: all 0.3s;
            text-transform: uppercase; tracking-wider;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Step counter per la sezione "Come funziona" */
        .step-bubble {
            width: 60px; height: 60px; 
            background: linear-gradient(135deg, #1D7F75, #36A482);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 900; color: white; margin: 0 auto 1.5rem auto;
            box-shadow: 0 10px 20px rgba(54, 164, 130, 0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transform: rotate(3deg);
        }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 w-full z-50 pt-4 px-4 md:px-8">
        <header class="glass-nav max-w-7xl mx-auto flex justify-between items-center py-3 px-6 md:px-8">
            <div class="flex items-center gap-3">
                <img src="src/Logo.png" alt="Logo" class="h-9 object-contain drop-shadow-md" onerror="this.style.display='none'">
                <span class="font-black text-lg md:text-xl tracking-wider text-white uppercase">Luboo<span class="text-[#36A482]">Zucchetti</span></span>
            </div>
            <nav class="hidden md:flex gap-8 text-sm font-bold uppercase tracking-wide text-[#BFD6E8]">
                <a href="#servizi" class="hover:text-white hover:scale-105 transition-all">Servizi</a>
                <a href="#come-funziona" class="hover:text-white hover:scale-105 transition-all">Come Funziona</a>
            </nav>
            <div>
                <a href="front-page.php" class="btn-primary py-2.5 px-6 text-xs md:text-sm shadow-none">Login Portale</a>
            </div>
        </header>
    </div>

    <section class="hero-section min-h-[90vh] flex flex-col items-center justify-center text-center px-4 pt-32">
        <div class="luboo-title animate-[fadeIn_1s_ease-out]">LUBOO</div>
        <h1 class="text-2xl md:text-4xl font-black text-[#BFD6E8] mb-6 tracking-wide drop-shadow-md max-w-4xl mx-auto uppercase">
            L'hub per organizzare i tuoi spazi lavorativi
        </h1>
        <p class="text-lg md:text-xl text-white/70 max-w-2xl mb-10 leading-relaxed font-semibold">
            Semplifica la tua giornata. Prenota postazioni, sale riunioni e posti auto in un unico portale aziendale sicuro, dinamico e in tempo reale.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
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
            <h2 class="text-xs font-black text-[#36A482] uppercase tracking-[0.2em] mb-2">Cosa Offriamo</h2>
            <h3 class="text-3xl md:text-4xl font-black text-white">Gestione Spazi Intelligente</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/PostazioneBase.svg" alt="Postazioni Desk" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-xl font-bold mb-3 tracking-wide text-white uppercase">Postazioni Desk</h4>
                <p class="text-[#BFD6E8] text-sm leading-relaxed font-semibold">
                    Prenota in anticipo la tua scrivania. Scegli tra postazioni base o postazioni tech dotate di monitor aggiuntivi per il massimo della produttività.
                </p>
            </div>

            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/Riunioni.svg" alt="Sale Riunioni" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-xl font-bold mb-3 tracking-wide text-white uppercase">Sale Riunioni</h4>
                <p class="text-[#BFD6E8] text-sm leading-relaxed font-semibold">
                    Organizza meeting perfetti. Verifica la capienza della sala e riservala per il tempo necessario, evitando accavallamenti tra i team.
                </p>
            </div>

            <div class="glass-card flex flex-col text-center">
                <div class="icon-bubble">
                    <img src="src/Icone/PostoAuto.svg" alt="Posti Auto" class="feature-icon" onerror="this.style.display='none'">
                </div>
                <h4 class="text-xl font-bold mb-3 tracking-wide text-white uppercase">Posti Auto</h4>
                <p class="text-[#BFD6E8] text-sm leading-relaxed font-semibold">
                    Non perdere tempo a cercare parcheggio. Riserva il tuo posto auto standard o per disabili direttamente dalla planimetria interattiva.
                </p>
            </div>
        </div>
    </section>

    <section id="come-funziona" class="py-24 px-6 bg-[#071B2B]/50 border-y border-white/5 relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20">
                <h2 class="text-xs font-black text-[#BFD6E8] uppercase tracking-[0.2em] mb-2">Processo Semplificato</h2>
                <h3 class="text-3xl md:text-4xl font-black text-white">Flusso di Prenotazione</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="glass-panel p-6 rounded-[24px] flex flex-col items-center text-center hover:-translate-y-2 transition-transform">
                    <div class="step-bubble">1</div>
                    <h4 class="font-bold text-lg mb-2 text-white uppercase tracking-wider">Accedi</h4>
                    <p class="text-[#BFD6E8] text-sm font-medium">Effettua il login in modo sicuro con le tue credenziali aziendali.</p>
                </div>
                <div class="glass-panel p-6 rounded-[24px] flex flex-col items-center text-center hover:-translate-y-2 transition-transform">
                    <div class="step-bubble">2</div>
                    <h4 class="font-bold text-lg mb-2 text-white uppercase tracking-wider">Scegli Data</h4>
                    <p class="text-[#BFD6E8] text-sm font-medium">Seleziona giorno e fascia oraria. I limiti si adattano al tuo ruolo.</p>
                </div>
                <div class="glass-panel p-6 rounded-[24px] flex flex-col items-center text-center hover:-translate-y-2 transition-transform">
                    <div class="step-bubble">3</div>
                    <h4 class="font-bold text-lg mb-2 text-white uppercase tracking-wider">Esplora</h4>
                    <p class="text-[#BFD6E8] text-sm font-medium">Visualizza la mappa interattiva aggiornata in tempo reale.</p>
                </div>
                <div class="glass-panel p-6 rounded-[24px] flex flex-col items-center text-center hover:-translate-y-2 transition-transform">
                    <div class="step-bubble">4</div>
                    <h4 class="font-bold text-lg mb-2 text-white uppercase tracking-wider">Conferma</h4>
                    <p class="text-[#BFD6E8] text-sm font-medium">Con un clic riservi lo spazio. Monitoralo poi nella tua Dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 text-center bg-gradient-to-t from-[#04101A] to-transparent relative">
        <h3 class="text-2xl md:text-3xl font-black mb-8 text-white uppercase tracking-wide">Pronto per organizzare la tua giornata?</h3>
        <a href="front-page.php" class="btn-primary text-lg px-12 py-4">Entra nel Portale</a>
    </section>

    <footer class="bg-[#04101A] py-10 px-6 md:px-12 border-t border-white/10 text-white/50 text-sm">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
            
            <div class="flex flex-col items-center md:items-start gap-2">
                <div class="font-black text-white/80 text-xl tracking-wider mb-1 flex items-center gap-2 uppercase">
                    <img src="src/Logo.png" alt="Logo" class="h-6 object-contain opacity-80" onerror="this.style.display='none'">
                    Luboo<span class="text-[#36A482]">Zucchetti</span>
                </div>
                <p class="font-semibold text-xs text-[#BFD6E8]/50">&copy; 2026 Tutti i diritti riservati.</p>
            </div>

            <div class="text-center md:text-right flex flex-col items-center md:items-end gap-1 bg-[#0A2338]/50 p-4 rounded-2xl border border-white/5 shadow-inner">
                <span class="uppercase tracking-[0.15em] text-[10px] font-bold text-[#36A482]">Sistema proprietario per</span>
                <span class="text-lg font-black text-white uppercase tracking-widest drop-shadow-md">Luboo Zucchetti</span>
                <p class="text-[10px] mt-1 text-[#BFD6E8]/40 font-bold uppercase">Gestione Spazi & Comunicazione Aziendale</p>
            </div>

        </div>
    </footer>

</body>
</html>