<?php
    // Datenbank-Verbindung herstellen
    require_once ('konfiguration.php');
    
    // MySQL-Befehl der Variablen $sql zuweisen
    $sql = "
        CREATE TABLE IF NOT EXISTS mails (mail VARCHAR(150) NOT NULL PRIMARY KEY)";
    
    // MySQL-Anweisung ausführen lassen
    $db_erg = mysqli_query($db_link, $sql)
        or die("Anfrage fehlgeschlagen: " . mysqli_error($db_link));
?>
