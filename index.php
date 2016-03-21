<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 09.03.2016
 * Time: 08:28
 */

$type = 'FTTC';


////////////////////////////// Ab hier keine Edit nötig //////////////////////////////////////////



include 'includes/head.php';

// Settings laden
include 'includes/config.inc.php';


// Klasse laden:
include 'includes/classLoader.php';


if ($type == 'FTTC') {
	// KLassen - Objekt erzeugen
	// $hDimari = new Dimari($host, $username, $password);
	$hDimari = new DimariExp($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi);


	// Setzte Gruppen-Type auf FTTC bzw. FTTH
	$hDimari->setExportType = $type;

	// Initial Methode aufrufen:
	$hDimari->initialGetFTTCServices();
}

// Debug ausgeben:

//echo "<pre>";
//echo "<hr>globalTarget<br>";
//print_r($hDimari->globalTarget);
//echo "<hr>";
//print_r($hDimari->globalData);
//echo "</pre><br>";


print ('<div style="position: fixed; bottom: 5px; right: 5px;">DONE</div>');


include 'includes/footer.php';

