<?php
/*
 * Adminseite: Auf dieser Seite kann die Datenbank administriert werden
 * (ändern, löschen und hinzufügen von Veranstaltungen). Die angezeigte Liste
 * kann nach allen Kriterien, auf- und absteigend, sortiert werden. Über die
 * Suche kann die Anzeige reduziert werden. Die Suche berücksichtigt Thema, 
 * Titel, Beschreibung, Datum, Ort und PLZ. Die Anzeige der Datensätze ist auf
 * 15 begrenzt um die Übersicht zu wahren. Bei mehr als 15 Datensätzen kann mit
 * dem Paginator auf die Datensätze die die Anzahl von 15 übersteigen zugegriffen
 * werden.
 */

// Ausgelagerte Funktionen für die DB-Verbindung
require "template/functions.php";


// Anzahl der anzuzeigenden Treffer pro Seite festlegen
const PROSEITE = 20;
$fehler = '';
// array: Events aus der Datenbank
$events = [];

// Verbindung zur DB aufbauen, ggf. Fehlermeldung ausgeben
try {
    $db = dbConnect();
} catch (mysqli_sql_exception $ex) {
    echo 'Verbindungsfehler: ' . $ex->getMessage();
    die;
} 

// Suchstring holen
$suche = !empty($_GET['suche']) ? trim($_GET['suche']) : '';

// anzuzeigende Seite holen
$seite = !empty($_GET['seite']) ? intval($_GET['seite']) : 1;

// Suchstring für die DB escapen
$suchedb = mysqli_real_escape_string($db, $suche);

// WHERE-Klausel erzeugen
$where = '' != $suche ? "WHERE titel LIKE '%$suchedb%' OR beschreibung LIKE '%$suchedb%' OR thema LIKE '%$suchedb%' OR datum LIKE '%$suchedb%' OR ort LIKE '%$suchedb%' OR plz LIKE '%$suchedb%'" : '';

// Sortierung holen
$sort = $_GET['sort'] ?? 'id';
$richtung = $_GET['richtung'] ?? 'ASC';

// Gültigkeit der Sortierung prüfen
$sortfelder = ['id', 'thema', 'titel', 'beschreibung', 'datum', 'ort', 'plz', 'preis'];
if(!in_array($sort, $sortfelder)) {
    $sort = 'id';
}

$order = "ORDER BY $sort";

// SQL-Statement für die Abfrage der Trefferanzahl
$sql = "SELECT COUNT(*) anzahl FROM events $where";

// Trefferanzahl ermitteln
$result = mysqli_query($db, $sql);
$treffer = mysqli_fetch_assoc($result);
$anzahl = $treffer['anzahl'];

// Anzahl der Seiten berechnen
$seiten = max(1,ceil($anzahl / PROSEITE));

// aktuelle Seite prüfen (größer 0 und kleiner/gleich $seiten)
if($seite <= 0) {
    $seite = 1;
}
elseif($seite > $seiten) {
    $seite = $seiten;
}

// LIMIT-Klausel ermitteln
$offset = PROSEITE * ($seite - 1);
$limit = "LIMIT $offset, " . PROSEITE;

// SQL-Statement erzeugen
$sql = <<<EOT
        SELECT id
               , thema
               , titel
               , CONCAT(LEFT(beschreibung,40), IF(CHAR_LENGTH(beschreibung)>40,'...','')) beschreibung
               , DATE_FORMAT(datum, '%d.%m.%Y') adatum
               , ort
               , plz
               , preis
        FROM events
        $where
        $order
        $richtung
        $limit
EOT;


    // Statement an DB schicken und Ergebnis speichern
    $result = mysqli_query($db, $sql);
    // Datensätze aus dem Resultset holen
    while($datensatz = mysqli_fetch_assoc($result)) {
    $events[] = $datensatz;
    }

// DB-Verbindung schließen
mysqli_close($db);

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin</title>
        <link rel="stylesheet" href="css/adminstyles.css">
    </head>
    <body>
    <div class="wrapper">
        <table>
            <caption>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
                   <span><a href="events.php">Home</a></span>
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="richtung" value="<?= $richtung ?>">
                    <label for="suche">Suche gesammt:</label>
                    <input type="text" name="suche" id="suche" value="<?= htmlspecialchars($suche) ?>">
                    <button type="submit" name="suchbutton" class="suchen" value="42">suchen</button>
                </form>
            </caption>
            <tr>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=id&richtung=<?= 'id' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">ID</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=thema&richtung=<?= 'thema' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Thema</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=titel&richtung=<?= 'titel' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Titel</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=beschreibung&richtung=<?= 'beschreibung' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Beschreibung</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=datum&richtung=<?= 'datum' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Datum</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=ort&richtung=<?= 'ort' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Ort</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=plz&richtung=<?= 'plz' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">PLZ</a></th>
                <th><a href="<?= $_SERVER['PHP_SELF'] ?>?sort=preis&richtung=<?= 'preis' == $sort && 'ASC' == $richtung ? 'DESC' : 'ASC' ?>&suche=<?= htmlspecialchars($suche) ?>">Preis</a></th>
                <th colspan="2">&nbsp;</th>
            </tr>
            <?php foreach($events as $event): ?>
                <tr>
                <?php foreach($event as $value): ?>
                    <td><?= htmlspecialchars($value) ?></td>
                <?php endforeach; ?>
                <td><a class="aendern" href="event-aendern.php?id=<?= $event['id'] ?>">ändern</a></td>
                <td><a class="loeschen" href="event-loeschen.php?id=<?= $event['id'] ?>">löschen</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div id="unten">
            <div>
                <a class="hinzufuegen" href="event-neu.php">+ Neues Event erfassen</a>
            </div>
        <div id="paginator">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=1&sort=<?= $sort ?>" title="erste Seite">&lt;&lt;</a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seite - 1 ?>&sort=<?= $sort ?>" title="vorherige Seite">&lt;</a>
                Seite <?= $seite ?> von <?= $seiten ?>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seite + 1 ?>&sort=<?= $sort ?>" title="nächste Seite">&gt;</a>
                <a href="<?= $_SERVER['PHP_SELF'] ?>?seite=<?= $seiten ?>&sort=<?= $sort ?>" title="letzte Seite">&gt;&gt;</a>
        </div>
        <div>
            <a class="hinzufuegen" href="event-neu.php">Neues Event erfassen +</a>
        </div>
        </div>
        <div id="fehlermeldung">
            <?= $fehler ?>
        </div>
    </div>    
    </body>
</html>
