<?php
/**
 * Bibliothek mit allgemeinen Funktionen
 * 
 */
const BILDPFAD = './bildupload/';
// Konstanten für die DB-Verbindung
const DBSERVER = 'localhost';
const DBUSER   = 'profil-php';
const DBPASSWD = 'profil';
const DBTABLE  = 'eventdb';

/** @const var_dump() als Methode für Variablen-Dumps */
const DUMP_VARDUMP = 'v';

/** @const print_r() als Methode für Variablen-Dumps */
const DUMP_PRINTR  = 'p';

/**
 * Liefert eine Verbindung zur Datenbank
 * 
 * @return mysqli
 */
function dbConnect() {
    // DB-Verbindung aufbauen
    $db = mysqli_connect(DBSERVER, DBUSER, DBPASSWD, DBTABLE);

    // Zeichensatz für die Verbindung explizit festlegen
    mysqli_set_charset($db, 'UTF8');

    // Datenbankverbindung zurückgeben
    return $db;
}


/**
 * Gibt einen Dump der übergebenen Variable in einem präformatierten HTML-Block aus
 *
 * @param  mixed   $varToDump  Variable, deren Dump ausgegeben wird
 * @param  string  $title      Titelzeile für die Ausgabe
 * @param  string  $method     [DUMP_VARDUMP] Dump-Methode
 */
function dump($varToDump, $title = '', $method = DUMP_VARDUMP) 
{
    // Block für präformatierten Text öffnen
    echo '<pre>';
    // Ausgabe des Titels, falls angegeben
    if($title) {
        echo '<strong><u>'.(string) $title.':</u></strong><br>';
    }
    // Dump der Variablen mit angeforderter Funktion
    if(DUMP_PRINTR == $method) {
        print_r($varToDump);
    }
    else {
        var_dump($varToDump);
    }
    echo '</pre>';
}

/**
 * Gibt einen Dump der übergebenen Variable in einem präformatierten HTML-Block aus
 * und beendet dann die Programmausführung
 *
 * @param  mixed   $varToDump  Variable, deren Dump ausgegeben wird
 * @param  string  $title      Titelzeile für die Ausgabe
 * @param  string  $method     [DUMP_VARDUMP] Dump-Methode
 */
function dieDump($varToDump, $title = '', $method = DUMP_VARDUMP) 
{
    // Dump der Variablen
    dump($varToDump, $title, $method);
    // Programmausführung beenden
    die;
}
// Datum bestimmen
setlocale(LC_TIME, 'de');
// Unixtime holen
$timestamp = time();
// Datum von Heute
$heute=date('Y-m-d');

