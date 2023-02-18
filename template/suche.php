<?php 
require_once 'functions.php';

// Datum bestimmen
setlocale(LC_TIME, 'de');
// Unixtime holen
$timestamp = time();
// Datum von Heute
$heute=date('Y-m-d');
// Array um Daten der Suche zu speichern
$form = [];
// Array um Fehler in der Suche zu speichern
$fehler = [];
// Array für Sucheventausgabe
$eventausgabe=[];
// Array für alle Events
$allevents=[];

// Daten von der Globalen in form Array speichern
$form['thema']      = !empty($_GET['thema'])      ? trim($_GET['thema'])       : '';
$form['titel']      = !empty($_GET['titel'])      ? trim($_GET['titel'])       : '';
$form['beschreibung']      = !empty($_GET['beschreibung'])      ? trim($_GET['beschreibung'])       : '';
$form['datumstart']      = !empty($_GET['datumstart'])      ? trim($_GET['datumstart'])       : '';
$form['datumende']      = !empty($_GET['datumende'])      ? trim($_GET['datumende'])       : '';
$form['ort']      = !empty($_GET['ort'])      ? trim($_GET['ort'])       : '';
$form['plzstart']      = !empty($_GET['plzstart'])      ? trim($_GET['plzstart'])       : '';
$form['plzende']      = !empty($_GET['plzende'])      ? trim($_GET['plzende'])       : '';
$form['kostenlos']      = !empty($_GET['kostenlos'])      ? trim($_GET['kostenlos'])       : '';

// Thema aus der Datenbank holen
$db = dbconnect();
$sql = "SELECT DISTINCT(thema) AS thema FROM events ORDER BY thema;";
$result = mysqli_query($db, $sql);
while($datensatz = mysqli_fetch_assoc($result)) {
    $eventthema[] = $datensatz['thema'];
}


// Ort aus der Datenbank holen
$db = dbconnect();
$sql = "SELECT DISTINCT(ort) AS ort FROM events ORDER BY ort;";
$result = mysqli_query($db, $sql);
while($datensatz = mysqli_fetch_assoc($result)) {
    $eventort[] = $datensatz['ort'];
}

// Array für alle Events
$db = dbconnect();
$sql = "SELECT * FROM events ORDER BY datum;";
$result = mysqli_query($db, $sql);
while($datensatz = mysqli_fetch_assoc($result)) {
    $allevents[] = $datensatz;
}
mysqli_close($db);

if(isset($_GET['okbutton'])){

    //Thema überprüfen
    if($_GET['thema']!=0){
        if(!in_array($_GET['thema'],$eventthema)){
            $fehler['thema']='Kein gültiges Thema';
        }
    }
    
    // Titel Überprüfen
    if(strlen($_GET['titel'])>255){
        $fehler['titel'] = 'Titel darf höchstens 255 Zeichen lang sein';
    } 

    // Beschreibung überprüfen
    if(strlen($_GET['beschreibung'])>65000){
        $fehler['beschreibung'] = 'Beschreibung darf höchstens 65000 Zeichen lang sein';
    }
    
    // Start datum überprüfen
    if(strtotime($_GET['datumstart'])===false){
        $fehler['datumstart'] = 'Gültiges Datum eingeben';
    }
    elseif (strtotime($_GET['datumstart']) < strtotime(date("Y-m-d"))){
        $fehler['datumstart'] = 'Das Datum liegt in der Vergangenheit';
    }

    // Ende datum überprüfen
    if(!empty($_GET['datumende'])){
        if(strtotime($_GET['datumende'])===false){
        $fehler['datumende'] = 'Gültiges Datum eingeben';
        }
        elseif (strtotime($_GET['datumende']) < strtotime(date("Y-m-d"))){
            $fehler['datumende'] = 'Das Datum liegt in der Vergangenheit';
        }
        elseif(strtotime($_GET['datumstart'])>strtotime($_GET['datumende'])){
            $fehler['datumende'] = 'Das Datum muss nach dem Startdatum liegen';
        }
    }

    // Ort Überprüfen
    
    if($_GET['ort']!=0){
        if(!in_array($_GET['ort'],$eventort)){
            $fehler['ort']='Kein gültiger Ort';
        }
    }
    
    // PLZ start prüfen
    if(!empty($_GET['plzstart'])){
        if(preg_match("/[^0-9]/", $form['plzstart']) || intval($form['plzstart']) < 100 || intval($form['plzstart']) > 99999) {
            $fehler['plzstart'] = 'Bitte eine gültige deutsche PLZ eingeben';
            }  
    }
    
    // PLZ ende prüfen
    if(!empty($_GET['plzende'])){
        if(preg_match("/[^0-9]/", $form['plzende']) || intval($form['plzende']) < 100 || intval($form['plzende']) > 99999) {
            $fehler['plzende'] = 'Bitte eine gültige deutsche PLZ eingeben';
        }
        elseif(intval($form['plzstart'])>$form['plzende']){
            $fehler['plzende'] = 'Zahl muss größer als Startplz sein';
        }
    }
    
    // Kostenlos überprüfen
    if(!empty($_GET['kostenlos'])&&$_GET['kostenlos']!='kostenlos'){
        $fehler['kostenlos']='Falscher Wert für Checkbox';
    }
    
    if(!count($fehler)) {
        // Mit Datenbank verbinden
        $db = dbconnect();
        
        // Thema an Where SQL-Statement anhängen
        $thema=$form['thema']==''?'':"AND thema='".$form['thema']."'";
        // Titel an Where SQL-Statement anhängen
        $titel=$form['titel']==''?'':"AND titel LIKE '%".$form['titel']."%'";
        // Beschreibung an Where SQL-Statement anhängen
        $beschreibung=$form['beschreibung']==''?'':"AND beschreibung LIKE '%".$form['beschreibung']."%'";
        // Datum an Where SQL-Statement anhängen
        if($form['datumende']==''){
            $datum=$form['datumstart']===''?'':"AND datum>='".$form['datumstart']."'";
        }
        else{
            $datum="AND datum BETWEEN '" .$form['datumstart']. "' AND '" .$form['datumende']."'";
        }
        // Ort an Where SQL-Statement anhängen
        $ort=$form['ort']==''?'':"AND ort='".$form['ort']."'";
        // PLZ an Where SQL-Statement anhängen
        if($form['plzende']==''){
            $plz=$form['plzstart']==''?'':"AND plz>='".$form['plzstart']."'";
        }
        else{
            $plz="AND plz BETWEEN '" .$form['plzstart']. "' AND '" .$form['plzende']."'";
        }
        // Preis an Where SQL-Statement anhängen
        $preis=$form['kostenlos']==''?'':"AND preis=0";
        
        
        // SQL-Statment für Suchergebnis
        $sql =<<<EOT
                SELECT 
                titel
                ,plz
                ,ort
                ,datum
                ,id
                FROM events
                WHERE 1=1 $thema $titel $beschreibung $datum $ort $plz $preis
                ORDER BY datum
EOT;

        $result = mysqli_query($db, $sql);
        
        // Suchergebnis in Array schreiben
        while($datensatz = mysqli_fetch_assoc($result)) {
        $eventausgabe[] = $datensatz;
        }
        //Datenbank schließen
        mysqli_close($db);
    }   
}
if(isset($_GET['reset'])){
    header('location:events.php?');
}
?>


        
        <h2 class="center">Suchen</h2>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
            <div>
                <label for="thema">Thema</label>
                <select id="thema" name="thema">
                    <option value="0">Bitte auswählen</option>
                    <?php foreach($eventthema as $wert): ?>
                    <option value="<?=$wert?>" <?= $form['thema'] == $wert ? 'selected' : '' ?>><?= htmlspecialchars($wert)?></option>
                    <?php endforeach; ?>
                </select>
                <span><?= $fehler['thema'] ?? '' ?></span>
            </div>   
            <div>
                <label for="titel" >Titel</label>
                <input type="text" name="titel" id="titel" value="<?= htmlspecialchars($form['titel']) ?>">
                <span><?= $fehler['titel'] ?? '' ?></span>
            </div>
            <div>
                <label for="beschreibung" >Beschreibung</label>
                <input type="text" name="beschreibung" id="beschreibung" value="<?= htmlspecialchars($form['beschreibung']) ?>">
                <span><?= $fehler['beschreibung'] ?? '' ?></span>
            </div>
            <div>
                <label for="datumstart" >Startdatum</label>
                <input type="date" name="datumstart" id="datumstart" value="<?= $form['datumstart']== ''?$heute:htmlspecialchars($form['datumstart'])?>" >
                <span><?= $fehler['datumstart'] ?? '' ?></span><br>
            </div>
            <div>
                <label for="datumstart" >Enddatum</label>
                <input type="date" name="datumende" id="datumende" value="<?= htmlspecialchars($form['datumende']) ?>" >
                <span><?= $fehler['datumende'] ?? '' ?></span><br>
            </div>
            <div>
                <label for="ort">Ort</label>
                <select id="ort" name="ort">
                    <option value="0">Bitte auswählen</option>
                    <?php foreach($eventort as $wert): ?>
                    <option value="<?=$wert?>" <?= $form['ort'] == $wert ? 'selected' : '' ?>><?= htmlspecialchars($wert)?></option>
                    <?php endforeach; ?>
                </select>
                <span><?= $fehler['ort'] ?? '' ?></span>
            </div>
            <div>
                <label for="plzstart">von PLZ</label>
                <input type="text" id="plzstart" name="plzstart" value="<?= htmlspecialchars($form['plzstart']) ?>">
                <span><?= $fehler['plzstart'] ?? '' ?></span><br>
            </div>
            <div>
                <label for="plzende">bis PLZ</label>
                <input type="text" id="plzende" name="plzende" value="<?= htmlspecialchars($form['plzende']) ?>">
                <span><?= $fehler['plzende'] ?? '' ?></span>
            </div>
            <div>
                <label for="kostenlos"> Kostenlose Events</label>
                <input type="checkbox" id="kostenlos" name="kostenlos" value="kostenlos" <?php if(isset($_GET['kostenlos'])) echo "checked='checked'"?>>
                <span><?= $fehler['kostenlos'] ?? '' ?></span>
            </div>
            
            
            <div>
                <button type="submit" name="okbutton" value="1">Suchen</button>
            
            
                <button type="submit" name="reset" value="reset">Reset</button>
            </div>
        </form>
        
        
