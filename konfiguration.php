<?php
    // die Konstanten auslagern in eigene Datei
    // die dann per require_once ('konfiguration.php');
    // geladen wird.    
    // Damit alle Fehler angezeigt werden
    
    error_reporting(E_ALL);    
    
    
    // Zum Aufbau der Verbindung zur Datenbank
    
    define ( 'MYSQL_HOST',      'project.proco.uni-due.de' ); 
    
    
    // Daten aus config.php
    
    define ( 'MYSQL_BENUTZER',  'limesurvey_dev' );
    define ( 'MYSQL_KENNWORT',  'LxPDN8TCe9tMSwYZ' );
    
    
    // für unser Bsp. nennen wir die DB gewinnspiel
    
    define ( 'MYSQL_DATENBANK', 'gewinnspiel' );
    
    
    $db_link = mysqli_connect (MYSQL_HOST,
        MYSQL_BENUTZER,
        MYSQL_KENNWORT,
        MYSQL_DATENBANK);    
    if ( $db_link )
    {
        echo 'Verbindung erfolgreich: ';
        print_r($db_link);
    }
    else
    {
        // hier könnte dann später dem Programmierer eine
        // E-Mail mit dem Problem zukommen gelassen werden
        
        die('keine Verbindung möglich: ' . mysqli_error());
    }    
    mysqli_set_charset($db_link, 'utf8');
?>
