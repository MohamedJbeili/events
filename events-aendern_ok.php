<?php
require_once 'adminfunctions.php';

// Daten für die anzuzeigende Adresse
$adresse = [];

/** @var string[] $form alle Formularfelder */
$form = [];

// ID aus der URL holen
$id = !empty($_GET['id']) ? intval($_GET['id']) : 0;

if($id != 0) {
    // 1. Datenverbindung aufbauen
    $db = dbConnect();
    
    // 2. SQL-Statement erzeugen
    $sql = <<<EOT
        SELECT titel, 
               thema, 
               beschreibung,
               DATE_FORMAT(datum, '%d.%m.%Y') veranstaltungsdatum,
               ort,
               plz,
               preis
        FROM events 
        WHERE id = $id
EOT;
    
    // 3. SQL-Statement an die DB schicken und Ergebnis (Resultset) in Variablen speichern
    $result = mysqli_query($db, $sql);
    
    // 4. Ersten (und einzigen) Datensatz aus dem Resultset holen
    $veranstaltung = mysqli_fetch_assoc($result);
    
    // DB schließen
    mysqli_close($db);
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Erfassung</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <link href="adminstyles.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            
            <table>
                <caption>Sie haben folgende Daten eingegeben:</caption>
                <?php foreach ($veranstaltung as $key => $value): ?>
                <tr>
                    <th><?= ucfirst($key) ?></th>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <h3><a href="event-neu.php">Neues Event erfassen</a></h3>
            <h3><a href="admin.php">Übersicht anzeigen</a></h3>
        </div>
    </body>
</html>
