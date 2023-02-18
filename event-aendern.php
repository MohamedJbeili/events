<?php
require "template/functions.php";

/** @var string[] $form alle Formularfelder */
$form = [];

/** @var string[] $fehler Fehlermeldungen für Formularfelder */
$fehler = [];

// Formularwerte holen
$form['id']           = !empty($_REQUEST['id'])        ? intval($_REQUEST['id'])      : 0;
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
        $fehler['titel'] = 'titelname eingeben';
    }
    elseif(strlen($form['titel']) < 1 || strlen($form['titel']) > 100) {
        $fehler['titel'] = '1-100 Zeichen';
    }

//////Datum (Pflicht)
    if(!$form['datum']) {
        $fehler['datum'] = 'Kein Datum eingegeben';
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
        $fehler['ort'] = 'Bitte 2-32 Zeichen';
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
    elseif(!is_numeric($form['preis']) || intval($form['preis']) < 0 || intval($form['preis']) > 999) {
        $fehler['preis'] = 'Realistischen Preis eingeben!';
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
            UPDATE events 
            SET id           = '{$form['id']}',
                titel        = '{$form['titel']}',
                thema        = '{$form['thema']}',
                beschreibung = '{$form['beschreibung']}',
                datum        = '{$form['datum']}',
                ort          = '{$form['ort']}',
                plz          = '{$form['plz']}',
                preis        = '{$form['preis']}'
            WHERE id = {$form['id']}
EOT;
             
        // 3. SQL-Statement an die DB schicken
        mysqli_query($db, $sql);
        
        // 4. DB schließen
        mysqli_close($db);
        
        // Weiterleiten auf Erfolgsseite und Programm beenden
        header('location: events-aendern_ok.php?id=' . $form['id']);
        die;
    }
}
else {
    /*
     * Formular wurde NICHT abgeschickt (Erstaufruf)
     * Prüfen, ob ID übergeben wurde
     */
    if(0 != $form['id']) {
        // ID des Datensatzes wurde übergeben
        // Datensatz aus DB lesen
        $db = dbConnect();
        
        // SQL-Statement erzeugen
        $sql = 'SELECT * FROM events WHERE id = ' . $form['id'];
       
        // SQL-Statement an die DB schicken
        $result = mysqli_query($db, $sql);
        
        if(mysqli_num_rows($result) == 0) {
            $fehler['id'] = 'Datensatz nicht gefunden';
        }
        else {
            // den Datensatz aus dem Resultset holen
            $form = mysqli_fetch_assoc($result);
        }
        
        // Datenbank schließen
        mysqli_close($db);
        
    }
    else {
        // Fehlermeldung erzeugen
        $fehler['id'] = 'Datensatz-ID fehlt';
    }
    
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events ändern</title>
        <link href="css/adminstyles.css" rel="stylesheet">
    </head>
    <body>
    <div class="wrapper">
<h1>Veranstaltung bearbeiten</h1>
<?php if(!empty($fehler['id'])): ?>
<h3><span><?= $fehler['id'] ?></span></h3>
<h4><a href="events.php">zurück zur Übersicht</a></h4>

<?php else: ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $form['id'] ?>">

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
<?php endif; ?>
<div>
    <h3><a href="admin.php">Übersicht anzeigen</a></h3>
</div>
    </div>
    </body>
</html>
