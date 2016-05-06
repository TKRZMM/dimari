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
	public $productIDtoDesc = array('0' => array('770' => 'VDSL4me',
												 '771' => 'FTTx VoIP',
												 '830' => 'FIBER2home BASIC',
												 '831' => 'FIBER2home PREMIUM',
												 '832' => 'FIBER2home Internet Vorab',
												 '833' => 'DOCSIS FON',
												 '834' => 'DOCSIS WEB',
												 '835' => 'DOCSIS FON/WEB',
												 '836' => 'FIBER2home WEB+',
												 '810' => 'FIBER4business VoIP'
	),
									'1' => array('838' => 'Surf 50/10 Flat',
												 '839' => 'Surf 50/10 Flat und Fone Flat',
												 '840' => 'Business Light Surf 50/10 Flat',
												 '841' => 'Business Light Surf 100/20 Flat'
									),
									'3' => array('837' => 'FIBER2home Schüttorf'
									)
	);


	// Zuweisung Produkt_ID zu neuer Produkt ID
	public $productIDTo_DOCSIS_0 = array('10026'    => '833',        // fiberFON
										 '10027'    => '834',        // fiberWEB
										 'together' => '835'         // Zusammen
	);

	// Zuweisung Produkt_ID zu neuer Produkt ID
	// TKRZ
	public $productIDTo_GENEXIS_0 = array('10026'    => '810',       // fiberFON
										  '10027'    => '832',       // fiberWEB
										  '10044'    => '832',       // fiberWEB
										  '10071'    => '832',       // fiberWEB
										  'together' => '830',       // Zusammen
										  '10025'    => '831'        // + TV
	);

	// RheiNet
	public $productIDTo_GENEXIS_1 = array('10061' => '840',        // Business Light Surf 50/10 Flat
										  '10063' => '839',        // Surf 50/10 Flat und Fone Flat
										  '10062' => '841',        // Business Light Surf 100/20 Flat
										  '10060' => '838',        // Surf 50/10 Flat
										  '10049' => '838'        // Surf 50/10 Flat
	);

	// RheiNet
	public $productIDTo_GENEXIS_3 = array('10043' => '837');


	// Zuweisung Optionen
	// Format MandantID => AlteProductID (Optionen Speedupdate oder 5 Euro Rabatt) => Bezeichnung aus $productIDtoOptionIDtoDesc
	public $productOptions = array('0' => array('10028' => 'Speedupgrade',
												'10072' => 'Speedupgrade',
												'10001' => '5 Euro Rabatt'
	),
								   '1' => array('xxx' => 'Speedupgrade xxxxxxx',
												'xyz' => '5 Euro Rabatt xxxxxxx'
								   ),
								   '3' => array('10028' => 'Speedupgrade',
												'xyz' => '5 Euro Rabatt xxxxxxx'
								   )
	);


	// Anhang - Option:
	// Format: ProduktID => OotionID => OptionBezeichnung
	public $productIDtoOptionIDtoDesc = array('770' => array('7'  => 'NOCGN',
															 '15' => 'CGN',
															 '21' => '5 Euro Rabatt',
															 '35' => 'Gutschrift Anschlussaktivierung',
															 '45' => 'Eigene Kunde CPE'),
											  '832' => array('85' => '5 Euro Rabatt',
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