<?php
/*
 * Auf dieser Seite hat der Admin die Möglichkeit noch einmal zu prüfen
 * ob er den zuvor ausgewählten Datensatz tatsächlich löschen möchte.
 */

 require "template/functions.php";

// ID des zu löschenden Datensatzes holen
$id = !empty($_GET['id']) ? intval($_GET['id']) : 0;

// String für Fehlermeldung
$fehler = '';


// Prüfen, ob gültige ID übergeben wurde
if(0 < $id) {
    // gültige ID erhalten
    $db = dbConnect();
    
    // Wurde der Bestätigungsbutton gedrückt
    if(empty($_GET['ok'])) {
        // Bestätigung noch nicht erhalten
        
        // SQL-Statement zum Holen des Datensatzes
        $sql = <<<EOT
                SELECT id
                        , thema
                        , titel
                        , beschreibung
                        , DATE_FORMAT(datum, '%d.%m.%Y') datum
                        , ort
                        , plz
                        , preis
                FROM events
                WHERE id = $id
EOT;
        
        // Statement an die DB schicken und Ergebnis speichern
        $result = mysqli_query($db, $sql);
        
        // Abfragen, ob Datensatz gefunden wurde
        if(mysqli_num_rows($result) == 0) {
            $fehler = 'Datensatz nicht gefunden';
        }
        else {
            // Datensatz wurde gefunden
            $event = mysqli_fetch_assoc($result);
        }
    }
    else {
        // Bestätigung erhalten
        // SQL-Statement zum Löschen des Datensatzes
        $sql = 'DELETE FROM events WHERE id = ' . $id; // WHERE nicht vergessen!
        
        // SQL-Statement ausführen
        mysqli_query($db, $sql);
        
        // Weiterleiten auf Erfolgsseite
        header('Location: event-loeschen-ok.php');
        die;
        
    }
    // DB-Verbindung schließen
    mysqli_close($db);
}
else {
    // keine gültige ID bekommen
    $fehler = 'Ungültige oder fehlende Datensatz-ID';
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Event löschen</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/adminstyles.css">
    </head>
    <body>
        <h1>Event löschen</h1>

        <?php if(!empty($fehler)): ?>
        <h3><span><?= $fehler ?></span></h3>
        
        <?php else: ?>
        <h3 class="h3red">Soll dieser Datensatz wirklich gelöscht werden?</h3>
        
        <table>
            <?php foreach($event as $feld => $wert): ?>
            <tr>
                <th><?= ucfirst($feld) ?></th>
                <td><?= htmlspecialchars($wert) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div id="löschen">
                <button type="submit" name="ok" value="137">LÖSCHEN</button>
            </div>
        </form>
        
        <?php endif; ?>
        <h3><a href="admin.php">zurück zur Übersicht</a></h3>
    </body>
</html>
