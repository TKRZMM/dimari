<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 15:08
 */
abstract class CollectData extends Message
{

	/////////// ACHTUNG !!! VERGISS DIE CONFIG - DATEI NICHT !!! ////////////

	// TKRZ / RheiNet / Schuettorf
//	public $setMandant = 'TKRZ';	// TKRZ
	 public $setMandant = 'RheiNet';	// RheiNet
//	public $setMandant = 'Schuettorf';	// Schuettorf

	// 0 TKRZ / 1 RheiNet / 3 Schuettorf
//	public $setMandantID = 0;	// TKRZ
	public $setMandantID = 1;	// RheiNet
//	public $setMandantID = 3;	// Schuettorf

	// Export Typ: FTTC oder FTTH
	public $setExportType = 'FTTH';

	// Telephonbuch ID Referenz
	// Bei TKRZ: 0 = Kein Eintrag
	//			10001 Standardeintrag
	// 			10002 nur Name und Rufnummer / ohne Adresse
	// 			10003 Wissing Heike und Ansgar
	public $setPhoneBookEntryIDToVal = array('RheiNet'    => array('0'     => '',
																   '10001' => 'A',
																   '10002' => 'V',
																   '10003' => 'N'
	),
											 'TKRZ'       => array('0'     => '',
																   '10001' => 'A',
																   '10002' => 'V',
																   '10003' => 'N'
											 ),
											 'Schuettorf' => array('0'     => '',
																   '10001' => 'A',
																   '10002' => 'V',
																   '10003' => 'N'
											 )
	);

	// Export - Datei schreiben? (default yes = Datei wird erzeugt)
	public $setExpFileCreation = 'no';

	//  ACHTUNG Hauptverteiler, Kabelverzweiger und DSLAM_PORT habe ich nur bei GENEXIS - Kunden!
	//  ACHTUNG keine Bridge-Daten bei DOCSIS Kunden... ist richtig so!
	// Nur Customer einlesen der Kundennummer x besitzt?
	public $setOnlyExampleCustomerID = '';
//	public $setOnlyExampleCustomerID = '20010412';
	// 20010028 ... Kunde mit Telefonbucheintrag (Docsis)
	// 20010184 ... Kunde mit mehr als einer Telefonnummer
	// 20010230 ... ?
	// 20010272 ... GENEXIS Kunde
	// 20010043 ... Weder GENEXIS noch Arris
	// 20010296 ... GENEXIS + TV
	// 20010398 ... GENEXIS Bridge J N (!) + Grundgebühr Energiekunde = 5 Euro Rabatt
	// 20010034 ... Speedupgrade
	// 20010398 ... Bridge Mode J N ... ?
	// 20010124	... Bridge Mode N N	.. BRIDGE
	// 20010123 ... Bridge Mode N J	... IPFON
	// 20010261 ... ?
	// 20010028 ... ?

	// RheiNet
	// 100002 ... normaler Kunde
	// 100006 ... Router MAC Adresse prüfen


	// Wie viele Customer sollen eingelesen werden?
	// 0 für keine Einschränkung beim Limit
	public $setReadLimitCustomer = '0';


	// Customer einlesen die in der Customer Gruppe ... sind
	// Tabelle: CUSTOMER_GROUP
	// Feld:    GROUP_ID
	// Format: Mandant => GROUP_ID
	public $setCustomerByGroupID = array('Schuettorf' => array('100001',
															   '100002',
															   '100005',
															   '100006'
	),
										 'RheiNet'    => array('100001',
															   '100002',
															   '100005',
															   '100006'
										 ),
										 'TKRZ'       => array('100001',
															   '100002',
															   '100005',
															   '100006',
															   '100007',
															   '100008',
															   '100010',
										 )
	);


	// Customer einlesen die NICHT StatusID x haben
	// Tabelle: CUSTOMER_STATUS
	// Feld:    STATUS_ID
	// Nicht gekündigt '10004' ... nicht archiv '2'
	public $setNoCustomerInStatusID = array('Schuettorf' => array('10004', '2'),
											'RheiNet'    => array('10004', '2'),
											'TKRZ'       => array('10004', '2')
	);

	// Customer mit folgender Nummer NICHT einlesen
	public $setDoNotReadThisCustomerIDs = array('20010000',    // Vorname: Max 	Nachname: Testmeier
												'20010081',    // Vorname: tkrz 	Nachname: tkrz_test2
												'20011099', // Vorname: Thomas	Nachname: Eggenkämper ... Kunde mit ungültigem Vertrag
												'20011244'    // Vorname: Maximilian 	Nachname: Wellmann ... Kunde mit ungültigem Vertrag
	);

	// Vertragstatus muss grösser Null sein? (bool var)	(DEFAULT false)
	private $setReadOnlyContractStatusAboveNull = false;

	// VOIPsatus muss grösser Null sein? (bool var)	(DEFAULT true)
	//private $setReadOnlyVOIPStatusAboveNull = true;
	private $setReadOnlyVOIPStatusAboveNull = false;

	// Sollen nur unterzeichnete Verträge ermittelt werden? (bool var)
	// Abschnitt nicht programmiert!!!
	// private $setReadOnlySignedContracts = true;

	// Produkt-Bereinigung
	// Wenn Produkt ID a und Produkt ID b ... dann lösche a und behalte b
	// Format: Kennung Mandant => a => b
	public $setClearProduct = array('RheiNet'    => array('10054' => '10061',
														  '10050' => '10063',
														  '10055' => '10062',
														  '10049' => '10060'),
									'TKRZ'       => array('10035' => '10064',
														  '10042' => '10043'),
									'Schuettorf' => array('x' => 'x')
	);


	// Produkt-Bereinigung
	// Wenn Produkt vorhanden ist... lösche ich es aus der Exportliste
	// Format: Kennung Mandant => DelProduktID
	public $setDelProduct = array('RheiNet' => array('10049'),
								  'TKRZ'    => array('10030', '10033'),
								  'Schuettorf'    => array('10030', '10029','10001')
	);


	// GENEXIS ... DOCSIS Unterscheidung
	// Tabelle: RESOURCE_PACKAGES
	// Feld:    RP_ID
	public $setModemType = array('RheiNet' => array('GENEXIS' => array('10007', '10009'),
													'DOCSIS'  => array('10006')
	),
								 'TKRZ'    => array('GENEXIS' => array('10007', '10009'),
													'DOCSIS'  => array('10015', '10006')
								 ),
								 'Schuettorf'    => array('GENEXIS' => array('10007', '10009'),
													'DOCSIS'  => array('10015', '10006')
								 )
	);

	// Gellöschte Kunden der Exportliste anzeigen die nicht Doscis oder Arris sind? (Default false)
	public $showDeletedCustomerNumberByModemType = true;

	////////////////////////////////// Do not edit below this line!!! /////////////////////////////



	// Datenbank Variable ... werden durch den Construktor gesetzt
	private $myHost;
	private $myUsername;
	private $myPassword;

	// Datenbank Object
	private $dbF;

	// Var-Array enthält alle Customer/Kunden Objekt-Handler
	public $custArray = array();

	// Var-Array enhält alle Carrier
	// Tabelle: CARRIER
	// Feld:    *
	public $globalCarrierData = array();


	public $globalLastFilename = ''; // Erzeugte Datei










	// Klassen - Konstruktor
	public function __construct($host, $username, $password)
	{

		$this->myHost = $host;
		$this->myUsername = $username;
		$this->myPassword = $password;

	}   // END public function __construct(...)










	// Initial und Steuer-Methode für das Daten-Einlesen
	function initialCollectData()
	{

		$this->outNow('Start', '...', 'Info');

		// Export Typ FTTC oder FTTH gesetzt?
		if ((!isset($this->setMandant)) || (strlen($this->setMandant) < 1)) {

			$this->outNow('FEHLER: "$setMandant" muss in der Klasse: "CollectData" definiert werden!', 'Error -> Stop', 'Info');

			return false;
		}

		$this->outNow('Gewählter Mandant:', $this->setMandant, 'Info');
		$this->outNow('Gewählter Daten-Typ:', $this->setExportType, 'Info');


		// Info Datenerfassung start
		$this->addMessage('Datenerfassung', 'START ...', 'Runtime');


		// Datenbankverbindung zum Dimari-System aufbauen
		$this->addMessage('Dimari Datenbnkverbindung herstellen', 'START ...', 'Runtime');
		if ($this->createDimariDBConnection())
			$this->addMessage('Dimari Datenbnkverbindung herstellen', '... DONE', 'Runtime');
		else {
			$this->addMessage('Dimari Datenbnkverbindung herstellen', '... FAIL', 'Runtime');

			return false;
		}



		// Carrier Referenz einlesen
		$this->outNow('Carrier Referenz einlesen', 'START ...', 'Runtime');
		if ($this->getCarrierRef())
			$this->outNow('Carrier Referenz einlesen', '... DONE', 'Runtime');
		else {
			$this->outNow('Carrier Referenz einlesen', '... FAIL', 'Runtime');

			return false;
		}



		// Customer einlesen die in der angegebenen GruppenID enthalten sind
		$this->outNow('Customer einlesen die in der angegebenen GruppenID enthalten sind', 'START ...', 'Runtime');
		if ($this->getCustomerByGroupID())
			$this->outNow('Customer einlesen die in der angegebenen GruppenID enthalten sind', '... DONE', 'Runtime');
		else {
			$this->outNow('Customer einlesen die in der angegebenen GruppenID enthalten sind', '... FAIL', 'Runtime');

			return false;
		}



		// Contracts einlesen die zu den ausgewählten Customer gehören
		$this->outNow('Contracts einlesen die zu den ausgewählten Customer gehören', 'START ...', 'Runtime');
		if ($this->getContractsByCustomerID())
			$this->outNow('Contracts einlesen die zu den ausgewählten Customer gehören', '... DONE', 'Runtime');
		else {
			$this->outNow('Contracts einlesen die zu den ausgewählten Customer gehören', '... FAIL', 'Runtime');

			return false;
		}



		// CO_Products einlesen die zu den Contracts gehören
		$this->outNow('CO_Products einlesen die zu den Contracts gehören', 'START ...', 'Runtime');
		if ($this->getProductsByContractID())
			$this->outNow('CO_Products einlesen die zu den Contracts gehören', '... DONE', 'Runtime');
		else {
			$this->outNow('CO_Products einlesen die zu den Contracts gehören', '... FAIL', 'Runtime');

			return false;
		}



		// VOIP Daten einlesen
		$this->outNow('VOIP Daten einlesen', 'START ...', 'Runtime');
		if ($this->getCOVoicedataByCOID())
			$this->outNow('VOIP Daten einlesen', '... DONE', 'Runtime');
		else {
			$this->outNow('VOIP Daten einlesen', '... FAIL', 'Runtime');

			return false;
		}



		// VOIP - Telefonnummern einlesen
		$this->outNow('VOIP - Telefonnummern einlesen', 'START ...', 'Runtime');
		if ($this->getSubscriberByCOVID())
			$this->outNow('VOIP - Telefonnummern einlesen', '... DONE', 'Runtime');
		else {
			$this->outNow('VOIP - Telefonnummern einlesen', '... FAIL', 'Runtime');

			return false;
		}



		// Telefonbucheinträge ermitteln
		$this->outNow('Telefonbucheinträge ermitteln', 'START ...', 'Runtime');
		if ($this->getPhoneBookEntrysByCustomerID())
			$this->outNow('Telefonbucheinträge ermitteln', '... DONE', 'Runtime');
		else {
			$this->outNow('Telefonbucheinträge ermitteln', '... FAIL', 'Runtime');

			return false;
		}



		// Bereinige Cisco Kabelmodem und Arris Kabelmodem zugunsten von Arris
		$this->outNow('Bereinige Produkte z.B.: Cisco Modem und Arris Modem zugunsten von Arris', 'START ...', 'Runtime');
		if ($this->cleanResetProductVars())
			$this->outNow('Bereinige Produkte z.B.: Cisco Modem und Arris Modem zugunsten von Arris', '... DONE', 'Runtime');
		else {
			$this->outNow('Bereinige Produkte z.B.: Cisco Modem und Arris Modem zugunsten von Arris', '... FAIL', 'Runtime');

			return false;
		}


		// Bereinige Produk-Liste z.B. von TAE - Adapter
		$this->outNow('Bereinige Produk-Liste z.B. von TAE - Adapter', 'START ...', 'Runtime');
		if ($this->cleanProductVars())
			$this->outNow('Bereinige Produk-Liste z.B. von TAE - Adapter', '... DONE', 'Runtime');
		else {
			$this->outNow('Bereinige Produk-Liste z.B. von TAE - Adapter', '... FAIL', 'Runtime');

			return false;
		}



		// MAC - und Hardwaredaten ermitteln
		$this->outNow('MAC - und Hardwaredaten ermitteln', 'START ...', 'Runtime');
		if ($this->getHardwareEntrysByCoProducts())
			$this->outNow('MAC - und Hardwaredaten ermitteln', '... DONE', 'Runtime');
		else {
			$this->outNow('MAC - und Hardwaredaten ermitteln', '... FAIL', 'Runtime');

			return false;
		}


		// Lösche Export-Kunden die nicht DOCIS oder GENEXIS- Kunden sind
		$this->outNow('Lösche Export-Kunden die nicht DOCIS oder GENEXIS- Kunden sind', 'START ...', 'Runtime');
		if ($this->delUserNoDocsisOrArris())
			$this->outNow('Lösche Export-Kunden die nicht DOCIS oder GENEXIS- Kunden sind', '... DONE', 'Runtime');
		else {
			$this->outNow('Lösche Export-Kunden die nicht DOCIS oder GENEXIS- Kunden sind', '... FAIL', 'Runtime');

			return false;
		}



		// Ermittel Kunden GENEXIS oder DOCSIS
		$this->outNow('Ermittel Kunden GENEXIS oder DOCSIS', 'START ...', 'Runtime');
		if ($this->getGenexisOrDocsis())
			$this->outNow('Ermittel Kunden GENEXIS oder DOCSIS', '... DONE', 'Runtime');
		else {
			$this->outNow('Ermittel Kunden GENEXIS oder DOCSIS', '... FAIL', 'Runtime');

			return false;
		}



		// Erstes Daten Setzen / Füllen ... in Klasse OutData
		$this->outNow('Erstes Daten Setzen', 'START ...', 'Runtime');
		if ($this->custExpSetFillStepOne())
			$this->outNow('Erstes Daten Setzen', '... DONE', 'Runtime');
		else {
			$this->outNow('Erstes Daten Setzen', '... FAIL', 'Runtime');

			return false;
		}


		// Debug - Ausgabe erzwingen wenn nur ein Kunde gesucht wird
		if (strlen($this->setOnlyExampleCustomerID) > 0) {
			echo "<pre><hr>";
			print_r($this->custArray);
//		foreach ($this->custArray as $x)
//			print_r($x->custExpSet);
			echo "<hr></pre><br>";
		}
		else {
//			echo "<pre><hr>";
//			print_r($this->custArray);
//			echo "<hr></pre><br>";
		}



		// Info Datenerfassung start
		$this->addMessage('Datenerfassung', '... DONE', 'Runtime');

		return true;

	}    // END function initialCollectData()










	// Ermittel Kunden GENEXIS oder DOCSIS
	private function getGenexisOrDocsis()
	{

		$delCnt = '0';
		$unknown = array();

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {


			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Produkte
			if ((!isset($curCustObj->custProductSet)) || (count($curCustObj->custProductSet) < 1))
				return '';

			$curType = 'unknown';

			// Durchlauf Produkte
			foreach($curCustObj->custProductSet as $curProductID => $productArray) {

				if ((!isset($productArray['SR_RP_ID'])) || (strlen($productArray['SR_RP_ID']) < 1))
					continue;

				$curRP_ID = $productArray['SR_RP_ID'];

				if (in_array($curRP_ID, $this->setModemType[$this->setMandant]['GENEXIS'])) {
					$curType = 'GENEXIS';
					break;
				}
				elseif (in_array($curRP_ID, $this->setModemType[$this->setMandant]['DOCSIS'])) {

					$curType = 'DOCSIS';
					break;
				}
				else
					$curType = 'unknown';

			}    // END // Durchlauf Produkte

			// Wenn weder GENEXIS nocht DOCSIS lösche ich den Customer aus der Export - Liste
			if ($curType == 'unknown') {
				$delCnt++;
				unset($this->custArray[$curCustomerID]);
				$unknown[] = $curCustomerID;
			}
			else
				$curCustObj->custModemType = $curType;


		}    // END // Duchlauf Customer - Handler

		// Gelöschte CustomerNummern ausgeben?
		if ((count($unknown) > 0) && ($this->showDeletedCustomerNumberByModemType)) {
			print('Gelöschte KundenNummer die nicht ins Muster Docis oder GENEXIS passen (CustomerID) :');
			echo "<pre><br>";
			print_r($unknown);
			echo "<hr></pre><br>";
		}

		// Wieviel Kunden haben wir jetzt noch?
		$cntCustomerToExport = count($this->custArray);
		if ($cntCustomerToExport <= 0)
			$cntCustomerToExport = '0';

		// Status:
		$this->addMessage('&sum; Gelöschte Kunden weder Docis noch GENEXIS 2', '' . $delCnt . '', 'Info');
		$this->addMessage('&sum; Gelöschte Kunden weder Docis noch GENEXIS 2', '' . $delCnt . '', 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Info');

		return true;
	}    // END private function getGenexisOrDocsis()










	// Develop Methode ... zeigt Kunden die weder Docsis nocht GENEXIS sind!
	private function delUserNoDocsisOrArris()
	{

		$unknown = array();

		// Check welche RP_ID es noch gibt
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			if (isset($curCustObj->custProductSet)) {

				foreach($curCustObj->custProductSet as $curProductID => $productArray) {

					if ((isset($productArray['SR_RP_ID'])) && (strlen($productArray['SR_RP_ID']) > 0)) {

						$curRP_ID = $productArray['SR_RP_ID'];    // Leichter zu handhaben

						// Prüfe ob ich die RP_ID nicht kenne:
						if ((!in_array($curRP_ID, $this->setModemType[$this->setMandant]['GENEXIS'])) && ((!in_array($curRP_ID, $this->setModemType[$this->setMandant]['DOCSIS'])))) {

							$unknown[][$curRP_ID] = $curCustomerID;

							unset($this->custArray[$curCustomerID]);
						}

					}

				}

			}

		}

		// Gelöschte CustomerNummern ausgeben?
		if ((count($unknown) > 0) && ($this->showDeletedCustomerNumberByModemType)) {
			print('Gelöschte KundenNummer die nicht ins Muster Docis oder GENEXIS passen (RP_ID=>CustomerID) :');
			echo "<pre><br>";
			print_r($unknown);
			echo "<hr></pre><br>";
		}

		if (count($unknown) > 0) {
			$delCnt = count($unknown);

			// Wieviel Kunden haben wir jetzt noch?
			$cntCustomerToExport = count($this->custArray);
			if ($cntCustomerToExport <= 0)
				$cntCustomerToExport = '0';

			// Status:
			$this->addMessage('&sum; Gelöschte Kunden weder Docis noch GENEXIS ', '' . $delCnt . '', 'Info');
			$this->addMessage('&sum; Gelöschte Kunden weder Docis noch GENEXIS ', '' . $delCnt . '', 'Sum');
			$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Sum');
			$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Info');
		}

		return true;
	}










	// MAC - und Hardwaredaten ermitteln
	private function getHardwareEntrysByCoProducts()
	{

		// Counter Var
		$countHardware = '0';

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Durchlauf Produkte des Kunden
			foreach($curCustObj->custProductSet as $curProductID => $curProductArray) {

				// Besitzt das aktuell Produkt keine SR_ID ... können wir die weitere Verarbeitung überspringen!
				if ((!isset($curProductArray['SR_ID'])) || (strlen($curProductArray['SR_ID']) < 1))
					continue;

				// Ab hier wenn wir eine Hardware mit SR_ID gefunden haben ... also ein Modem
				$curSR_ID = $curProductArray['SR_ID'];


				// TODO Schüttorf
				// Query's für TKRZ
				if ($this->setMandantID == '0') {
					if ($curProductID != '10043') {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.TECHNOLOGY_ID ='0'";
					}
					else {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.COMPONENT_ID ='10041'";
					}
				}
				elseif ($this->setMandantID == '1') {    // Query's für RheiNet
					if ($curProductID != '10043') {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.COMPONENT_ID ='10036'";
					}
					else {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.TECHNOLOGY_ID ='0'";
					}

				}
				elseif ($this->setMandantID == '3') {    // Query's für Schüttorf
					if ($curProductID != '10043') {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.COMPONENT_ID ='10036'";
					}
					else {
						$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $curSR_ID . "'
							AND sd.TECHNOLOGY_ID ='0'";
					}

				}

				$result = ibase_query($this->dbF, $query);

				while ($row = ibase_fetch_object($result)) {

					$countHardware++;

					$curCustObj->custProductSet[$curProductID]['SR_PARENT_ID'] = $row->PARENT_ID;
					$curCustObj->custProductSet[$curProductID]['SR_DESCRIPTION'] = $row->DESCRIPTION;
					$curCustObj->custProductSet[$curProductID]['SR_COMPONENT_ID'] = $row->COMPONENT_ID;
					$curCustObj->custProductSet[$curProductID]['SR_TECHNOLOGY_ID'] = $row->TECHNOLOGY_ID;
					$curCustObj->custProductSet[$curProductID]['SR_DATA_1'] = $row->DATA_1;
					$curCustObj->custProductSet[$curProductID]['SR_DATA_2'] = $row->DATA_2;
					$curCustObj->custProductSet[$curProductID]['SR_DATA_3'] = $row->DATA_3;
					$curCustObj->custProductSet[$curProductID]['SR_DATA_4'] = $row->DATA_4;
					$curCustObj->custProductSet[$curProductID]['SR_ROOT_ID'] = $row->ROOT_ID;
					$curCustObj->custProductSet[$curProductID]['SR_RP_ID'] = $row->SR_RP_ID;

				}    // END while

				ibase_free_result($result);



				// Jetzt MyGate -> unit -> port ermitteln
				// 1. Port ermitteln
				$returnPortArray = $this->getPortAndParentIDBySR_ID($curSR_ID);
				$curCustObj->custProductSet[$curProductID]['DSLAM_PORT'] = $returnPortArray['PORT'];

				// 2. Unit ermitteln
				$returnUnitArray = $this->getUnitAndParentIDBySD_ID($returnPortArray['PARENT_ID']);
				$curCustObj->custProductSet[$curProductID]['KABELVERZWEIGER'] = $returnUnitArray['UNIT'];

				// 3. MFG (MyGate) ermitteln
				$returnMFGArray = $this->getMFGBySD_ID($returnUnitArray['PARENT_ID']);
				$curCustObj->custProductSet[$curProductID]['HAUPTVERTEILER'] = $returnMFGArray['MFG'];
				$curCustObj->custProductSet[$curProductID]['HAUPTVERTEILER_IP'] = $returnMFGArray['MFG_IP'];

			}    // END // Durchlauf Produkte des Kunden

		}    // END // Duchlauf Customer - Handler


		$this->addMessage('&sum; Ermittelte Hardware (Router) -Einträge ', '' . $countHardware . '', 'Info');
		$this->addMessage('&sum; Ermittelte Hardware (Router) -Einträge ', '' . $countHardware . '', 'Sum');

		return true;

	}    // END private function getHardwareEntrysByCoProducts()










	// Ermittelt das MFG eines Routers
	private function getMFGBySD_ID($getSD_ID)
	{

		$return = array('MFG'    => '',
						'MFG_IP' => '');

		$query = "SELECT *
					FROM SERVICE_DATA
					WHERE SD_ID = '" . $getSD_ID . "'
					  AND COMPONENT_ID ='10003'";


		$result = ibase_query($this->dbF, $query);

		while ($row = @ibase_fetch_object($result)) {

			$return['MFG'] = $row->DESCRIPTION;
			$return['MFG_IP'] = $row->DATA_1;
		}

		ibase_free_result($result);

		return $return;

	}    // END private function getUnitAndParentIDBySD_ID($getSD_ID)










	// Ermittelt die Unit eines Routers
	private function getUnitAndParentIDBySD_ID($getSD_ID)
	{

		$return = array('UNIT'      => '',
						'PARENT_ID' => ''
		);

		$query = "SELECT *
					FROM SERVICE_DATA
					WHERE SD_ID = '" . $getSD_ID . "'
					  AND COMPONENT_ID ='10035'";


		$result = ibase_query($this->dbF, $query);

		while ($row = @ibase_fetch_object($result)) {

			$return['UNIT'] = $row->DESCRIPTION;
			$return['PARENT_ID'] = $row->PARENT_ID;
		}

		ibase_free_result($result);

		return $return;

	}    // END private function getUnitAndParentIDBySD_ID($getSD_ID)










	// Ermittelt den Port eines Routers
	private function getPortAndParentIDBySR_ID($getSR_ID)
	{

		$return = array('PORT'      => '',
						'PARENT_ID' => ''
		);

		$query = "SELECT sr.SR_ID	AS	SR_SR_ID,
								 sr.RP_ID	AS  SR_RP_ID,
								 sd.*
							FROM SERVICE_RESOURCE_CONTENTS sr
						LEFT JOIN SERVICE_DATA sd ON sd.SD_ID = sr.SD_ID
							WHERE sr.SR_ID = '" . $getSR_ID . "'
							AND sd.COMPONENT_ID ='10036'";

		$result = ibase_query($this->dbF, $query);

		while ($row = @ibase_fetch_object($result)) {

			$return['PORT'] = $row->DESCRIPTION;
			$return['PARENT_ID'] = $row->PARENT_ID;
		}

		ibase_free_result($result);

		return $return;

	}    // END private function getPortAndParentIDBySD_ID($getSD_ID)










	// Bereinige Produk-Liste z.B. von TAE - Adapter
	private function cleanProductVars()
	{

		// Wenn wir nicht zu bereinigen haben (für diesen Mandanten), können wir die Methode schon verlassen.
		if ((!isset($this->setDelProduct[$this->setMandant])) || (count($this->setDelProduct[$this->setMandant]) < 1))
			return true;

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			// Nicht löschen bei Kunden Nummer: 100037 (RheiNet)
			if ($curCustomerID == '100037')
				continue;

			// Durchlauf custProductSet des Kunden
			foreach($curCustObj->custProductSet as $curProduct_ID => $curProductPArray) {

				// Prüfen ob wir ein zu löschendes Produkt haben
				if (in_array($curProduct_ID, $this->setDelProduct[$this->setMandant])) {

					// TODO ACHTUNG hier lösche ich das TAE Modul!!!
					unset($curCustObj->custProductSet[$curProduct_ID]);
				}

			}    // END // Durchlauf custProductSet des Kunden


		}    // END // Duchlauf Customer - Handler

		return true;

	}    // END private function cleanModemToOneModem()










	// Bereinige Cisco Kabelmodem und Arris Kabelmodem zugunsten von Arris
	private function cleanResetProductVars()
	{

		// Wenn wir nicht zu bereinigen haben (für diesen Mandanten), können wir die Methode schon verlassen.
		if ((!isset($this->setClearProduct[$this->setMandant])) || (count($this->setClearProduct[$this->setMandant]) < 1))
			return true;

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Durchlauf custProductSet des Kunden
			foreach($curCustObj->custProductSet as $curProduct_ID => $curProductPArray) {

				// Prüfen ob wir ein zu löschendes Produkt haben
				if (array_key_exists($curProduct_ID, $this->setClearProduct[$this->setMandant])) {

					// Die zu erstzende bzw. die ProduktID ermitteln die ebenfalls vorhanden sein muss
					$needProductID = $this->setClearProduct[$this->setMandant][$curProduct_ID];

					if (isset($curCustObj->custProductSet[$needProductID])) {

						// TODO ACHTUNG hier lösche ich das Cisco-Modem!!!
						unset($curCustObj->custProductSet[$curProduct_ID]);
					}

				}

			}    // END // Durchlauf custProductSet des Kunden

		}    // END // Duchlauf Customer - Handler

		return true;

	}    // END private function cleanModemToOneModem()










	// Telefonbucheinträge ermitteln -> Build Telefonbucheintrag in custSubIDSet
	private function getPhoneBookEntrysByCustomerID()
	{

		// Counter Var
		$cntSumPhoneBookEntry = '0';

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			$cntVOIPPerCustomer = '0';

			// Durchlauf VOIP-Daten des Kunden
			foreach($curCustObj->custVOIPSet as $curCOV_ID => $curVOIPArray) {

				// Wenn Tel-Eintrag == J ... foreach custSubIDSet
				// Soll laut VOIP-Daten ein Telefonbucheintrag gesetzt werden?

				if (($curVOIPArray['TELEFONBUCHEINTRAG'] == 'J') && (isset($curCustObj->custVOIPSet[$curCOV_ID]['TELEFONBUCH_UMFANG']))) {    // LIVE
					// if ($curVOIPArray['TELEFONBUCHEINTRAG'] == 'N') {    // DEVELOP
					// Lauf VOIP J ... jetzt prüfen ob das bei den Sub-IDs (Telefonnummern auch der Fall ist:

					if (isset($curCustObj->custSubIDSet)) {

						$cntSubID = 0;

						// Durchlauf Subscriber SubID (Telefonnumern)
						foreach($curCustObj->custSubIDSet as $curSub_ID => $curSubArray) {

							$cntSubID++;

							// Wenn bei SubID auch der Telefonbucheintrag gesetzt werden soll... dann haben wir bei beiden Settings ja!
							if ($curSubArray['TELEFONBUCHEINTRAG'] == 'J') {    //  LIVE
								// if ($curSubArray['TELEFONBUCHEINTRAG'] == 'N') {    // DEVELOP
								// Ja, Diese Telefonnummer soll einen Telefonbucheintrag erhalten

								$phoneBookEntryType = $curCustObj->custVOIPSet[$curCOV_ID]['TELEFONBUCH_UMFANG'];    // LIVE
								// $phoneBookEntryType = 'V';    // DEVELOP

								$retArray = $this->getAddressPhoneBoockByCustomerIDAndTypeID($curCustomerID, $phoneBookEntryType);

								if (count($retArray) > 0) {

									$cntSumPhoneBookEntry++;
									$cntVOIPPerCustomer++;

									//$curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntVOIPPerCustomer;
									$curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntSubID;
									//$curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntVOIPPerCustomer;
									$curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntSubID;

									//echo "$curSub_ID -> $cntSubID -> $curVOIP_NATIONAL_VORWAHL<br>";

									$curCustObj->custSubIDSet[$curSub_ID]['TELEBUCH_TEL'] = $curCustObj->custSubIDSet[$curSub_ID][$curVOIP_NATIONAL_VORWAHL] . '/' . $curCustObj->custSubIDSet[$curSub_ID][$curVOIP_KOPFNUMMER];

									// Telefonbuch - Daten hinzufügen
									foreach($retArray as $keyName => $value) {
										$curCustObj->custSubIDSet[$curSub_ID][$keyName] = $value;
									}

								}
							}
							else {
								// Nein, Diese Telefonnummer soll keinen Telefonbucheintrag erhalten
								$curCustObj->custSubIDSet[$curSub_ID]['TELEFONBUCHEINTRAG'] = 'N';
							}

						}    // END // Durchlauf Subscriber SubID (Telefonnumern)

					}
					else
						$curVOIPArray['TELEFONBUCHEINTRAG'] = 'N';
				}

			}    // END // Durchlauf VOIP-Daten des Kunden

		}    // END // Duchlauf Customer - Handler

		$this->addMessage('&sum; Ermittelt Telefonbuch-Einträge ', '' . $cntSumPhoneBookEntry . '', 'Info');
		$this->addMessage('&sum; Ermittelt Telefonbuch-Einträge ', '' . $cntSumPhoneBookEntry . '', 'Sum');

		return true;

	}    // END private function getPhoneBookEntrysByCustomerID()










	// VOIP - Telefonnummern einlesen -> Build custSubIDSet (Telefonnumer)
	private function getSubscriberByCOVID()
	{

		// Counter Vars
		$cntSubIDData = '0';


		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Durchlauf VOIP-Daten des Kunden
			foreach($curCustObj->custVOIPSet as $curCOV_ID => $curVOIPArray) {

				$query = "SELECT * FROM SUBSCRIBER WHERE COV_ID = '" . $curCOV_ID . "' ORDER BY DISPLAY_POSITION";

				$result = ibase_query($this->dbF, $query);

				$cntInnerSubscriber = 0;
				while ($row = ibase_fetch_object($result)) {
					$cntSubIDData++;
					$cntInnerSubscriber++;

					$curCustObj->custSubIDSet[$row->SUBS_ID]['SUBS_ID'] = $row->SUBS_ID;
					$curCustObj->custSubIDSet[$row->SUBS_ID]['COV_ID'] = $curCOV_ID;

					$curCustObj->custSubIDSet[$row->SUBS_ID]['SUBSCRIBER_ID'] = $row->SUBSCRIBER_ID;    // (Telefonnummer)
					$curCustObj->custSubIDSet[$row->SUBS_ID]['VOIP_PORT_TERMIN'] = $this->getFormatDate($row->DATE_PORTI_REQ);

					// Carrier?
					if ($row->CARRIER_ID > 0) {
						$curCarrierCode = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'];
						$curCarrierID = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'];
						$curCarrierName = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'];

						$curCustObj->custSubIDSet[$row->SUBS_ID]['CARRIER_ID'] = $curCarrierID;
						$curCustObj->custSubIDSet[$row->SUBS_ID]['CARRIER_CODE'] = $curCarrierCode;
						$curCustObj->custSubIDSet[$row->SUBS_ID]['CARRIER_NAME'] = $curCarrierName;
					}


					// Telefonbuch-Eintrag für diese Nummer?
					if ($row->PHON_BOOK == '1')
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'J';
					else
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'N';


					// Telefonnummer inversuche sperren?
					if ($row->INVERS_SEARCH == '1')
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEBUCH_SPERRE_INVERS'] = 'N';
					else
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEBUCH_SPERRE_INVERS'] = 'J';


					// Elektr.Telefonbuch?
					if ($row->DIGITAL_MEDIA == '1')
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEBUCH_EINTRAG_ELEKT'] = 'J';
					else
						$curCustObj->custSubIDSet[$row->SUBS_ID]['TELEBUCH_EINTRAG_ELEKT'] = 'N';


					// SIP Authname
					$curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntInnerSubscriber;
					$curCustObj->custSubIDSet[$row->SUBS_ID][$curVOIP_ACCOUNT] = $row->SIP_AUTHNAME;


					// SIP Passwort
					$curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntInnerSubscriber;
					$curCustObj->custSubIDSet[$row->SUBS_ID][$curVOIP_ACCOUNT_PASSWORT] = $row->SIP_PASSWORD;


					// Vorwahl
					$curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntInnerSubscriber;
					$val = $this->getNatVorwahl($row->SUBSCRIBER_ID);
					$curCustObj->custSubIDSet[$row->SUBS_ID][$curVOIP_NATIONAL_VORWAHL] = $val;


					// Kopfnummer
					$curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntInnerSubscriber;
					$val = $this->getKopfnummer($row->SUBSCRIBER_ID);
					$curCustObj->custSubIDSet[$row->SUBS_ID][$curVOIP_KOPFNUMMER] = $val;

				}    // END while

				ibase_free_result($result);

			}    // END // Durchlauf VOIP-Daten des Kunden

		}    // END // Duchlauf Customer - Handler

		$this->addMessage('&sum; Ermittelt VOIP Nummern ', $cntSubIDData, 'Info');
		$this->addMessage('&sum; Ermittelt VOIP Nummern ', $cntSubIDData, 'Sum');

		return true;

	}    // END private function getSubscriberByCOVID()










	// VOIP Daten einlesen -> Build custVOIPSet
	private function getCOVoicedataByCOID()
	{

		// Filter Info
		// Nur Voice-Data dessen Status_ID > 0 ist?
		if ($this->setReadOnlyVOIPStatusAboveNull)
			$this->addMessage('Nur VOIP - Daten deren Status_ID > 0 (Null) ist', 'ja', 'Filter');


		// Counter Vars
		$cntVOIPData = '0';
		$cntNoVOIPData = '0';


		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Durchlauf Verträge des Kunden für bezogene Produkte einlesen
			foreach($curCustObj->custContractSet as $curContractID => $curContractArray) {

				// Nur Voice-Data dessen Status_ID > 0 ist?
				if ($this->setReadOnlyVOIPStatusAboveNull)
					$query = "SELECT * FROM CO_VOICEDATA WHERE CO_ID = '" . $curContractID . "' AND STATUS_ID > '0' ORDER BY COV_ID";
				else
					$query = "SELECT * FROM CO_VOICEDATA WHERE CO_ID = '" . $curContractID . "' ORDER BY COV_ID";

				$result = ibase_query($this->dbF, $query);

				$boolGotVOIP = false;
				while ($row = ibase_fetch_object($result)) {

					$boolGotVOIP = true;

					$cntVOIPData++;

					$curCustObj->custVOIPSet[$row->COV_ID]['COV_ID'] = $row->COV_ID;
					$curCustObj->custVOIPSet[$row->COV_ID]['CONTRACT_ID'] = $curContractID;


					// EGN_VERFREMDUNG?
					$val = ($row->IB_REDUCED == 1) ? 'J' : 'N';    // $val ... wenn $row->IB_REDUCED == 1 dann J sonst N
					$curCustObj->custVOIPSet[$row->COV_ID]['EGN_VERFREMDUNG'] = $val;


					// Telefonbucheintrag?

					$curCustObj->custVOIPSet[$row->COV_ID]['TELEFONBUCHEINTRAG'] = 'N';        // Default nein also N
					if ($row->PHONE_BOOK_ENTRY_ID > 0) {

						// ... Ja Telefonbucheintrag soll erstellt werden

						// Erkenne ich die Art / den Umfang? ... Wenn nein... dann Tel.Eintrag auf N belassen
						if (array_key_exists($row->PHONE_BOOK_ENTRY_ID, $this->setPhoneBookEntryIDToVal[$this->setMandant])) {

							// Marker Telefonbucheintrag mit J überschreiben
							$curCustObj->custVOIPSet[$row->COV_ID]['TELEFONBUCHEINTRAG'] = 'J';

							// Telefonbuch-Umfang festlegen:
							$curCustObj->custVOIPSet[$row->COV_ID]['TELEFONBUCH_UMFANG'] = $this->setPhoneBookEntryIDToVal[$this->setMandant][$row->PHONE_BOOK_ENTRY_ID];
						}
					}

					// Zusätzlich Informationen speichern
					$curCustObj->custVOIPSet[$row->COV_ID]['DATE_ACTIVE'] = $row->DATE_ACTIVE;
					$curCustObj->custVOIPSet[$row->COV_ID]['DATE_DEACTIVE'] = $row->DATE_DEACTIVE;

				}    // END while ...

				ibase_free_result($result);

				if (!$boolGotVOIP)
					$cntNoVOIPData++;

			}    // END // Durchlauf Verträge des Kunden für bezogene Produkte einlesen


		}    // END // Duchlauf Customer - Handler

		$this->addMessage('&sum; Ermittelt VOIP ', $cntVOIPData, 'Info');
		$this->addMessage('&sum; Ermittelt sonstige Dienste ', $cntNoVOIPData, 'Info');

		$this->addMessage('&sum; Ermittelt VOIP ', $cntVOIPData, 'Sum');
		$this->addMessage('&sum; Ermittelt sonstige Dienste ', $cntNoVOIPData, 'Sum');

		return true;

	}    // END private function getCOVoicedataByCOID()










	// CO_Products einlesen die zu den Contracts gehören -> Build: custProductSet
	private function getProductsByContractID()
	{

		// Counter Vars
		$cntProducts = '0';


		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];


			// Wenn gar kein Produkt zu gar keinem Vertrag vorhanden ist... dann lösche ich den Kunden aus der Export-Liste
			$boolGotSomeProductForCustomer = false;


			// Durchlauf Verträge des Kunden für bezogene Produkte einlesen
			foreach($curCustObj->custContractSet as $curContractID => $curContractArray) {

				// Wenn kein Produkt zu Vertag, dann lösche ich den Vertrag des Kunden
				$boolGotProductForContract = false;

				$add = " WHERE cop.CO_ID = '" . $curContractID . "' ";

//				$query = "SELECT cop.CO_ID          AS CO_ID,
//                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
//                                 cop.SR_ID			AS SR_ID,
//                                 p.DESCRIPTION      AS DESCRIPTION,
//                                 p.PRODUCT_ID       AS PRODUCT_ID,
//                                 p.PRODUCT_CODE		AS COS_ID,
//                                 p.INFO_TITLE1		AS PINFO_TITLE1,
//                                 p.INFO_TITLE2		AS PINFO_TITLE2,
//                                 cop.DATE_ACTIVE    AS COPDATE_ACTIVE,
//                                 cop.DATE_DEACTIVE  AS COPDATE_DEACTIVE,
//                                 a.ACCOUNTNO        AS ACCOUNTNO,
//                                 a.DESCRIPTION      AS ADESCRIPTION
//                            FROM CO_PRODUCTS cop
//                              LEFT JOIN PRODUCTS p  ON p.PRODUCT_ID  = cop.PRODUCT_ID
//                              LEFT JOIN ACCOUNTS a  ON a.ACCOUNTNO   = p.ACCOUNTNO
//                              " . $add . "
//                            ORDER BY cop.CO_PRODUCT_ID";

				$query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 cop.SR_ID			AS SR_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID,
                                 p.PRODUCT_CODE		AS COS_ID,
                                 p.INFO_TITLE1		AS PINFO_TITLE1,
                                 p.INFO_TITLE2		AS PINFO_TITLE2,
                                 cop.DATE_ACTIVE    AS COPDATE_ACTIVE,
                                 cop.DATE_DEACTIVE  AS COPDATE_DEACTIVE,
                                 a.ACCOUNTNO        AS ACCOUNTNO,
                                 a.DESCRIPTION      AS ADESCRIPTION,
                                 oi.INFO_TEXT1		AS INFO_TEXT1,
                                 oi.INFO_TEXT2		AS INFO_TEXT2
                            FROM CO_PRODUCTS cop
                              LEFT JOIN PRODUCTS p  ON p.PRODUCT_ID  = cop.PRODUCT_ID
                              LEFT JOIN ACCOUNTS a  ON a.ACCOUNTNO   = p.ACCOUNTNO
                              LEFT JOIN OBJECT_INFOS oi ON oi.OBJECT_ID = cop.CO_PRODUCT_ID
                              " . $add . "
                            ORDER BY cop.CO_PRODUCT_ID";

				$result = ibase_query($this->dbF, $query);

				while ($row = ibase_fetch_object($result)) {

					// Habe ein Produkt für einen Vertrag des Kunden... Flag setzen
					$boolGotSomeProductForCustomer = true;

					// Habe ein Produkt für diesen Vertrag des Kunden ... Flag setzen
					$boolGotProductForContract = true;

					$cntProducts++;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['CONTRACT_ID'] = $curContractID;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['PRODUCT_NAME'] = $row->DESCRIPTION;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['COPDATE_ACTIVE'] = $row->COPDATE_ACTIVE;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['DATE_DEACTIVE'] = $row->COPDATE_DEACTIVE;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['COS_ID'] = $row->COS_ID;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTNO'] = $row->ACCOUNTNO;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTDESC'] = $row->ADESCRIPTION;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['SR_ID'] = $row->SR_ID;
					// $curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTDESC'] = utf8_encode($row->ADESCRIPTION);

					$curCustObj->custProductSet[$row->PRODUCT_ID]['INFO_TITLE1'] = $row->PINFO_TITLE1;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['INFO_TITLE2'] = $row->PINFO_TITLE2;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['INFO_TEXT1'] = $row->INFO_TEXT1;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['INFO_TEXT2'] = $row->INFO_TEXT2;
				}

				ibase_free_result($result);


				// Wenn kein Produkt zu Vertag, dann lösche ich den Vertrag des Kunden
				if (!$boolGotProductForContract)
					unset($curCustObj->custContractSet[$curContractID]);


			}    // END // Durchlauf Verträge des Kunden


			// Wenn gar kein Produkt zu gar keinem Vertrag vorhanden ist... dann lösche ich den Kunden aus der Export-Liste
			if (!$boolGotSomeProductForCustomer)
				unset($this->custArray[$curCustomerID]);

		}    // END // Duchlauf Customer - Handler


		// Wieviel Kunden haben wir jetzt noch?
		$cntCustomerToExport = count($this->custArray);

		// Status:
		$this->addMessage('&sum; Ermittelte Produkte ', $cntProducts, 'Info');
		$this->addMessage('&sum; Ermittelte Produkte ', $cntProducts, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden ', $cntCustomerToExport, 'Sum');


		return true;

	}    // END private function getProductsByContractID()










	// Contracts einlesen die zu den ausgewählten Customer gehören -> Build: custContractSet
	private function getContractsByCustomerID()
	{

		// Init - Add für die Query durch Filter gegeben
		$addQueryFilter = '';

		// TODO Firebird Query für "nur unterzeichnete Verträge" nachreichen
		// Nur unterzeichnete Vertäge einlesen?
		// Query funtioniert so unter Firebird nicht... Code zunächst deaktiviert bzw. die Setting - Var nicht deklariert
		if ((isset($this->setReadOnlySignedContracts)) && ($this->setReadOnlySignedContracts == 'yes')) {
			$this->addMessage('Nur unterzeichnete Verträge einlesen', 'ja', 'Filter');

			// Add für die Query durch Filter gegeben
			$addQueryFilter = " AND DATE_SIGNED != '' ";
		}


		// Filter Info:
		// Nur Verträge einlesen dessen Status größer Null ist?
		if ($this->setReadOnlyContractStatusAboveNull)
			$this->addMessage('Nur Verträge deren Status_ID > 0 (Null) ist', 'ja', 'Filter');


		// Counter Vars
		$cntContracts = '0';
		$cntCustomerHasNoContract = '0';



		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Counter Vars pro Kunde
			$cntContractsPerCustomer = 0;

			// Aktuelle CustomerID ist?
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			// Nur Verträge einlesen dessen Status größer Null ist?
			if ($this->setReadOnlyContractStatusAboveNull)
				$query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '" . $curCustomerID . "' AND STATUS_ID > '0' " . $addQueryFilter . " ORDER BY CO_ID";
			else
				$query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '" . $curCustomerID . "'  " . $addQueryFilter . " ORDER BY CO_ID";


			$result = ibase_query($this->dbF, $query);

			// Gibt es gültige Verträge zu dem Customer?
			if ($this->ibase_num_rows($result) < 1) {
				$cntCustomerHasNoContract++;

				// Customer Handler/Objekt löschen und somit aus dem Kreis der zu exportierenden Kunden entfernen
				unset($this->custArray[$curCustomerID]);

				// Wenn ich wissen will welche Kunden genau keinen Vertrag haben... diese Zeile einkommentieren
				// $this->addMessage('Kein Vertag KdNr.', $curCustomerID, 'Warning');

				continue;
			}


			ibase_free_result($result);

			$result = ibase_query($this->dbF, $query);

			while ($row = ibase_fetch_object($result)) {
				$cntContracts++;
				$cntContractsPerCustomer++;

				// Coding Export - Daten für Verträge
				$curCustObj->custContractSet[$row->CO_ID]['CONTRACT_ID'] = $row->CO_ID;
				$curCustObj->custContractSet[$row->CO_ID]['CUSTOMER_ID'] = $row->CUSTOMER_ID;
				$curCustObj->custContractSet[$row->CO_ID]['CONTR_STATUS_ID'] = $row->STATUS_ID;
				$curCustObj->custContractSet[$row->CO_ID]['CONTR_DATE_ACTIVE_REQ'] = $this->getFormatDate($row->DATE_ACTIVE_REQ);

				// Weil ein Kunde mehrere Verträge habe kann, muss ich die (eigentlichen) Basis-Daten pro Vertrag festhalten
				$curCustObj->custContractSet[$row->CO_ID]['GUELTIG_VON'] = $this->getFormatDate($row->DATE_ACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['INSTALLATIONSTERMIN'] = $this->getFormatDate($row->DATE_ACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['GUELTIG_BIS'] = $this->getFormatDate($row->DATE_DEACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['ERFASST_AM'] = $this->getFormatDate($row->DATE_CREATED);
				$curCustObj->custContractSet[$row->CO_ID]['UNTERZEICHNET_AM'] = $this->getFormatDate($row->DATE_SIGNED);

				$curCustObj->custContractSet[$row->CO_ID]['WIDERRUFEN_AM'] = '';
				$curCustObj->custContractSet[$row->CO_ID]['GEKUENDIGT_AM'] = '';
			}

			ibase_free_result($result);

		}    // END foreach ... Durchlauf Customer - Handler


		// Wieviel Kunden haben wir jetzt noch?
		$cntCustomerToExport = count($this->custArray);

		// Status:
		$this->outNow('&sum; Ermittelte Verträge', $cntContracts, 'Info');
		$this->addMessage('&sum; Ermittelte Verträge', $cntContracts, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Sum');

		// Summenausgabe bei Alert
		if ($cntCustomerHasNoContract > 0)
			$this->addMessage('Kein Vertag für Kunde x mal', $cntCustomerHasNoContract, 'Warning');

		return true;

	}    // END private function getContractsByCustomerID()










	// Customer einlesen die in der angegebenen GruppenID enthalten sind -> Build: $this->custArrays
	private function getCustomerByGroupID()
	{

		$add = '';

		// Nur Customer einlesen die in der Gruppe x sind?
		if ((isset($this->setCustomerByGroupID[$this->setMandant])) && (count($this->setCustomerByGroupID[$this->setMandant]) > 0)) {
			$bool = false;
			$addGroupID = '';
			foreach($this->setCustomerByGroupID[$this->setMandant] as $curGroupID) {

				if (!$bool)
					$add .= " WHERE (GROUP_ID = '" . $curGroupID . "'";
				else
					$add .= " OR GROUP_ID = '" . $curGroupID . "'";

				if ($bool)
					$addGroupID .= '<br>';

				$addGroupID .= $curGroupID;

				$bool = true;

			}
			$add .= ') ';

			$this->addMessage('Nur Customer mit Gruppen_ID', $addGroupID, 'Filter');

		}
		else
			$add .= " WHERE CUSTOMER_ID > '1' ";



		// Nur Customer einlesen die NICHT Status x haben?
		if ((isset($this->setNoCustomerInStatusID[$this->setMandant])) && (count($this->setNoCustomerInStatusID[$this->setMandant]) > 0)) {
			$bool = false;
			$addStatusID = '';
			foreach($this->setNoCustomerInStatusID[$this->setMandant] as $curStatusID) {

				if (!$bool)
					$add .= " AND (STATUS_ID != '" . $curStatusID . "'";
				else
					$add .= " AND STATUS_ID != '" . $curStatusID . "'";

				if ($bool)
					$addStatusID .= '<br>';

				$addStatusID .= $curStatusID;

				$bool = true;
			}
			$add .= ')';

			$this->addMessage('Nicht Customer mit Status_ID', $addStatusID, 'Filter');
		}



		// Nur Customer einlesen die Kundennummer x besitzen?
		if ((isset($this->setOnlyExampleCustomerID)) && ($this->setOnlyExampleCustomerID > 0)) {

			$this->addMessage('Nur Kunden-Nr.:', $this->setOnlyExampleCustomerID, 'Filter');

			$add .= " AND CUSTOMER_ID = '" . $this->setOnlyExampleCustomerID . "' ";

		}


		// Folgende Customer nicht einlesen:
		// setDoNotReadThisCustomerIDs
		if ((isset($this->setDoNotReadThisCustomerIDs)) && (count($this->setDoNotReadThisCustomerIDs) > 0)) {
			$bool = false;
			$addCustomerID = '';
			foreach($this->setDoNotReadThisCustomerIDs as $curNotCustomerID) {

				if (!$bool)
					$add .= " AND (CUSTOMER_ID != '" . $curNotCustomerID . "'";
				else
					$add .= " AND CUSTOMER_ID != '" . $curNotCustomerID . "'";

				if ($bool)
					$addCustomerID .= '<br>';

				$addCustomerID .= $curNotCustomerID;

				$bool = true;
			}
			$add .= ')';


			$this->addMessage('Nicht Customer mit Customer_ID', $addCustomerID, 'Filter');

		}


		// Limitierung gesetzt?
		if ((isset($this->setReadLimitCustomer)) && ($this->setReadLimitCustomer > 0)) {

			$addFirst = 'FIRST ' . $this->setReadLimitCustomer . ' SKIP 0 ';
			$this->addMessage('Ermittel maximal Xn Kunden', $this->setReadLimitCustomer, 'Filter');

		}
		else
			$addFirst = '';



		$query = "SELECT " . $addFirst . " * FROM CUSTOMER " . $add . " ORDER BY CUSTOMER_ID";


		$result = ibase_query($this->dbF, $query);


		$cntCustomer = 0;
		while ($row = ibase_fetch_object($result)) {

			$cntCustomer++;

			// $this->globalData['CUSTOMER_ID_Array'][$row->CUSTOMER_ID]['CUSTOMER_ID'] = $row->CUSTOMER_ID;


			// Neuen Kunden - Datensatz - Handler erzeugen
			$hCust[$row->CUSTOMER_ID] = new Customer();

			// Definiere KUNDEN_NR zu
			$hCust[$row->CUSTOMER_ID]->custExpSet['KUNDEN_NR'] = $row->CUSTOMER_ID;

			// Speichere Kunden - Datensatz - Handler in globaler Klassen - Variable
			$this->custArray[$row->CUSTOMER_ID] = $hCust[$row->CUSTOMER_ID];

		}


		ibase_free_result($result);

		// Status:
		$this->outNow('&sum; Ermittelte Kunden', $cntCustomer, 'Info');
		$this->addMessage('&sum; Ermittelte Kunden', $cntCustomer, 'Sum');


		return true;

	}   // END private function getCustomerByGroupID()










	// Ermittelt die Adressdaten für das Telefonbuch
	private function getAddressPhoneBoockByCustomerIDAndTypeID($getCustomerID, $phoneBookEntryType)
	{

		// Telephonbuch ID Referenz
		// Bei TKRZ: 0 = Kein Eintrag
		//			10001 Standardeintrag
		// 			10002 nur Name und Rufnummer / ohne Adresse
		// 			10003 Wissing Heike und Ansgar
		//			'10001' => 'A',
		//	        '10002' => 'V',
		//		 	'10003' => 'N',

		$retArray = array();

		// ID 10011 == Expliziet gewünschter Telefonbucheintrag
		$query = "SELECT * FROM CUSTOMER_ADDRESSES WHERE CUSTOMER_ID = '" . $getCustomerID . "' AND (ADDRESS_TYPE_ID = '10010' OR ADDRESS_TYPE_ID = '10011') ORDER BY ADDRESS_TYPE_ID";
		$result = ibase_query($this->dbF, $query);

		$cnt = 0;
		while ($row = ibase_fetch_object($result)) {
			$cnt++;

			// Nur wenn alle Daten gewünscht sind diese auch setzen
			if ($phoneBookEntryType == 'A') {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
				$retArray['TELEBUCH_STRASSE'] = $row->STREET . ' ' . $row->HOUSENO . ' ' . $row->HOUSENO_SUPPL;
				$retArray['TELEBUCH_PLZ'] = $row->CITYCODE;
				$retArray['TELEBUCH_ORT'] = $row->CITY;
				$retArray['TELEBUCH_FAX'] = $row->FAX;
			}
			elseif ($phoneBookEntryType == 'V') {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
			}
			elseif ($phoneBookEntryType == 'N') {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
			}
			else {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
			}

		}

		ibase_free_result($result);

		return $retArray;
	}










	// Carrier Referenz einlesen
	public function getCarrierRef()
	{

		// Counter Var
		$cntCarrier = '0';

		$query = 'SELECT * FROM CARRIER ORDER BY CARRIER_ID';

		$result = ibase_query($this->dbF, $query);

		while ($row = ibase_fetch_object($result)) {

			$cntCarrier++;

			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'] = $row->CARRIER_ID;
			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'] = $row->NAME;
			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'] = $row->CARRIER_CODE;
		}

		ibase_free_result($result);

		$this->addMessage('&sum; Ermittelte Carrier ', $cntCarrier, 'Sum');

		return true;

	}   // END private function getCarrierRef()










	// Datum passend formatieren
	public function getFormatDate($getDate = null)
	{

		if (strlen($getDate) > 0)
			$getDate = date("d.m.Y ", strToTime($getDate));

		return $getDate;

	}   // END private function getFormatDate(...)










	// Vorwahl extrahieren
	public function getNatVorwahl($arg = 0)
	{

		$val = 0;
		$pattern = $arg;
		$search = '/(.*\d+)+( )(\d+)( )(.\d+)/';
		$matches[1] = '';
		$matches[3] = '';

		preg_match($search, $pattern, $matches);
		if ((isset($matches[3])) && (strlen($matches[3] > 0)))
			$val = '0' . $matches[3];

		return trim($val);

	}   // END private function getNatVorwahl(...)










	// Vorwahl extrahieren
	public function getKopfnummer($arg = 0)
	{

		$val = 0;
		$pattern = $arg;
		$search = '/(.*\d+)+( )(\d+)( )(.\d+)/';
		$matches[5] = '';

		preg_match($search, $pattern, $matches);

		if ((isset($matches[5])) && (strlen($matches[5] > 0)))
			$val = $matches[5];

		return trim($val);

	}   // END private function getKopfnummer(...)










	// Datenbankverbindung zum Dimari-System aufbauen
	public function createDimariDBConnection()
	{

		// Muss neue DB - Verbindung hergestellt werden?
		if (!($dbF = ibase_pconnect($this->myHost, $this->myUsername, $this->myPassword, 'ISO8859_1', 0, 3)))
			die('Could not connect: ' . ibase_errmsg());

		$this->dbF = $dbF;

		// Status
		// $this->outNow('DB Verbindung!', 'OK', 'Info');

		return true;

	}   // END function createDimariDBConnection()










	// Ibase num_rows
	public function ibase_num_rows($result)
	{

		$myResult = $result;

		$cnt = 0;

		while ($row = @ibase_fetch_row($myResult))
			$cnt++;

		return $cnt;

	}   // END private function ibase_num_rows(...)


}   // END class CollectData