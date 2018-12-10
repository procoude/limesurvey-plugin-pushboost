<?php
    // Datenbank-Verbindung herstellen
    require_once ('konfiguration.php');

    // zuweisen der MySQL-Anweisung einer Variablen
    $sql = 'CREATE DATABASE gewinnspiel_1';

    $result = mysqli_query($db_link, $sql)
        or die("Anfrage fehlgeschlagen: " . mysql_error());
?>
