<?php 
session_start(); 
// Resettiamo le coordinate al caricamento della pagina per sicurezza
unset($_SESSION['puzzle_x']);
unset($_SESSION['captcha_verified']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Luboo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* --- TYPOGRAPHY & BASE --- */
        @font-face { font-family: 'SF Pro Rounded'; src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2'); }
        body {
            font-family: 'SF Pro Rounded', 'Nunito', sans-serif;
            background: linear-gradient(90deg, #30A9FF 0%, #14364F 60%, #0B0F15 100%);
            height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white;
        }

        /* --- GLASS EFFECT --- */
        .glass-card {
            width: 90%; max-width: 380px;
            background: rgba(43, 95, 173, 0.31);
            border-radius: 40px; padding: 30px 40px 40px;
            backdrop-filter: blur(30px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 40px 80px -10px rgba(0,0,0,0.6);
        }

        /* --- INPUTS --- */
        .input-group label { color: white; font-size: 0.7rem; font-weight: 700; margin-left: 18px; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.05em; }
        .glass-input {
            width: 100%; background: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 20px; color: white; border-radius: 999px; outline: none; transition: 0.3s;
        }
        .glass-input:focus { background: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.5); }
        .toggle-password { position: absolute; right: 15px; top: 36px; cursor: pointer; color: white; }

        /* --- CAPTCHA UI --- */
        .captcha-trigger-btn {
            background: rgba(0, 255, 115, 0.2); border: 1px solid rgba(0, 255, 115, 0.2);
            padding: 10px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; width: 100%; border-radius: 99px; margin-top: 5px; transition: 0.3s;
        }
        .captcha-trigger-btn.verified { background: rgba(0, 255, 115, 0.6); pointer-events: none; box-shadow: 0 0 15px rgba(0, 255, 115, 0.4); }

        #captcha-popout {
            position: absolute; left: 105%; top: -80px; width: 280px;
            background: rgba(100, 125, 162, 0.65); backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.3); border-radius: 28px;
            padding: 20px; display: flex; flex-direction: column; gap: 15px;
            opacity: 0; visibility: hidden; transform: scale(0.9) translateX(-10px);
            transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1); z-index: 50;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }
        #captcha-popout.active { opacity: 1; visibility: visible; transform: scale(1) translateX(0); }

        /* --- PUZZLE STAGE --- */
        .puzzle-stage {
            width: 100%; height: 140px; border-radius: 12px; position: relative; overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
            /* Carica BG Dinamico */
            background-image: url('captcha.php?mode=bg&t=<?php echo time(); ?>'); 
            background-size: cover; background-position: center;
        }

        /* La forma del puzzle viene applicata sia al pezzo che al "buco" visivo */
        .puzzle-shape {
             clip-path: path('M 10 10 L 20 10 Q 25 0 30 10 L 40 10 L 40 20 Q 50 25 40 30 L 40 40 L 30 40 Q 25 50 20 40 L 10 40 L 10 30 Q 0 25 10 20 L 10 10 Z');
        }

        .puzzle-piece {
            width: 50px; height: 50px; position: absolute;
            top: 45px; left: 10px; /* Start position */
            background-image: url('captcha.php?mode=piece&t=<?php echo time(); ?>');
            background-size: 50px 50px; /* Il PHP restituisce già il pezzo ritagliato 50x50 */
            z-index: 10;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.8));
            /* Applichiamo il clip-path al quadrato restituito da PHP per renderlo un puzzle */
        }
        
        /* SLIDER */
        .slider-container {
            position: relative; width: 100%; height: 44px; background: rgba(0,0,0,0.2);
            border-radius: 99px; display: flex; align-items: center; padding: 2px;
        }
        input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; z-index: 5; cursor: grab; margin: 0; }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; height: 40px; width: 48px; border-radius: 20px;
            background: #00B3A5; box-shadow: 0 2px 10px rgba(0,0,0,0.3); cursor: grab;
        }

        .submit-btn {
            background: rgba(255, 178, 71, 0.31); width: 100%; padding: 14px;
            color: white; font-weight: 700; border-radius: 99px;
            border: 1px solid rgba(255,178,71,0.2); margin-top: 15px;
            transition: 0.3s; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;
        }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }
        .submit-btn:not(:disabled):hover { background: rgba(255, 178, 71, 0.5); transform: scale(1.02); }
    </style>
</head>
<body>

    <div class="flex flex-col items-center gap-4 w-full px-4">
        
        <div class="w-auto h-20 flex items-center justify-center">
            <img src="src/Logo.png" alt="Logo" class="h-full object-contain drop-shadow-2xl"
                 onerror="this.style.display='none'; document.getElementById('fb-logo').style.display='flex'">
            <div id="fb-logo" class="hidden w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md items-center justify-center font-bold text-2xl">L</div>
        </div>

        <div class="glass-card">
            <h1 class="text-center text-3xl font-bold tracking-widest mb-6 drop-shadow-lg">LOGIN</h1>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="mb-5 text-red-200 text-sm font-semibold text-center bg-red-500/20 py-2 rounded-xl border border-red-500/30">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="loginhandle.php" method="POST" id="loginForm">
                
                <div class="input-group mb-4">
                    <label>Username</label>
                    <input type="text" name="username" class="glass-input" placeholder="Utente" required>
                </div>

                <div class="input-group mb-4 relative">
                    <label>Password</label>
                    <input type="password" name="password" id="password" class="glass-input" placeholder="••••••••" required>
                    <div class="toggle-password" onclick="togglePass()">👁</div>
                </div>

                <div class="input-group relative mt-2">
                    <label>Sicurezza</label>
                    
                    <div id="captcha-trigger" class="captcha-trigger-btn">
                        <span id="captcha-status" class="text-xs font-bold uppercase tracking-wide">Clicca per verificare</span>
                    </div>

                    <div id="captcha-popout">
                        <div class="text-xs font-bold uppercase ml-1 opacity-80">Completa il puzzle</div>
                        
                        <div class="puzzle-stage">
                            <div class="puzzle-piece puzzle-shape" id="puzzle-piece"></div>
                        </div>

                        <div class="slider-container">
                            <input type="range" min="0" max="100" value="0" id="captcha-slider">
                        </div>
                    </div>
                </div>

                <button type="submit" id="submit-btn" class="submit-btn" disabled>Accedi</button>
            </form>
        </div>
        
        <div class="text-white/40 text-xs font-medium tracking-wide mt-3">Powered by Luboo</div>
    </div>

    <script>
        function togglePass() {
            const x = document.getElementById("password");
            x.type = x.type === "password" ? "text" : "password";
        }

        // --- LOGICA CAPTCHA ---
        const trigger = document.getElementById('captcha-trigger');
        const popout = document.getElementById('captcha-popout');
        const slider = document.getElementById('captcha-slider');
        const piece = document.getElementById('puzzle-piece');
        const submitBtn = document.getElementById('submit-btn');
        const statusText = document.getElementById('captcha-status');

        let isVerified = false;

        // Apertura Popout
        trigger.addEventListener('click', (e) => {
            if (!isVerified) {
                e.stopPropagation();
                popout.classList.add('active');
                // Quando apriamo, ricarichiamo l'immagine se necessario per evitare cache
                // (Opzionale, qui gestito dal timestamp in PHP)
            }
        });

        // Chiudi click fuori
        document.addEventListener('click', (e) => {
            if (!popout.contains(e.target) && !trigger.contains(e.target)) {
                popout.classList.remove('active');
            }
        });

        // Movimento (Solo visuale lato client)
        // Stage width approx 240px (dipende dal padding).
        // Piece width 50px. Max travel ~190px.
        const maxTravel = 190;
        const startOffset = 10;

        slider.addEventListener('input', (e) => {
            const pct = e.target.value;
            const moveX = (pct / 100) * maxTravel;
            piece.style.left = (startOffset + moveX) + 'px';
        });

        // Verifica (Chiamata al server PHP)
        slider.addEventListener('change', () => {
            const pct = slider.value;
            const currentPixelX = startOffset + ((pct / 100) * maxTravel);
            
            // Disabilita slider durante verifica
            slider.disabled = true;
            statusText.innerText = "Verifica in corso...";

            fetch('verify.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ x: currentPixelX })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Successo
                    isVerified = true;
                    popout.classList.remove('active');
                    trigger.classList.add('verified');
                    statusText.innerText = "VERIFICATO";
                    trigger.innerHTML = '<span class="font-bold text-white">✓ VERIFICATO</span>';
                    submitBtn.disabled = false;
                } else {
                    // Errore
                    resetCaptcha();
                }
            })
            .catch(() => resetCaptcha());
        });

        function resetCaptcha() {
            slider.disabled = false;
            slider.value = 0;
            piece.style.left = startOffset + 'px';
            statusText.innerText = "Riprova";
            
            // Animazione errore
            popout.classList.add('animate-pulse');
            setTimeout(() => popout.classList.remove('animate-pulse'), 500);
        }
    </script>
</body>
</html>
