<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { exit(); }

$mio_id = $_SESSION['user_id'];

// RICERCA NEI MESSAGGI E CONTATTI
if (isset($_GET['search'])) {
    header('Content-Type: application/json');
    $query = trim($_GET['search']);
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit();
    }
    
    $search_term = '%' . $conn->real_escape_string($query) . '%';
    
    $stmt_search = $conn->prepare("
        SELECT DISTINCT u.id, u.nome, u.cognome, u.role, COUNT(m.id) as match_count
        FROM users u
        LEFT JOIN messaggi m ON (
            (m.mittente_id = u.id AND m.destinatario_id = ?) 
            OR (m.mittente_id = ? AND m.destinatario_id = u.id)
        )
        WHERE u.id != ? AND (
            CONCAT(u.nome, ' ', u.cognome) LIKE ?
            OR m.testo LIKE ?
        )
        GROUP BY u.id
        ORDER BY match_count DESC, CONCAT(u.nome, ' ', u.cognome) ASC
    ");
    $stmt_search->bind_param("iiss", $mio_id, $mio_id, $mio_id, $search_term, $search_term);
    $stmt_search->execute();
    $result = $stmt_search->get_result();
    
    $risultati = [];
    while ($row = $result->fetch_assoc()) {
        $risultati[] = $row;
    }
    echo json_encode($risultati);
    exit();
}

// 1. INSERIMENTO NUOVO MESSAGGIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testo']) && isset($_POST['destinatario_id'])) {
    $destinatario_id = intval($_POST['destinatario_id']);
    $testo = trim($_POST['testo']);
    
    if ($destinatario_id > 0 && !empty($testo)) {
        $stmt_insert = $conn->prepare("INSERT INTO messaggi (mittente_id, destinatario_id, testo, data_invio) VALUES (?, ?, ?, NOW())");
        $stmt_insert->bind_param("iis", $mio_id, $destinatario_id, $testo);
        $stmt_insert->execute();
    }
    exit();
}

// 2. RECUPERO E RAGGRUPPAMENTO MESSAGGI PER DATA
if (isset($_GET['partner_id'])) {
    $partner_id = intval($_GET['partner_id']);
    
    $stmt_get = $conn->prepare("
        SELECT id, mittente_id, testo, data_invio 
        FROM messaggi 
        WHERE (mittente_id = ? AND destinatario_id = ?) 
           OR (mittente_id = ? AND destinatario_id = ?) 
        ORDER BY data_invio ASC
    ");
    $stmt_get->bind_param("iiii", $mio_id, $partner_id, $partner_id, $mio_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    $last_date = '';
    $mesi = [1=>'Gennaio', 2=>'Febbraio', 3=>'Marzo', 4=>'Aprile', 5=>'Maggio', 6=>'Giugno', 7=>'Luglio', 8=>'Agosto', 9=>'Settembre', 10=>'Ottobre', 11=>'Novembre', 12=>'Dicembre'];

    if ($result->num_rows === 0) {
        echo '<div class="flex-grow flex items-center justify-center text-[#BFD6E8]/40 text-sm italic font-medium">Invia un messaggio per iniziare la conversazione.</div>';
        exit();
    }

    while ($row = $result->fetch_assoc()) {
        $timestamp = strtotime($row['data_invio']);
        $msg_date = date('Y-m-d', $timestamp);
        
        // ---- LOGICA RAGGRUPPAMENTO DATA ----
        if ($msg_date !== $last_date) {
            $oggi = date('Y-m-d');
            $ieri = date('Y-m-d', strtotime('-1 day'));
            
            if ($msg_date === $oggi) {
                $label_data = 'Oggi';
            } elseif ($msg_date === $ieri) {
                $label_data = 'Ieri';
            } else {
                $label_data = date('d', $timestamp) . ' ' . $mesi[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
            }
            
            // Stampa l'etichetta divisoria della data
            echo '<div class="flex justify-center my-5 sticky top-0 z-10 relative">';
            echo '  <div class="absolute inset-x-0 top-1/2 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>';
            echo '  <span class="relative bg-[#04101A]/95 border border-white/20 text-[#36A482] text-[11px] font-black uppercase tracking-[2px] px-5 py-2 rounded-full shadow-lg backdrop-blur-md hover:bg-[#0A2338] transition-colors">';
            echo '    ' . $label_data;
            echo '  </span>';
            echo '</div>';
            
            $last_date = $msg_date;
        }
        // -------------------------------------

        $is_mio = ($row['mittente_id'] == $mio_id);
        $time_format = date('H:i', $timestamp);
        $testo_pulito = htmlspecialchars($row['testo'], ENT_QUOTES, 'UTF-8');
        
        // Converte i ritorni a capo in <br> in modo pulito
        $testo_pulito = nl2br($testo_pulito);

        if ($is_mio) {
            // Bolla Messaggio Utente Loggato (Verde Acqua, Destra)
            echo '
            <div class="flex justify-end mb-2 relative group">
                <div class="bg-[linear-gradient(135deg,#1D7F75_0%,#36A482_100%)] text-white max-w-[75%] rounded-[20px] rounded-tr-[4px] px-4 py-2.5 shadow-md">
                    <p class="text-[15px] leading-relaxed break-words font-medium">' . $testo_pulito . '</p>
                    <div class="flex justify-end items-center gap-1.5 mt-1">
                        <span class="text-[10px] text-white/70 font-semibold">' . $time_format . '</span>
                        <svg class="w-3 h-3 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </div>
            </div>';
        } else {
            // Bolla Messaggio Collega (Scura, Sinistra)
            echo '
            <div class="flex justify-start mb-2 relative group">
                <div class="bg-[#0A2338] border border-white/5 text-[#F1F6FF] max-w-[75%] rounded-[20px] rounded-tl-[4px] px-4 py-2.5 shadow-md">
                    <p class="text-[15px] leading-relaxed break-words font-medium">' . $testo_pulito . '</p>
                    <div class="flex justify-start mt-1">
                        <span class="text-[10px] text-[#BFD6E8]/60 font-semibold">' . $time_format . '</span>
                    </div>
                </div>
            </div>';
        }
    }
}
?>