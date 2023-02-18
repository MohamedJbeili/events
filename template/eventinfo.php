<?php 
require_once 'functions.php';

$beschreibung=$_GET['beschreibung'];


// Mit Beschreibung Info aus der Datenbank holen
$db = dbconnect();
$sql = "SELECT * FROM events WHERE beschreibung LIKE '".$beschreibung."'";
$result = mysqli_query($db, $sql);
while($datensatz = mysqli_fetch_assoc($result)) {
    $events[] = $datensatz;
}

?>



<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="url"/>
        <title></title>
    </head>
    <body>
        <div>
            <a href="suche.php?">Zurück zur Homepage</a>
        </div>
        <table>
            <caption>Eventausgabe</caption>
            <tr>
                <th>Thema</th>
                <th>Titel</th>
                <th>Beschreibung</th>
                <th>Datum</th>
                <th>Ort</th>
                <th>PLZ</th>
                <th>Preis</th>
            </tr>
            <?php foreach($events as $event): ?>
            <tr>
                    <?php foreach($event as $schluessel => $wert): ?>
                        <?php if($schluessel=='id'): ?>
                        <?php continue?>
                        <?php else: ?>
                            <td>
                                <?= htmlspecialchars($wert) ?>
                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
        </table>   
            <h3>Extra Informationen</h3>
            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. </p>
            <p> Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.</p>
            <p> Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut  </p>
    </body>
</html>