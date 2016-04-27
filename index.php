<?php
/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 09.03.2016
 * Time: 08:28
 */


$mandant = 'TKRZ';    // TKRZ / RheiNet / Schuettorf
$type = 'FTTH';


////////////////////////////// Ab hier keine Edit nötig //////////////////////////////////////////



include 'includes/head.php';

// Settings laden
include 'includes/config.inc.php';


// TKRZ
if ($mandant == 'TKRZ') {

	// FTTC
	if ($type == 'FTTC') {

		include 'classes/Dimari.class.php';	// KLasse Dimari laden
		include 'classes/DimariExp.class.php';	// Klasse DimariExport laden

		// KLassen - Objekt erzeugen
		$hDimari = new DimariExp($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi);

		// Setzte Gruppen-Type auf FTTC bzw. FTTH
		$hDimari->setExportType = $type;

		// Initial Methode aufrufen:
		$hDimari->initialGetFTTCServices();
	}



	// FTTH
	elseif ($type == 'FTTH') {

		include 'classes/Dimari_TKRZ_FTTH.class.php';	// KLasse Dimari laden
		include 'classes/DimariExp_TKRZ_FTTH.class.php';	// Klasse DimariExport laden

		// Klassen - Objekt erzeugen
		$hDimari = new DimariExp_TKRZ_FTTH($host, $username, $password);

		// Setzte Gruppen-Type auf FTTC bzw. FTTH
		$hDimari->setExportType = $type;

		// Initial Methode aufrufen:
		$hDimari->initialGetFTTHServices();
	}

}    // END TKRZ



elseif ($mandant == 'RheiNet') {

	// FTTH
	if ($tpye == 'FTTH') {

	}

}    // END RheiNet



elseif ($mandant == 'Schuettorf') {

	// FTTH
	if ($tpye == 'FTTH') {

	}

}    // END RheiNet



// Debug ausgeben:

//echo "<pre>";
//echo "<hr>globalTarget<br>";
//print_r($hDimari->globalTarget);
//echo "<hr>";
//print_r($hDimari->globalData);
//echo "</pre><br>";


print ('<div style="position: fixed; bottom: 5px; right: 5px;">DONE</div>');


include 'includes/footer.php';

