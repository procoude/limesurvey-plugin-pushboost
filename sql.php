<?php
    // Datenbank-Verbindung herstellen
    require_once ('konfiguration.php');
    
    $sql = "
      INSERT INTO `mails`
      (
      `mail`
      )
      VALUES
      (
      'email@adresse.com'
      );
    ";
    $db_erg = mysqli_query($db_link, $sql)
    or die("Anfrage fehlgeschlagen: " . mysqli_error());
?>
