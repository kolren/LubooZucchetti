<?php
session_start();
// Resettiamo eventuali vecchie verifiche quando si ricarica la pagina
$_SESSION['captcha_verified'] = false;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - BookSystem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ===============================================
           TIPOGRAFIA E STILI BASE
           =============================================== */
        @font-face { 
            font-family: 'SF Pro Rounded'; 
            src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2'); 
        }
        
        body {
            font-family: 'SF Pro Rounded', 'Nunito', sans-serif;
            background: linear-gradient(135deg, #30A9FF 0%, #14364F 50%, #0B0F15 100%);
            min-height: 100vh; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; overflow: hidden;
        }

        .glass-card {
            width: 90%; max-width: 380px; background: rgba(43, 95, 173, 0.25);
            border-radius: 40px; padding: 40px 30px;
            backdrop-filter: blur(30px) saturate(120%); -webkit-backdrop-filter: blur(30px) saturate(120%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 40px 80px -10px rgba(0,0,0,0.6), inset 0 2px 20px rgba(255,255,255,0.1);
            position: relative; z-index: 10;
        }

        .input-group label { 
            color: rgba(255,255,255,0.9); font-size: 0.75rem; font-weight: 800; margin-left: 20px; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.1em; 
        }
        
        .glass-input {
            width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 16px 24px; color: white; font-weight: 600; font-size: 1rem; border-radius: 999px; outline: none; transition: all 0.3s;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        .glass-input::placeholder { color: rgba(255, 255, 255, 0.4); font-weight: 400; }
        .glass-input:focus { background: rgba(0,0,0,0.4); border-color: #30A9FF; box-shadow: 0 0 0 4px rgba(48,169,255,0.2), inset 0 2px 4px rgba(0,0,0,0.2); }
        
        .toggle-password { position: absolute; right: 20px; top: 40px; cursor: pointer; color: rgba(255,255,255,0.6); display: flex; align-items: center; justify-content: center; transition: 0.2s;}
        .toggle-password:hover { color: white; }

        .captcha-trigger-btn {
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 14px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; width: 100%; border-radius: 999px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .captcha-trigger-btn:hover { background: rgba(255, 255, 255, 0.2); transform: translateY(-2px); }
        .captcha-trigger-btn.verified { background: rgba(0, 224, 150, 0.25); border-color: rgba(0, 224, 150, 0.5); pointer-events: none; transform: translateY(0); box-shadow: 0 0 20px rgba(0, 224, 150, 0.2), inset 0 0 10px rgba(0, 224, 150, 0.2); }

        /* ===============================================
           POPUP DEL CAPTCHA & IMMAGINI
           =============================================== */
        #captcha-popout {
            position: absolute; left: 105%; top: -60px; width: 320px;
            background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 28px; padding: 24px; display: flex; flex-direction: column; gap: 16px;
            opacity: 0; visibility: hidden; transform: scale(0.9) translateX(-10px); transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); z-index: 50; box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        }
        #captcha-popout.active { opacity: 1; visibility: visible; transform: scale(1) translateX(0); }

        /* Sfondo Fisso */
        .puzzle-stage {
            width: 100%; height: 160px; border-radius: 16px; position: relative; overflow: hidden;
            background-image: url('src/CaptchaBg.png'); 
            background-size: 272px 160px; /* Esatta larghezza interna (320px - 48px padding) */
            background-position: top left;
            border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: inset 0 4px 20px rgba(0,0,0,0.5); margin-bottom: 8px;
        }
        
        /* Il buco oscurato */
        .puzzle-hole {
            width: 55px; height: 55px; position: absolute; top: 52px;
            background: rgba(0, 0, 0, 0.6); border-radius: 8px;
            box-shadow: inset 0 4px 10px rgba(0,0,0,0.9); border: 1px dashed rgba(255,255,255,0.4); display: none;
        }

        /* Il pezzo da trascinare (stesso BG ma spostato) */
        .puzzle-piece {
            width: 55px; height: 55px; position: absolute; top: 52px; left: 10px;
            background-image: url('src/CaptchaBg.png');
            background-size: 272px 160px;
            /* background-position viene calcolato dinamicamente nel JS per combaciare! */
            z-index: 20; border-radius: 8px;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.7)); transition: left 0.05s linear; cursor: grab;
            border: 2px solid rgba(255,255,255,0.8); box-shadow: inset 0 0 10px rgba(255,255,255,0.4);
        }
        .puzzle-piece:active { cursor: grabbing; transform: scale(1.05); }

        /* ===============================================
           SLIDER PIXEL PERFECT
           =============================================== */
        .slider-wrapper {
            position: relative; width: 100%; height: 50px; background: rgba(0, 0, 0, 0.4); border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; overflow: hidden; box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);
        }
        .slider-text {
            position: absolute; width: 100%; text-align: center; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; pointer-events: none; transition: opacity 0.3s; z-index: 1;
        }
        .slider-wrapper.dragging .slider-text { opacity: 0; }
        .slider-fill { position: absolute; height: 100%; width: 0%; left: 0; top: 0; background: rgba(48, 169, 255, 0.3); pointer-events: none; z-index: 0;}

        input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; z-index: 5; margin: 0; height: 100%; cursor: grab;}
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; height: 46px; width: 56px; border-radius: 23px;
            background: #ffffff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%2330A9FF" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>') center center no-repeat;
            background-size: 24px; box-shadow: 0 0 10px rgba(0,0,0,0.3), inset 0 -2px 5px rgba(0,0,0,0.1); border: 1px solid rgba(0,0,0,0.1); cursor: grab; transition: transform 0.1s;
        }
        input[type=range]::-webkit-slider-thumb:active { cursor: grabbing; transform: scale(1.05); }

        .submit-btn {
            background: linear-gradient(135deg, #FFB247 0%, #FF9020 100%); width: 100%; padding: 18px; color: white; font-weight: 800; font-size: 1.1rem; border-radius: 999px; border: none; margin-top: 10px; box-shadow: 0 10px 25px rgba(255, 144, 32, 0.4), inset 0 2px 5px rgba(255,255,255,0.3); transition: all 0.3s; cursor: pointer; text-transform: uppercase; letter-spacing: 1.5px;
        }
        .submit-btn:disabled { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.3); box-shadow: none; cursor: not-allowed; border: 1px solid rgba(255,255,255,0.1); }
        .submit-btn:not(:disabled):hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(255, 144, 32, 0.5); }

        .logo-container { margin-bottom: 32px; display: flex; justify-content: center; width: 100%; z-index: 10; }
        .footer-text { color: rgba(255, 255, 255, 0.4); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; margin-top: 32px; z-index: 10; }
    </style>
</head>
<body>

    <div class="logo-container">
        <img src="src/Logo.png" alt="BookSystem Logo" class="h-20 object-contain drop-shadow-2xl" onerror="this.style.display='none'">
    </div>

    <div class="glass-card">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black tracking-widest drop-shadow-lg mb-2">LOGIN</h1>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-500/20 border border-red-500/40 text-red-100 text-sm font-bold text-center py-3 rounded-2xl backdrop-blur-sm">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="loginhandle.php" method="POST" id="loginForm">
            <div class="input-group mb-5">
                <label>Username</label>
                <input type="text" name="username" class="glass-input" placeholder="es. mario.rossi" required autocomplete="off">
            </div>

            <div class="input-group mb-5 relative">
                <label>Password</label>
                <input type="password" name="password" id="password" class="glass-input" placeholder="inserisci password" required>
                <div class="toggle-password" onclick="togglePass()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
            </div>

            <div class="input-group relative mt-2 mb-8">
                <label>Sicurezza</label>
                
                <div id="captcha-trigger" class="captcha-trigger-btn">
                    <span id="captcha-status" class="text-sm font-bold uppercase tracking-widest text-white/90">Verifica Sicurezza</span>
                </div>

                <div id="captcha-popout">
                    <div class="flex justify-between items-center mb-1">
                        <div class="text-xs font-black uppercase text-white/80 tracking-widest">Verifica umana</div>
                    </div>
                    
                    <div class="puzzle-stage">
                        <div class="puzzle-hole" id="puzzle-hole"></div>
                        <div class="puzzle-piece" id="puzzle-piece"></div>
                    </div>

                    <div class="slider-wrapper" id="slider-container">
                        <div class="slider-fill" id="slider-fill"></div>
                        <span class="slider-text">Scorri per verificare</span>
                        <input type="range" min="0" max="100" value="0" id="captcha-slider">
                    </div>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="submit-btn" disabled>ACCEDI</button>
        </form>
    </div>
    
    <div class="footer-text">Powered by Luboo</div>

    <script>
        function togglePass() {
            const x = document.getElementById("password");
            x.type = x.type === "password" ? "text" : "password";
        }

        const trigger = document.getElementById('captcha-trigger');
        const popout = document.getElementById('captcha-popout');
        const slider = document.getElementById('captcha-slider');
        const piece = document.getElementById('puzzle-piece');
        const hole = document.getElementById('puzzle-hole');
        const submitBtn = document.getElementById('submit-btn');
        const statusText = document.getElementById('captcha-status');
        const sliderWrapper = document.getElementById('slider-container');
        const sliderFill = document.getElementById('slider-fill');

        let isVerified = false;
        const startOffset = 10;
        // La corsa massima è la larghezza del container (272px) meno la larghezza del pezzo (55px) meno l'offset iniziale (10px)
        const maxTravel = 207; 

        // Recupera la posizione del buco da captcha.php
        function initCaptcha() {
            fetch('captcha.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Posiziona il "buco"
                    hole.style.left = data.target + 'px';
                    hole.style.display = 'block';
                    // Ritaglia l'immagine nel pezzo per farla coincidere perfettamente col buco!
                    piece.style.backgroundPosition = `-${data.target}px -52px`;
                }
            });
        }

        trigger.addEventListener('click', (e) => {
            if (!isVerified) {
                e.stopPropagation();
                popout.classList.add('active');
                initCaptcha(); // Carica i dati del buco
            }
        });

        document.addEventListener('click', (e) => {
            if (!popout.contains(e.target) && !trigger.contains(e.target)) {
                popout.classList.remove('active');
            }
        });

        slider.addEventListener('input', (e) => {
            sliderWrapper.classList.add('dragging');
            const pct = e.target.value;
            const moveX = (pct / 100) * maxTravel;
            piece.style.left = (startOffset + moveX) + 'px';
            if (sliderFill) sliderFill.style.width = pct + '%';
        });

        slider.addEventListener('change', () => {
            sliderWrapper.classList.remove('dragging');
            const pct = slider.value;
            const currentPixelX = startOffset + ((pct / 100) * maxTravel);
            
            slider.disabled = true;
            statusText.innerText = "Controllo...";

            fetch('verifica.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ x: currentPixelX })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    isVerified = true;
                    popout.classList.remove('active');
                    trigger.classList.add('verified');
                    trigger.innerHTML = '<span style="color:#00E096; margin-right:8px; font-weight:bold; font-size: 1.2rem;">✓</span> <span class="text-sm font-bold uppercase tracking-widest" style="color:#00E096;">VERIFICATO</span>';
                    submitBtn.disabled = false;
                } else {
                    resetCaptcha();
                }
            })
            .catch(() => resetCaptcha());
        });

        function resetCaptcha() {
            slider.disabled = true; 
            slider.value = 0;
            piece.style.left = startOffset + 'px';
            if (sliderFill) sliderFill.style.width = '0%';
            statusText.innerText = "Verifica Fallita. Riprova.";
            statusText.style.color = "#FF4747";
            
            popout.classList.add('animate-pulse');
            setTimeout(() => {
                popout.classList.remove('animate-pulse');
                statusText.innerText = "Verifica Sicurezza";
                statusText.style.color = "";
            }, 1000);

            // Chiede una nuova posizione in caso di fallimento
            initCaptcha();
            setTimeout(() => { slider.disabled = false; }, 250);
        }
    </script>
</body>
</html>
