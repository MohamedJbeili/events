<?php
require_once './template/functions.php';
//Array für alle Events

$events = [];







//Verbindung mit Daten Bank 
try {
    $db = dbconnect();
    mysqli_set_charset($db, 'UTF8');
}
catch(mysqli_sql_exception $ex){
    echo 'Verbindungsfehler: ' . $ex->getMessage();
    die;

}

// Paginator erstellen
const PROSEITE = 8;
$seite = !empty($_GET["seite"]) ? intval($_GET["seite"]) : 1;
//mysql Statement
$sql1 = "SELECT COUNT(*) anzahl FROM events WHERE datum >= '$heute'";
$result1 = mysqli_query($db, $sql1);
$treffer1 = mysqli_fetch_assoc($result1);

$anzahl = $treffer1["anzahl"];
$seiten = max(1,ceil($anzahl / PROSEITE));
if($seite <= 0){
    $seite = 1 ;
}
elseif($seite > $seiten){
    $seite = $seiten;
}

$offset = PROSEITE * ($seite - 1);
$limit = "LIMIT $offset, ". PROSEITE;



//mysql Statement
$sql = <<<EOT
SELECT titel
,plz
,ort
,datum
,id FROM events
WHERE datum >= "$heute"
ORDER BY datum
$limit

EOT;

//Daten an Daten Bank schicken und speichern
$result = mysqli_query($db, $sql);

//DateSätzen holen
while($datenSatz = mysqli_fetch_assoc($result)){
$events[] = $datenSatz;
}

   
//   echo"<pre>";
//    echo  $id;
//      echo "</pre>";
//    die;

//
mysqli_close($db);
include "template/header.phtml";
?>


<!-- Inhalt Bereich -->
<div class="inhalt">
    <aside>
    <?php include"template/suche.php"?>

    </aside>

    <main class="center">

        <div class="behalter">
        <!--  Wenn Suche gedrückt aber kein Treffer dann if -->

        <?php if(!empty($_GET['okbutton'])&&$eventausgabe == []):?>
            <p>Keine Events gefunden</p>

        <!--  Wenn Suche gedrückt und Treffer gefunden -->
        <?php elseif($eventausgabe != []):?>
        <?php foreach($eventausgabe as $event):?>

            <div class="event">

                <img src="bilder/main.jpg" alt="ein Bild von Event">
        <?php foreach($event as $key => $wert):?>
        <?php if($key == "id"):?>
                <a href="events_info.php?id=<?=$wert?>">mehr Information</a>
        <?php elseif($key == "plz" || $key == "ort"):?>
                <span><?= htmlspecialchars($wert)?></span>
        <?php else:?>
                <p><?= htmlspecialchars($wert)?></p>

        <?php endif;?>
        <?php endforeach;?>
            </div> <!--  event -->  
        <?php endforeach;?>

        <!--  Liste aller Events wenn keine Suche gedrückt -->
        <?php else:?>

        <?php include_once"template/ausgabe.phtml"?>

        <!-- Paginator -->

            <div class="paginator">
                <div>

                <a href=  "<?= $_SERVER['PHP_SELF'] ?>?seite=1" title="erste Seite" > &lt;&lt; </a>
                <a href= "<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seite - 1 ?>" title="vorherige Seite">&lt;</a>
                <span>Seite <?= $seite ?> von <?= $seiten ?> </span>
                <a href= "<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seite + 1 ?>" title="nächste Seite">&gt;  </a>
                <a href= "<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seiten ?>" title="letzte Seite">&gt;&gt;  </a>
                </div>
            </div>

        <?php endif;?>



        </div> <!--  behalter -->

    </main>

</div><!--  Inhalt -->

<?php include "template/footer.phtml"; ?>