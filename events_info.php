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
$id = $_GET["id"];
//mysql Statement
$sql = <<<EOT
SELECT thema
,titel
,beschreibung
,ort
,plz
,preis
FROM events
WHERE id = $id;

EOT;
//Daten an Daten Bank schicken und speichern
$result = mysqli_query($db, $sql);

$events[] = mysqli_fetch_assoc($result);
// echo "<pre>";
// print_r($events);
// echo "</pre>";
include "template/header.phtml";

?>

         <!-- Inhalt Bereich -->
        <div class="inhalt info">
            <!-- <aside>
               

            </aside> -->
            <main class="center info">
                <div class="behalter">

        <!--  table -->  
                    
                <table >
                    <?php foreach($events as $event):?>
                    
                    <?php foreach($event as $key => $wert):?>
                    <tr>
                    <th><?=ucfirst($key)?></th> 
                    <td><?= htmlspecialchars($wert)?></td>
                    </tr>
                    <?php endforeach;?>
                    
                    
                    <?php endforeach;?>
                    
                    
                </table> 
                

                
                </div> <!--  behalter -->

            </main>

        </div><!--  Inhalt -->

        <?php include "template/footer.phtml"; ?>