<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 09.03.2016
 * Time: 08:28
 */

include 'includes/head.php';

// Settings laden
include 'includes/config.inc.php';


// Klasse laden:
include 'includes/classLoader.php';


// KLassen - Objekt erzeugen
$hDimari = new Dimari($host, $username, $password);


// Setzte Gruppen-Type auf FTTC bzw. FTTH
$hDimari->getGroupType = 'FTTC';
$hDimari->getAccountType  = 'FTTC';


// Initial Methode aufrufen:
$hDimari->initialGetFTTCServices();






// Status ausgeben:
if (isset($hDimari->globalMessage['Status'])){
    print ('<div style="position: fixed; top: 5px; right: 5px; background-color: beige">STATUS:<br>');
    foreach ($hDimari->globalMessage['Status'] as $index=>$statusInfo){
        print ('&nbsp;# ' . $index . ' > ' .  $statusInfo . "&nbsp;<br>");
    }
    print ('</div>');
}

//echo "<pre>";
//echo "<hr>globalTarget<br>";
//print_r($hDimari->globalTarget);
//echo "<hr>";
//print_r($hDimari->globalData);
//echo "</pre><br>";


print ('<div style="position: fixed; bottom: 5px; right: 5px;">DONE</div>');


include 'includes/footer.php';

