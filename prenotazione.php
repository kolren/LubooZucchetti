<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazioni - Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* --- TYPOGRAPHY --- */
        @font-face {
            font-family: 'SF Pro Rounded';
            src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2');
            font-weight: normal;
        }

        body {
            font-family: 'SF Pro Rounded', 'Nunito', -apple-system, sans-serif;
            /* Gradient Background: #30A9FF -> Dark */
            background: linear-gradient(45deg, #30A9FF 0%, #001e3b 35%, #000000 100%);
            background-attachment: fixed;
            background-size: cover;
            height: 100vh;
            color: white;
            overflow: hidden; 
        }

        /* --- NAVBAR FLOTTANTE (INTEGRATA) --- */
        .nav-glass-container {
            background: rgba(15, 25, 45, 0.65);
            backdrop-filter: blur(30px) saturate(140%);
            -webkit-backdrop-filter: blur(30px) saturate(140%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.4), inset 0 0 15px rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            transition: all 0.3s ease;
        }

        .nav-item {
            font-family: 'SF Pro Rounded', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.65);
            padding: 10px 20px;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.05);
        }

        /* --- DASHBOARD GLASS PANELS --- */
        .glass-panel {
            background: rgba(0, 51, 128, 0.24);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2), inset 0 0 20px rgba(255, 255, 255, 0.05);
        }

        /* --- ADMIN BADGE STYLE --- */
        .admin-badge-bg {
            background-color: #014d4e; /* Teal scuro */
        }
        
        /* SIDEBAR ITEMS */
        .sidebar-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            background: linear-gradient(90deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
        }
        
        .sidebar-item.active-blue {
            background: linear-gradient(90deg, rgba(100, 149, 237, 0.4) 0%, rgba(65, 105, 225, 0.1) 100%);
            border-color: rgba(100, 149, 237, 0.5);
            box-shadow: 0 0 20px rgba(65, 105, 225, 0.2);
        }
        .sidebar-item.active-green {
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.4) 0%, rgba(6, 95, 70, 0.1) 100%);
            border-color: rgba(52, 211, 153, 0.5);
        }

        /* UTILS */
        .pill-shape { border-radius: 999px; }
        .map-lines {
            background-image: 
                linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* INPUTS */
        .glass-input {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            text-align: center;
        }
        
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="flex flex-col h-screen p-4 md:p-6 overflow-hidden relative">

    <div class="relative w-full h-24 mb-4 shrink-0">
        
        <nav class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50">
            <div class="nav-glass-container flex items-center gap-1 px-2 py-2">
                
                <a href="#" class="nav-item pill-shape">Dipendenti</a>
                <a href="#" class="nav-item pill-shape active">Prenota</a>
                <a href="#" class="nav-item pill-shape">DashBoard</a>
                <a href="#" class="nav-item pill-shape">Gestisci</a>

            </div>
        </nav>

        <div class="absolute top-2 left-6 z-30">
            <img src="assets/icons/logo.png" alt="L" class="h-12 w-auto drop-shadow-lg opacity-80"
                 onerror="this.style.display='none';"> </div>

        <div class="absolute top-2 left-28 z-20 flex flex-col">
            
            <div class="admin-badge-bg h-10 px-6 flex items-center rounded-t-2xl rounded-br-2xl rounded-bl-none relative ml-8 shadow-lg">
                <span class="text-white font-bold text-lg tracking-wide">Amministratore</span>
                <div class="absolute -bottom-5 left-0 w-5 h-5 overflow-hidden">
                     <div class="w-5 h-5 rounded-tr-xl shadow-[5px_-5px_0_0_#014d4e]"></div>
                </div>
            </div>

            <div class="admin-badge-bg h-8 px-5 flex items-center gap-4 rounded-b-xl rounded-l-xl rounded-tr-none shadow-lg mt-[0px]">
                <span class="text-white font-bold text-sm">Ciao Valentina!</span>
                <div class="flex gap-2 text-[10px] text-teal-200 font-medium">
                    <a href="#" class="hover:text-white transition-colors">Esci</a>
                    <a href="#" class="hover:text-white transition-colors">Cambia</a>
                    <a href="#" class="hover:text-white transition-colors">Modifica</a>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex gap-6 overflow-hidden min-h-0 pt-4"> <aside class="w-1/4 glass-panel rounded-[30px] p-6 flex flex-col shadow-2xl">
            <h2 class="text-xl font-medium mb-6 pl-2 tracking-wide">Prenotazioni disponibili</h2>
            
            <div class="flex flex-col gap-4 overflow-y-auto pr-1 flex-1">
                
                <div onclick="selectType('base', this)" class="sidebar-item active-blue cursor-pointer p-4 rounded-2xl flex items-center gap-4 group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <img src="assets/icons/desk_base.png" class="w-8 h-8 object-contain opacity-90" onerror="this.style.opacity='0.5'">
                    </div>
                    <div>
                        <p class="font-bold text-white text-base">Postazione Base</p>
                        <p class="text-[10px] text-blue-200/70 leading-tight mt-0.5">Scrivania + Cassettiera</p>
                    </div>
                </div>

                <div onclick="selectType('tech', this)" class="sidebar-item cursor-pointer p-4 rounded-2xl flex items-center gap-4 group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <img src="assets/icons/desk_tech.png" class="w-8 h-8 object-contain opacity-90" onerror="this.style.opacity='0.5'">
                    </div>
                    <div>
                        <p class="font-bold text-white text-base">Postazione Tech</p>
                        <p class="text-[10px] text-blue-200/70 leading-tight mt-0.5">Dual Monitor + Dock</p>
                    </div>
                </div>

                <div onclick="selectType('meeting', this)" class="sidebar-item cursor-pointer p-4 rounded-2xl flex items-center gap-4 group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <img src="assets/icons/meeting.png" class="w-8 h-8 object-contain opacity-90" onerror="this.style.opacity='0.5'">
                    </div>
                    <div>
                        <p class="font-bold text-white text-base">Sala Riunioni</p>
                        <p class="text-[10px] text-blue-200/70 leading-tight mt-0.5">Proiettore + Whiteboard</p>
                    </div>
                </div>

                <div onclick="selectType('auto', this, true)" class="sidebar-item cursor-pointer p-4 rounded-2xl flex items-center gap-4 group bg-gradient-to-r from-emerald-900/20 to-transparent">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-emerald-500/30">
                        <img src="assets/icons/car.png" class="w-8 h-8 object-contain opacity-90" onerror="this.style.opacity='0.5'">
                    </div>
                    <div>
                        <p class="font-bold text-emerald-300 text-base">Posto Auto</p>
                        <p class="text-[10px] text-emerald-200/60 leading-tight mt-0.5">Garage Interrato</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-white/5 space-y-2 pl-2">
                <p class="text-xs font-bold mb-2 opacity-80">Legenda</p>
                <div class="flex items-center gap-2 text-[10px] opacity-70"><span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_5px_lime]"></span> Disponibile</div>
                <div class="flex items-center gap-2 text-[10px] opacity-70"><span class="w-2.5 h-2.5 rounded-full bg-red-600 shadow-[0_0_5px_red]"></span> Occupato</div>
                <div class="flex items-center gap-2 text-[10px] opacity-70"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Selezione</div>
            </div>
        </aside>

        <section class="flex-1 glass-panel rounded-[40px] p-8 relative flex flex-col shadow-2xl border-t border-white/20">
            
            <div class="flex justify-between items-start mb-6">
                <h1 id="dynamic-title" class="text-3xl font-bold text-white drop-shadow-md transition-opacity duration-300">Postazione Base</h1>
                
                <div class="relative">
                    <select class="appearance-none bg-[#7c3aed]/30 border border-[#7c3aed]/50 text-white py-1.5 pl-4 pr-10 rounded-full text-sm font-medium focus:outline-none cursor-pointer backdrop-blur-md shadow-lg transition-all hover:bg-[#7c3aed]/40">
                        <option class="text-black">Piano 1</option>
                        <option class="text-black">Piano 2</option>
                        <option class="text-black">Piano 3</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex gap-8">
                
                <div class="flex-1 border-2 border-white/20 rounded-3xl relative map-lines flex items-center justify-center p-8 group overflow-hidden">
                    <div class="border-2 border-dashed border-white/30 rounded-xl w-3/4 h-3/4 flex items-center justify-center relative transition-transform duration-500 group-hover:scale-105">
                        
                        <div class="absolute top-4 left-4 w-10 h-10 border border-white/50 rounded-lg flex items-center justify-center hover:bg-green-500/20 cursor-pointer transition-colors"><div class="w-2 h-2 bg-green-500 rounded-full shadow-[0_0_8px_lime]"></div></div>
                        <div class="absolute top-4 left-16 w-10 h-10 border border-white/50 rounded-lg flex items-center justify-center hover:bg-green-500/20 cursor-pointer transition-colors"><div class="w-2 h-2 bg-green-500 rounded-full shadow-[0_0_8px_lime]"></div></div>
                        <div class="absolute top-4 left-28 w-10 h-10 border border-white/50 rounded-lg flex items-center justify-center hover:bg-green-500/20 cursor-pointer transition-colors"><div class="w-2 h-2 bg-green-500 rounded-full shadow-[0_0_8px_lime]"></div></div>
                        
                        <div class="w-32 h-20 border border-white/40 rounded-lg flex items-center justify-center opacity-50"><span class="text-xs font-bold">AREA UFFICI</span></div>

                        <div class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-2">
                             <div class="w-8 h-12 border border-white/30 rounded flex items-center justify-center hover:bg-green-500/20 cursor-pointer"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div></div>
                             <div class="w-8 h-12 border border-white/30 rounded flex items-center justify-center hover:bg-green-500/20 cursor-pointer"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div></div>
                             <div class="w-8 h-12 border border-white/30 rounded flex items-center justify-center hover:bg-green-500/20 cursor-pointer"><div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div></div>
                        </div>
                    </div>
                </div>

                <div class="w-72 flex flex-col">
                    <h3 class="text-lg font-medium mb-4 text-gray-200">Dettagli Prenotazione</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] uppercase text-gray-400 tracking-wider pl-1">Posizione</label>
                                <div class="bg-[#9333ea]/30 border border-[#9333ea]/50 rounded-xl py-2 text-center font-bold text-lg shadow-inner text-white">A1</div>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase text-gray-400 tracking-wider pl-1">ID Asset</label>
                                <div class="bg-[#0d9488]/30 border border-[#0d9488]/50 rounded-xl py-2 text-center font-bold text-lg shadow-inner text-white">042</div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] text-gray-400 pl-1 uppercase font-bold">Data Selezionata</label>
                            <input type="text" value="<?php echo date('d/m/Y'); ?>" class="w-full glass-input rounded-xl py-2 px-4 focus:outline-none font-mono text-sm" readonly>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] text-gray-400 pl-1 uppercase font-bold">Inizio</label>
                                <div class="glass-input rounded-xl py-2 text-sm font-mono">09:00</div>
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-400 pl-1 uppercase font-bold">Fine</label>
                                <div class="glass-input rounded-xl py-2 text-sm font-mono">18:00</div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-blue-900/40 to-blue-800/20 border border-blue-400/30 rounded-2xl p-3 flex items-center gap-3 mt-2">
                            <div class="w-10 h-10 bg-white/5 rounded-lg p-1 flex-shrink-0">
                                <img id="summary-icon" src="assets/icons/desk_base.png" class="w-full h-full object-contain" onerror="this.style.opacity='0.5'">
                            </div>
                            <div class="overflow-hidden">
                                <p id="summary-title" class="font-bold text-sm truncate">Postazione Base</p>
                                <p id="summary-desc" class="text-[9px] text-blue-200 truncate">Scrivania + Cassettiera</p>
                            </div>
                        </div>

                        <button class="w-full mt-4 bg-gradient-to-r from-[#d97706] to-[#b45309] hover:from-[#f59e0b] hover:to-[#d97706] text-white font-medium py-3 rounded-xl shadow-lg border-t border-white/20 transition-all transform active:scale-95 hover:shadow-orange-500/20">
                            Conferma Prenotazione
                        </button>
                    </div>

                    <div class="mt-auto flex items-baseline justify-end gap-2 text-right pt-4">
                        <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">DISPONIBILI</span>
                        <span class="text-5xl font-bold text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]">12</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        const data = {
            base: { title: "Postazione Base", desc: "Scrivania + Cassettiera + Laptop", icon: "assets/icons/desk_base.png" },
            tech: { title: "Postazione Tech", desc: "Dual Monitor + Dock + Laptop", icon: "assets/icons/desk_tech.png" },
            meeting: { title: "Sala Riunioni", desc: "Proiettore + Whiteboard + 8 Posti", icon: "assets/icons/meeting.png" },
            auto: { title: "Posto Auto", desc: "Garage Interrato - Piano -1", icon: "assets/icons/car.png" }
        };

        function selectType(type, el, isGreen = false) {
            // Reset Sidebar Style
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active-blue', 'active-green');
                // Ripristina gradiente base se necessario
                if(item.querySelector('p').textContent.includes('Posto Auto')) {
                     item.className = "sidebar-item cursor-pointer p-4 rounded-2xl flex items-center gap-4 group bg-gradient-to-r from-emerald-900/20 to-transparent";
                } else {
                     item.className = "sidebar-item cursor-pointer p-4 rounded-2xl flex items-center gap-4 group";
                }
            });

            // Set Active Style
            if (isGreen) {
                el.classList.add('active-green');
                el.classList.remove('bg-gradient-to-r'); 
            } else {
                el.classList.add('active-blue');
            }

            // Update UI Content with Animation
            const info = data[type];
            const titleEl = document.getElementById('dynamic-title');
            
            titleEl.style.opacity = 0;
            
            setTimeout(() => {
                titleEl.textContent = info.title;
                titleEl.style.opacity = 1;
                
                document.getElementById('summary-title').textContent = info.title;
                document.getElementById('summary-desc').textContent = info.desc;
                
                // Update icons with error handling check
                const sumIcon = document.getElementById('summary-icon');
                sumIcon.src = info.icon;
                sumIcon.onerror = function() { this.style.opacity='0.5'; };
                
            }, 150);
        }
    </script>
</body>
</html> 