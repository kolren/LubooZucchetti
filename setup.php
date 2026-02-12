<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Luboo Zucchetti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* --- STILE FRONT PAGE --- */
        @font-face {
            font-family: 'SF Pro Rounded';
            src: local('SF Pro Rounded'), url('fonts/SF-Pro-Rounded.woff2') format('woff2');
            font-weight: normal;
        }

        body {
            font-family: 'SF Pro Rounded', 'Nunito', -apple-system, sans-serif;
            /* BACKGROUND IDENTICO ALLA FRONT PAGE */
            background: linear-gradient(90deg, #30A9FF 0%, #14364F 60%, #0B0F15 100%);
            min-height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #ffffff;
            padding: 20px;
        }

        /* Stile Tabelle per adattarsi allo sfondo scuro */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background-color: rgba(48, 169, 255, 0.2);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        code {
            background: rgba(255,255,255,0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #A5F3FC;
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            border-radius: 24px;
        }

        .btn-primary {
            background: #30A9FF;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(48, 169, 255, 0.4);
        }
        .btn-primary:hover {
            background: #208bDd;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(48, 169, 255, 0.6);
        }
    </style>
</head>
<body>

    <div class="glass-panel p-8 w-full max-w-5xl flex flex-col items-center">
        <h1 class="text-3xl font-bold mb-6">Setup Database</h1>

        <div class="w-full overflow-x-auto">
        <?php
        // Configurazione Database
        $host = 'localhost';
        $db   = 'luboo_zucchetti5ib';
        $user = 'root'; 
        $pass = '';     
        $charset = 'utf8mb4';

        try {
            // 1. Connessione al server MySQL (senza specificare DB per poterlo creare)
            $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. Leggi file SQL
            $sqlFile = 'tabellauser.sql'; 
            if (!file_exists($sqlFile)) {
                die("<div class='text-red-400 bg-red-900/30 p-4 rounded-lg'>Errore: File $sqlFile non trovato.</div>");
            }
            $sql = file_get_contents($sqlFile);

            // 3. Esegui query multiple
            $pdo->exec($sql);

            // 4. Verifica Utenti Creati
            $pdo->exec("USE $db");
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<div class='text-green-300 bg-green-900/30 p-4 rounded-lg mb-4 text-center border border-green-500/30'>✓ Database aggiornato con successo!</div>";
            echo "<p class='mb-2 text-gray-300'>Ecco gli utenti configurati nel sistema:</p>";
            
            echo "<table>";
            echo "<thead><tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Nome Cognome</th>
                    <th>Età</th>
                    <th>ID Code</th>
                    <th>Ruolo</th>
                  </tr></thead>";
            echo "<tbody>";
            foreach($users as $u) {
                echo "<tr>";
                echo "<td><span class='font-bold text-white'>" . htmlspecialchars($u['username']) . "</span></td>";
                echo "<td>" . htmlspecialchars($u['password']) . "</td>";
                echo "<td>" . htmlspecialchars($u['nome'] . " " . $u['cognome']) . "</td>";
                echo "<td>" . htmlspecialchars($u['eta']) . "</td>";
                echo "<td><code>" . htmlspecialchars($u['codice_identificativo']) . "</code></td>";
                
                // Color coding per ruolo
                $roleColor = match($u['role']) {
                    'admin' => 'text-purple-300',
                    'coordinator' => 'text-yellow-300',
                    default => 'text-blue-300'
                };
                echo "<td class='$roleColor font-semibold'>" . htmlspecialchars($u['role']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";

        } catch (\PDOException $e) {
            echo "<div class='text-red-400 bg-red-900/30 p-4 rounded-lg border border-red-500/30'>Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
        </div>

        <div class="mt-8">
            <a href="front-page.php" class="btn-primary px-8 py-3 rounded-xl text-lg no-underline inline-block">
                Vai al Login &rarr;
            </a>
        </div>
    </div>

</body>
</html>