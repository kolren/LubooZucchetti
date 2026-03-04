
---

# 📘 Manuale Tecnico Definitivo: Luboo Zucchetti BookSystem

## 1. Introduzione e Architettura di Sistema

Il progetto "Luboo Zucchetti" è un ecosistema gestionale completo per la prenotazione di asset aziendali (scrivanie, sale riunioni, parcheggi).

L'architettura segue il classico pattern client-server:

- **Backend**: PHP puro (Vanilla PHP) senza framework pesanti, garantendo esecuzione rapida.
    
- **Database**: MySQL/MariaDB.
    
- **Frontend**: HTML5, JavaScript Vanilla (per manipolazioni DOM e AJAX) e **Tailwind CSS** per un design system moderno, fluido e responsive.
    
- **Design Pattern**: Role-Based Access Control (RBAC) per la gestione gerarchica degli utenti.
    

---

## 2. Struttura del Database Relazionale (`db.sql`)

Il cuore del salvataggio dati è il file `db.sql`. L'architettura è divisa in quattro tabelle principali, strettamente legate da vincoli di integrità referenziale (Foreign Keys). Durante il setup iniziale, il sistema disabilita temporaneamente i controlli (`SET FOREIGN_KEY_CHECKS=0;`) per permettere una rigenerazione pulita delle tabelle.

1. **`team`**: Tabella di anagrafica pura. Contiene un `id` e un `nome_team`. Serve per raggruppare i dipendenti in dipartimenti (es. Sviluppo, Design).
    
2. **`users`**: Tabella complessa per le identità.
    
    - Implementa un campo `role` in formato `ENUM('amministratore', 'coordinatore', 'dipendente')`.
        
    - Usa un `codice_identificativo` univoco a 6 caratteri.
        
    - La Foreign Key `team_id` collega l'utente al suo team con logica `ON DELETE SET NULL ON UPDATE CASCADE`.
        
3. **`asset`**: Il catalogo degli spazi prenotabili.
    
    - Il campo `tipo` è cruciale per le logiche aziendali: `ENUM('base', 'tech', 'meeting', 'parking')`.
        
    - Dispone di un `codice_univoco` e di parametri come `piano` e `armadietto` associato.
        
4. **`prenotazioni`**: La tabella transazionale di bridging tra `users` e `asset`.
    
    - Ospita i campi `data_prenotazione`, `ora_inizio`, e `ora_fine`.
        
    - Per mantenere lo storico senza corrompere le statistiche, non viene mai usato un DELETE fisico per le cancellazioni normali, ma uno "Soft Delete" tramutato nel campo `stato` (`ENUM('attiva', 'annullata', 'conclusa')`).
        

---

## 3. Connessione al Database (`db.php`)

Il file `db.php` è il punto d'ingresso per ogni interazione con il server MySQL.

Il modulo utilizza l'estensione `mysqli` e viene incluso tramite `require_once` all'inizio di quasi tutti i file del backend.

Se la connessione fallisce, lo script viene interrotto bruscamente per prevenire l'esecuzione di query malformate o il rendering parziale della pagina.

---

## 4. Sistema Anti-Bot Custom: Captcha a Scorrimento

A differenza di implementazioni esterne come Google reCAPTCHA, il BookSystem si dota di un captcha "fatto in casa" altamente estetico e sicuro, diviso in tre fasi:

1. **Generazione (`captcha.php`)**: Lo script inizializza una sessione PHP e calcola randomicamente le coordinate (Asse X) in cui un utente deve incastrare un tassello. Questa informazione viene salvata rigorosamente in sessione (`$_SESSION['captcha_target_x']`) e mai inviata in chiaro al client.
    
2. **Interfaccia (`front-page.php`)**: L'utente utilizza uno slider in JavaScript. Al rilascio (evento `mouseup` o `touchend`), i pixel calcolati vengono inviati tramite Fetch API (AJAX) al backend in formato JSON.
    
3. **Validazione (`verifica.php`)**: Il backend decodifica il JSON, estrae il valore e lo confronta con il target salvato in sessione. Se il margine di errore è accettabile, il sistema setta `$_SESSION['captcha_verified'] = true` e risponde con status 200 al frontend.
    

---

## 5. Autenticazione e Sicurezza (`loginhandle.php`)

Il file che processa il form di login è `loginhandle.php`. È uno script estremamente delicato ed è protetto a più livelli:

### Controllo Captcha

PHP

```
if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
    header("Location: front-page.php?error=" . urlencode("Verifica Captcha fallita!"));
    exit;
}
```

Questa è la barriera principale contro script automatizzati. Se si prova a forzare una chiamata POST bypassando il frontend, il blocco di codice espelle l'utente immediatamente.

### Prevenzione SQL Injection

Le credenziali (`username` e `password`) non vengono mai concatenate direttamente nella query. Si fa uso massiccio dei **Prepared Statements**:

PHP

```
$stmt = $conn->prepare("SELECT id, nome, cognome, role, codice_identificativo FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
```

In caso di successo (`num_rows === 1`), vengono create le chiavi primarie della sessione utente (`user_id`, `user_nome`, `user_ruolo`) che lo accompagneranno per tutta la navigazione.

---

## 6. Frontend: Tailwind CSS e Glassmorphism (`style.css` & `front-page.php`)

Il sistema si distacca dai look standard "piatti" introducendo il **Glassmorphism**, una tecnica UI che simula il vetro satinato sopra sfondi vibranti.

### Tipografia e Font

Il file `style.css` e i blocchi `<style>` puntano a `font-family: 'SF Pro Rounded'` con un fallback in `Nunito`. Questo garantisce curve morbide e un impatto visivo affine agli standard Apple.

### Composizione del Vetro

La classe `.glass-card` che domina il login utilizza questa tecnica CSS:

CSS

```
.glass-card {
    background: rgba(43, 95, 173, 0.25);
    backdrop-filter: blur(30px) saturate(120%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 40px 80px -10px rgba(0,0,0,0.6);
}
```

L'aggiunta del filtro `saturate(120%)` unito al blur rende il gradiente blu profondo (`.bg-main`) retrostante molto più vivido attraverso il componente.

---

## 7. Motore RBAC (Role-Based Access Control)

Nel BookSystem, il ruolo dell'utente non è solo una label visiva, ma il motore decisionale del backend. Ad ogni caricamento di pagina, le variabili di sessione vengono interrogate (`$ruoloUtente = strtolower(trim(isset($_SESSION['user_ruolo']) ? $_SESSION['user_ruolo'] : 'dipendente'));`).

Questi tre ruoli (`dipendente`, `coordinatore`, `amministratore`) alterano:

1. **Accesso alle pagine**: Redirect forzati.
    
2. **Visibilità dati**: Query SQL dinamicamente riscritte.
    
3. **Azioni consentite**: Interazione coi form bloccata/sbloccata in base ai permessi.
    

---

## 8. Dashboard e Calcolo Statistico (`dashboard.php`)

La `dashboard.php` fornisce una panoramica istantanea ed esegue query complesse in real-time. Esclude sempre le prenotazioni `stato = 'annullata'`.

### Analisi Widget

1. **Metriche Mensili**: Sfrutta funzioni MySQL native sulle date: `MONTH(data_prenotazione) = MONTH(CURRENT_DATE())` per isolare il mese in corso.
    
2. **Prenotazioni Attive "In Arrivo"**: Per calcolare se una prenotazione è ancora valida, la data e l'ora vengono unite e confrontate con l'orario server: `CONCAT(data_prenotazione, ' ', ora_fine) > NOW()`.
    
3. **Risorsa Favorita**: Esegue un join fra prenotazioni e asset, ragruppa per ID asset, ordina per conteggio discendente e frena alla prima riga (`GROUP BY a.id ORDER BY c DESC LIMIT 1`).
    

La dashboard include anche la renderizzazione di file SVG diretti tramite helper in PHP (`getAssetIconDashboard`) per non sprecare richieste HTTP e colorare le icone inline.

---

## 9. Visibilità Personale e Gerarchie (`dipendenti.php`)

Il file `dipendenti.php` illustra alla perfezione il potere del sistema a ruoli.

- **Se sei Dipendente**: Tentare di visualizzare questa route farà scattare un `header("Location: dashboard.php");`.
    
- **Se sei Coordinatore**: Viene forzata una clausola `WHERE u.role = 'dipendente' AND u.team_id = ?` agganciata al `team_id` estrapolato dalla sessione. Il leader vede solo il suo gregge.
    
- **Se sei Amministratore**: Hai accesso illimitato. Puoi usare un toggle `?view=coordinatori` per istruire il backend a cambiare l'intera query SQL di fetch e osservare i leader.
    

Nella colonna destra di questa pagina, un algoritmo SQL complessa esegue un `LEFT JOIN` con `DATE_SUB(CURDATE(), INTERVAL 7 DAY)` per stilare una classifica degli impiegati più attivi dell'ultima settimana.

---

## 10. Modifica, Profilazione e Cancellazione (`gestisci.php`)

La view `gestisci.php` funge da pannello di controllo per l'identità dell'utente (propria o altrui).

Riceve un parametro GET `?id=X`. Se un dipendente prova a ispezionare l'id di un collega, viene rimbalzato al proprio profilo.

### Sovrascrittura Appuntamenti

La logica più spinosa qui è la modifica di un orario. Se un utente cambia la fine di un meeting, il backend:

1. Isola l'`asset_id` originario della sua prenotazione.
    
2. Esegue un algoritmo di overlap temporale identico a quello della mappa.
    
3. Aggiunge alla query `WHERE id != ?` per direzionare il DB di _ignorare la vecchia prenotazione che l'utente sta tentando di modificare_, altrimenti segnalerebbe un falso positivo di "risorsa occupata" da sé stesso.
    

---

## 11. Core Engine: Regole di Business (`salva_prenotazione.php` e logiche aziendali)

Lo snodo cruciale di tutto il sistema è `salva_prenotazione.php`. Non fa solo inserimenti nel database, ma si accolla il carico di far rispettare minuziosamente le policy aziendali documentate.

### Validazioni Primarie

Prima di tutto, filtra errori stupidi o tentativi di manipolazione:

- Controllo temporale: `strtotime($inizio) >= strtotime($fine)` o prenotazioni nel passato (`< strtotime(date('Y-m-d'))`) innescano immediatamente redirect con errore.
    

### I Vincoli dei Dipendenti

Il sistema implementa le specifiche logiche:

1. **Niente Sale Riunioni**: Se il campo `tipo` recuperato dal DB equivale a `meeting` e sei un dipendente base, l'azione viene interrotta.
    
2. **Limite 1 Scrivania (Mutex)**: Il dipendente non può collezionare postazioni. Il backend esegue una `SELECT COUNT(*)` contando tutte le prenotazioni odierne dello stesso utente per `tipo IN ('base', 'tech')`. Se il conteggio `>= 1`, viene bloccato. Significa che Tech esclude Base e viceversa.
    
3. **Limite Parcheggio**: Stessa logica di conteggio per il parcheggio (massimo 1).
    

Coordinatori e Amministratori saltano del tutto questa cascata di controlli con un semplice check `if (!in_array($ruolo, ['amministratore', 'coordinatore']))`.

---

## 12. L'Algoritmo delle Sovrapposizioni (`prenotazione.php`)

Per evitare "doppioni" nella stessa stanza alla stessa ora, il sistema non aspetta il salvataggio per avvisare, ma inibisce la mappa a priori.

Quando accedi a `prenotazione.php?data=2024-10-10&inizio=10:00&fine=12:00`:

Il server immagazzina tutto in un array `$occupati` sfruttando la regola aurea dell'intersezione temporale:

PHP

```
$query_occ = "SELECT a.codice_univoco FROM prenotazioni p 
              JOIN asset a ON p.asset_id = a.id
              WHERE p.data_prenotazione = '$d' AND p.stato != 'annullata' 
              AND ((p.ora_inizio < '$f' AND p.ora_fine > '$i'))";
```

- Spiegazione matematica: Due segmenti temporali (Esistente e Richiesta) si toccano se `Inizio_Esistente < Fine_Richiesta` **E INSIEME** `Fine_Esistente > Inizio_Richiesta`. Solo così si catturano inclusioni, strascichi parziali ed esuberi.
    

---

## 13. Motore di Rendering Mappa (UI/UX)

Il file `prenotazione.php` presenta una piantina scalabile integrata con SVG. Questo garantisce che non perda mai risoluzione e che ogni scrivania sia un vero "nodo DOM".

### CSS Binding Dinamico

In PHP, l'array `$occupati` popolato al passo precedente viene usato nel codice HTML per generare le classi delle scrivanie.

Tramite una sintassi shorthand `in_array($codice, $occupati) ? 'occupato' : 'libero'`, il sistema appiccica le classi.

- `.libero`: Attiva il colore base (solitamente Verde/Teal), la classe pointer-events, ed effetti hover luminosi (brightness) usando i CSS Filter.
    
- `.occupato`: Cambia il riempimento (`fill` in SVG) verso il rosso scuro, blocca gli onclick di JS impedendo di aprire il modale per la selezione.
    

### Navbar Roles

La navbar superiore della mappa comunica passivamente i diritti d'azione all'utente. Utilizza un array PHP `$themeColors` che cambia lo sfondo dei badge (Admin Teal, Coordinatore Blu, Dipendente Verde) alterando classi Tailwind al volo in base alla chiave `$ruoloUtente`.

---

## 14. Modulo di Setup Applicativo (`setup.php`)

Il deploy di questo sistema non richiede di usare tool come phpMyAdmin per l'impostazione. Il pacchetto include `setup.php`.

Questa pagina serve come "Installatore". Interroga il file SQL, analizza le tabelle e procede ad inserire i dati mock/iniziali (come gli impiegati di default e le planimetrie degli uffici) per preparare l'ambiente al primo avvio aziendale. Utilizza lo stesso design scuro per integrarsi organicamente al feeling dell'intero applicativo.

---

# 📖 Glossario delle Funzioni PHP: Luboo Zucchetti

Questo documento è una guida di riferimento per comprendere le funzioni e i costrutti nativi di PHP utilizzati all'interno del progetto. È diviso per categorie per facilitarne la consultazione.

## 1. Gestione Variabili e Controlli

### `isset()`

- **Cosa fa:** Verifica se una variabile è stata dichiarata e se il suo valore è diverso da `null`.
    
- **Uso nel progetto:** È usata ovunque per controllare se un utente ha inviato un modulo o se esiste una variabile di sessione (es. nel captcha o nel login: `if (isset($_SESSION['user_id']))`).
    

### `empty()`

- **Cosa fa:** Controlla se una variabile è vuota. Una variabile è considerata vuota se non esiste, o se il suo valore è `false`, `0`, `""` (stringa vuota) o `null`.
    
- **Uso nel progetto:** Utilizzata per validare i campi dei form prima di processarli, assicurandosi che l'utente non abbia inviato campi vuoti (es. `if (empty($_POST['username']))`).
    

---

## 2. Superglobali (Array di Sistema)

Le superglobali sono variabili predefinite di PHP sempre accessibili in qualsiasi punto del codice.

### `$_POST`

- **Cosa fa:** È un array associativo che raccoglie i dati inviati al server tramite un form HTML con metodo `POST` (invisibili nell'URL).
    
- **Uso nel progetto:** Raccoglie credenziali in `loginhandle.php` (es. `$_POST['username']`) e i dati delle prenotazioni.
    

### `$_GET`

- **Cosa fa:** Raccoglie i dati inviati al server tramite parametri nell'URL (es. `pagina.php?id=5&view=coordinatori`).
    
- **Uso nel progetto:** Passa informazioni di stato tra le pagine, come gli errori (`?error=...`) o le viste della tabella dipendenti (`$_GET['view']`).
    

### `$_SESSION`

- **Cosa fa:** Array utilizzato per memorizzare informazioni specifiche dell'utente tra diverse pagine web, finché il browser non viene chiuso o la sessione distrutta.
    
- **Uso nel progetto:** Mantiene l'utente loggato (`$_SESSION['user_id']`), definisce i suoi privilegi (`$_SESSION['user_ruolo']`) e gestisce la sicurezza del captcha (`$_SESSION['captcha_verified']`).
    

---

## 3. Gestione Rete, Sessioni e Flusso

### `session_start()`

- **Cosa fa:** Inizializza una nuova sessione o ne riprende una esistente basata su un identificatore di sessione passato tramite cookie.
    
- **Uso nel progetto:** È la primissima riga di codice in quasi tutte le pagine (es. `dashboard.php`, `loginhandle.php`) per poter accedere all'array `$_SESSION`.
    

### `header()`

- **Cosa fa:** Invia un'intestazione HTTP grezza al browser. Viene usato prevalentemente per eseguire dei reindirizzamenti (redirect).
    
- **Uso nel progetto:** Usato per cacciare gli utenti non autorizzati o reindirizzarli dopo un'azione (es. `header("Location: dashboard.php");`).
    

### `exit` o `die()`

- **Cosa fa:** Termina immediatamente l'esecuzione dello script PHP corrente.
    
- **Uso nel progetto:** Fondamentale dopo ogni `header()`. Se non mettessi `exit`, il server continuerebbe a elaborare il resto del codice della pagina ignorando il redirect temporaneamente, causando problemi di sicurezza o bug.
    

### `urlencode()`

- **Cosa fa:** Codifica una stringa per poterla passare in sicurezza all'interno di un URL (sostituisce gli spazi con `+` o `%20` e codifica caratteri speciali).
    
- **Uso nel progetto:** Usato per passare messaggi di errore complessi nelle URL (es. `header("Location: pagina.php?error=" . urlencode("Messaggio di errore!"));`).
    

---

## 4. Manipolazione Stringhe e Array

### `in_array()`

- **Cosa fa:** Cerca un valore specifico all'interno di un array.
    
- **Uso nel progetto:** Usato nella mappa (`prenotazione.php`) per controllare se la postazione corrente che il ciclo sta stampando è contenuta nell'array `$occupati`. Se sì, la colora di rosso.
    

### `strtolower()` e `trim()`

- **Cosa fa:**
    
    - `strtolower()` converte tutti i caratteri di una stringa in minuscolo.
        
    - `trim()` rimuove gli spazi vuoti o altri caratteri invisibili all'inizio e alla fine di una stringa.
        
- **Uso nel progetto:** Standardizza i dati (come il `ruolo` in sessione) prima di fare dei controlli IF, evitando bug dovuti a spazi accidentali o lettere maiuscole impreviste.
    

---

## 5. Interazione col Database (`mysqli`)

Queste non sono funzioni generiche, ma metodi dell'oggetto `$conn` (che rappresenta la connessione MySQL).

### `$conn->prepare()` e `bind_param()`

- **Cosa fanno:** Preparano una query SQL in cui i valori sono sostituiti da punti interrogativi (`?`). Successivamente, `bind_param` "aggancia" le vere variabili a quei punti interrogativi, indicandone il tipo (es. "s" per stringa, "i" per integer).
    
- **Uso nel progetto:** Prevengono le **SQL Injections**. Invece di incollare il testo inserito dall'utente direttamente nel database, il testo viene inviato separatamente come "parametro inoffensivo" (es. in `loginhandle.php`).
    

### `$conn->query()`

- **Cosa fa:** Esegue una query SQL diretta (senza prepared statements).
    
- **Uso nel progetto:** Utilizzato nelle statistiche della `dashboard.php` dove le query non richiedono input dell'utente e sono quindi sicure.
    

### `fetch_assoc()`

- **Cosa fa:** Estrae una singola riga dal risultato di una query e la restituisce come un array associativo (dove i nomi delle colonne diventano le chiavi).
    
- **Uso nel progetto:** Usato all'interno dei cicli `while ($row = $result->fetch_assoc())` per stampare a schermo le liste di dipendenti o i dati delle tabelle.
    

---

## 6. Gestione di Date e Tempo

### `strtotime()`

- **Cosa fa:** Converte una data o un'ora scritta in formato stringa inglese o ISO (es. "2024-10-15" o "14:30") in un "Timestamp Unix" (il numero di secondi trascorsi dal 1 Gennaio 1970).
    
- **Uso nel progetto:** Molto usato in `salva_prenotazione.php` per fare confronti matematici (es. verificare se `strtotime($orario_inizio) < strtotime($orario_fine)` per evitare prenotazioni negative).
    

### `date()`

- **Cosa fa:** Formatta un timestamp locale o attuale secondo il formato specificato (es. `date('Y-m-d')`).
    
- **Uso nel progetto:** Usato per ottenere la data di oggi e paragonarla con le date di prenotazione, evitando che un utente prenoti risorse nel passato.
    

---

## 7. Decodifica JSON e I/O di Rete

### `json_decode()` e `file_get_contents()`

- **Cosa fanno:**
    
    - `file_get_contents('php://input')` legge l'intero contenuto della richiesta inviata dal client (spesso usata per richieste AJAX/Fetch API).
        
    - `json_decode()` trasforma una stringa di testo in formato JSON in un Oggetto o Array PHP.
        
- **Uso nel progetto:** Questi due comandi lavorano insieme in `verifica.php` per ricevere e interpretare le coordinate del captcha a scorrimento che il Javascript del frontend ha inviato via AJAX.