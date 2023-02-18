<?php
require "template/functions.php";

/** @var string[] $form alle Formularfelder */
$form = [];

/** @var string[] $fehler Fehlermeldungen für Formularfelder */
$fehler = [];

// Formularwerte holen
$form['titel']        = !empty($_GET['titel'])        ? trim($_GET['titel'])        : '';
$form['thema']        = !empty($_GET['thema'])        ? trim($_GET['thema'])        : '';
$form['beschreibung'] = !empty($_GET['beschreibung']) ? trim($_GET['beschreibung']) : '';
$form['datum']        = !empty($_GET['datum'])        ? trim($_GET['datum'])        : '';
$form['ort']          = !empty($_GET['ort'])          ? trim($_GET['ort'])          : '';
$form['plz']          = !empty($_GET['plz'])          ? trim($_GET['plz'])          : '';
$form['preis']        = isset($_GET['preis'])         ? floatval($_GET['preis'])    : 0.00;

//SQL-Code für die SELECT-BOX für die Themen:
$db = dbconnect();
$sql = "SELECT DISTINCT(thema) FROM thema";

$result = mysqli_query($db, $sql);
while($datensatz = mysqli_fetch_assoc($result)) {
    $themennamen[] = $datensatz;
}
mysqli_close($db);
$themamaximum = count($themennamen);

/*
 * Prüfen, ob Formular abgeschickt wurde
 * Falls ja, dann weitere Prüfungen durchführen
 */
if(isset($_GET['okbutton'])) {

/////////////////////////////----PRÜFUNGEN----//////////////////////////////////

 
/////Titel (Pflicht)
    if(!$form['titel']) {
        $fehler['titel'] = 'Titelname eingeben!';
    }
    elseif(strlen($form['titel']) < 1 || strlen($form['titel']) > 100) {
        $fehler['titel'] = '1-100 Zeichen';
    }

//////Datum (Pflicht)
    if(!$form['datum']) {
        $fehler['datum'] = 'Datum eingegeben!';
    }
    else {
        // Datum extrahieren
        $jahr  = substr($form['datum'], 0, 4);
        $monat = substr($form['datum'], 5, 2);
        $tag   = substr($form['datum'], 8, 2);
        // Datum auf allgemeine Gültigkeit prüfen
        if(!checkdate($monat, $tag, $jahr)) {
            $fehler['datum'] = 'Bitte gültiges Datum eingeben';
        }
        // Prüfen, ob kein früheres Datum gewählt wurde (ab heute wäre möglich)
        else {
            $heute = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
            $veranstaltungsdatum = mktime(0, 0, 0, $monat, $tag, $jahr);
            if($veranstaltungsdatum < $heute) {
                $fehler['datum'] = 'Bitte ein zukünftiges Datum auswählen';
            }
        }
    } 
//////PLZ
    if(!$form['plz']) {
        $fehler['plz'] = 'PLZ eingeben!';
    }
    elseif(!is_numeric($form['plz']) || intval($form['plz']) < 100 || intval($form['plz']) > 99999) {
        $fehler['plz'] = 'Gültige PLZ eingeben!';
    }

//////Ort (Pflicht)
    if(!$form['ort']) {
        $fehler['ort'] = 'Ort eingeben!';
    }
    elseif(!preg_match("/^[a-zA-ZäöüÄÖÜ]+[a-zA-Z äöüÄÖÜ-]*[a-zA-ZäöüÄÖÜ]+$/", $form['ort']) || strlen($form['ort']) < 2) {
        $fehler['ort'] = '2-32 Zeichen, keine Zahlen, Keine Bindestriche am anfang und ende';
    }
    elseif(!preg_match("/^[a-zA-ZäöüÄÖÜ]+[a-zA-Z äöüÄÖÜ-]*[a-zA-ZäöüÄÖÜ]+$/", $form['ort']) || strlen($form['ort']) > 32) {
        $fehler['ort'] = '2-32 Zeichen, keine Zahlen, Keine Bindestriche am anfang und ende';
    }
/////////Preis
    if($form['preis'] < 0) {
        $fehler['preis'] = 'Preis eingeben!';
    }
    
////////////////////////////////////////////////////////////////////////////////    
    
    // Wenn keine Fehler aufgetreten sind ...
    if(!count($fehler)) 
	{
        // 1. Datenbankverbindung aufbauen
        $db = dbConnect();
        
        // Daten escapen (zur Vorbeugung gegen SQL-Injection)
        foreach($form as $key => $value) {
            $form[$key] = mysqli_real_escape_string($db, $value);
        }
        
        // 2. SLQ-Statement erzeugen
        // HEREDOC-Schreibweise
        // Hinter der Anfangsmarke darf nichts stehen (auch kein Leerzeichen)
        $sql = <<<EOT
            INSERT INTO events (titel, thema, beschreibung, datum, ort, plz, preis)
            VALUES ('{$form['titel']}',
                    '{$form['thema']}',
                    '{$form['beschreibung']}',
                    '{$form['datum']}',
                    '{$form['ort']}',
                    '{$form['plz']}',
                    '{$form['preis']}')
EOT;
        // Die Ende-Marke muss in einer eigenen Zeile GANZ AM ANFANG stehen. Es darf auch nichts dahinter kommen
             
        // 3. SQL-Statement an die DB schicken
        mysqli_query($db, $sql);
        
        // 4. DB schließen
        mysqli_close($db);
        
        // Seite neu und leer aufrufen um weitere Datensätze anzulugen
        header('location: admin.php?sort=id&richtung=DESC&suche=');
        die;
    }
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Event hinzufügen</title>
        <link href="css/adminstyles.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <h1>Veranstaltung hinzufügen</h1>           
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get" enctype="multipart/form-data">
    
    <div>
        <label for="thema" class="pflicht">Thema</label>
        <select name="thema" id="thema">
            <?php foreach($themennamen as $wert): ?>
            <option value="<?= $wert['thema'] ?>" <?= $form['thema'] == $wert['thema'] ? 'selected' : '' ?>> <?= $wert['thema'] ?></option>
            <?php endforeach; ?>
        </select>
        <span><?= $fehler['thema'] ?? '' ?></span>
    </div>

    <div>
        <label for="titel" class="pflicht">Titel</label>
        <input type="text" name="titel" id="titel" value="<?= htmlspecialchars($form['titel']) ?>">
        <span><?= $fehler['titel'] ?? '' ?></span>
    </div>
    
    <div>
        <label for="beschreibung" class="pflicht">Beschreibung</label>
        <textarea name="beschreibung" id="beschreibung" placeholder=""><?= htmlspecialchars($form['beschreibung']) ?></textarea>
        <span><?= $fehler['beschreibung'] ?? '' ?></span>
    </div>
    
    <div>
        <label for="datum" class="pflicht">Datum</label>
        <input type="date" name="datum" id="datum" value="<?= htmlspecialchars($form['datum']) ?>">
        <span><?= $fehler['datum'] ?? '' ?></span>
    </div>

    <div>
        <label for="ort" class="pflicht">Ort</label>
        <input type="text" name="ort" id="ort" value="<?= htmlspecialchars($form['ort']) ?>">
        <span><?= $fehler['ort'] ?? '' ?></span>
    </div>

    <div>
        <label for="plz" class="pflicht">PLZ</label>
        <input type="text" name="plz" id="plz" value="<?= htmlspecialchars($form['plz']) ?>">
        <span><?= $fehler['plz'] ?? '' ?></span>
    </div>
    
    <div>
        <label for="preis" class="pflicht">Preis</label>
        <input type="text" name="preis" id="preis" value="<?= htmlspecialchars($form['preis']) ?>">
        <span><?= $fehler['preis'] ?? '' ?></span>
    </div>

    <div>
        <button class="speicherbutton" type="submit" name="okbutton" value="1">speichern</button>
    </div>
</form>
        <div>
            <h3><a href="admin.php?sort=id&richtung=DESC&suche=">Übersicht anzeigen</a></h3>
        </div>
    </div>
    </body>
</html>
