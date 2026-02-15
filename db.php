<?php
// db.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "luboo_zucchetti5ib"; 

// Creazione connessione
$conn = new mysqli($host, $user, $pass, $dbname);

// Controllo connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>