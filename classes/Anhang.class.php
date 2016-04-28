<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 12:02
 */
class Anhang
{

	// Anhang - Produkt:
	// Format: MandantID => ProduktID => ProduktBezeichnung
	private $productIDtoDesc = array('0' => array('770' => 'VDSL4me',
												  '771' => 'FTTx VoIP',
												  '830' => 'FIBER2home BASIC',
												  '831' => 'FIBER2home PREMIUM',
												  '832' => 'FIBER2home Internet Vorab',
												  '833' => 'DOCSIS FON',
												  '834' => 'DOCSIS WEB',
												  '835' => 'DOCSIS FON/WEB',
												  '836' => 'FIBER2home + WEB',
												  '810' => 'FIBER4business VoIP'
	),
									 '1' => array('838' => 'Surf 50/10 Flat',
												  '839' => 'Surf 50/10 Flat und Fone Flat',
												  '840' => 'Business Light Surf 50/10 Flat',
												  '841' => 'Business Light Surf 100/20 Flat',
												  '837' => 'FIBER2home Schüttorf'
									 )
	);


	// Anhang - Option:
	// Format: ProduktID => OotionID => OptionBezeichnung
	private $productIDtoOptionIDtoDesc = array('770' => array('7'  => 'NOCGN',
															  '15' => 'CGN',
															  '21' => '5 Euro Rabatt',
															  '35' => 'Gutschrift Anschlussaktivierung',
															  '45' => 'Eigene Kunde CPE'),
											   '780' => array('20' => '5 Eruo Rabatt',
															  '29' => 'Speedupgrade'),
											   '782' => array('25' => '5 Euro Rabatt',
															  '30' => 'Speedupgrade'),
											   '830' => array('55' => '5 Euro Rabatt',
															  '56' => 'Speedupgrade'),
											   '831' => array('57' => '5 Euro Rabatt',
															  '58' => 'Speedupgrade'),
											   '833' => array('59' => 'Speedupgrade'),
											   '834' => array('60' => 'Speedupgrade'),
											   '835' => array('75' => 'Speedupgrade'),
											   '836' => array('63' => '5 Euro Rabatt'),
											   '840' => array('64' => 'zusätzliche IP'),
											   '841' => array('65' => 'zusätzliche IP'),
											   '837' => array('61' => 'Speedupgrade',
															  '62' => 'Telefon Grundgebühr'),

	);










	// Klassen - Konstruktor
	public function __construct()
	{

	}   // END public function __construct(...)










	// Liefert alle OptionenID=>Beschreibung (Array) zu einem Produkt
	function getOptionsByProductID($argProductID)
	{

		$return = array();

		// Gibt es Optionen zu der ProduktID? -> Wenn nicht Methoden-Abbruch
		if (!isset($this->productIDtoOptionIDtoDesc[$argProductID]))
			return $return;


		return $this->productIDtoOptionIDtoDesc[$argProductID];

	}    // END	function getOptionsByProductID($argProductID)










	// Liefert die Produkt-Beschreibung zu einer übergebenen ProductID
	function getProductDescByProductID($argMandandtID, $argProductID)
	{

		// Mandandt ID gesetzt? -> Wenn nicht Methoden-Abbruch
		if (!isset($this->productIDtoDesc[$argMandandtID]))
			return false;

		// ProduktID gesetzt? -> Wenn nicht Methoden-Abbruch ... die übergebene ProduktID ist nicht definiert!
		if (!isset($this->productIDtoDesc[$argMandandtID][$argProductID]))
			return false;

		return $this->productIDtoDesc[$argMandandtID][$argProductID];

	}    // END function getProductDescByProductID(...)


}   // END class Anhang