<?php



/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 15.03.2016
 * Time: 08:33
 */
class Dimari
{

	// Initial Variable:

	// Customer einlesen die in der Customer Gruppe ... sind
	// Tabelle: CUSTOMER_GROUP
	// Feld:    GROUP_ID
	public $setCustomerByGroupID = array('FTTC' => array('100011'),
										 'FTTH' => array()
	);


	// Customer einlesen die NICHT StatusID x haben
	// Tabelle: CUSTOMER_STATUS
	// Feld:    STATUS_ID
	// Nicht gekündigt '10004' ... nicht archiv '2'
	public $setNoCustomerInStatusID = array('FTTC' => array('10004', '2'),
											'FTTH' => array('10004', '2')
	);


	// Nummern für Produkte die für Internet wichtig sind ... u.a. nur diese Daten werden gezogen
	public $setProductIDForInternet = array('FTTC' => array('10070', '10059',),
											'FTTH' => array()
	);

	// Nummern für Produkte die für VOIP wichtig sind ... u.a. nur diese Daten werden gezogen
	public $setProductIDForVOIP = array('FTTC' => array('10033', '10004',),
										'FTTH' => array()
	);

	// Ext Produkt ID der Dienste
	public $setExtProdServiceID = array('setProductIDForVOIP'     => '771',
										'setProductIDForInternet' => '770'
	);


	// Telefonbuch IDs die gesetzt werden können
	public $setPhoneBookIDs = array('10002', '10001');


	// Message Handler
	public $myMessage = array('Info'    => array(),
							  'Runtime' => array()
	);


	// Globaler HAUPT - Handler für Datenverarbeitung
	public $globalData = array();
	public $globalDataTMP = array();
	public $globalCarrierData = array();
	public $globalLastFilename = ''; // Erzeugte Datei

	// Export Typ wird hier gespeichert (FTTC oder FTTH)
	// !!! Muss ausserhalb der Klasse gesetzt werden
	public $setExportType;


	// Datenbank Variable ... werden durch den Construktor gesetzt
	private $myHost;
	private $myUsername;
	private $myPassword;
	private $hostRadi;
	private $usernameRadi;
	private $passwordRadi;

	// Datenbank Object
	private $dbF;


	// Kunden ohne Vertrag aber aktiv (Details) ausgeben?
	public $setFTTHtoFTTCWrongCustomerOnScreen = 'no';


	// Falsch zugewiesene FTTH zu FTTC Kunden (Details) ausgeben?
	public $setNoContractCustomerOnScreen = 'no';


	// Kunden die keine VOIP Daten haben... sie aber haben müssten ... Purtel-Kunden möglich!!!
	public $setNoVOIPDataOnScreen = 'no';


	// Wie viele Customer sollen eingelesen werden?
	// 0 für keine Einschränkung beim Limit
	public $setReadLimitCustomer = 0;


	// PurTel Kunde
	//private $onlyExampleCustomerID = '20010686';

	// Tester
	//private $onlyExampleCustomerID = '20010603';



	//private $onlyExampleCustomerID = '20010120';        // Matthias Brumm(!) ... Chaos

	//private $onlyExampleCustomerID = '20010003';        // FTTC Gruppe ist aber FTTH!!!
	//private $onlyExampleCustomerID = '20010686';        // Kein VOIP Username - Passwort ... Vertrag nicht unterschrieben!

	//private $onlyExampleCustomerID = '20010190';        // Vertrag in Zukunft ... nicht unterzeichnet(!) bzw. Portierung bestätigt 4 VOIP

	// private $onlyExampleCustomerID = '20010003';       // Standard Kunde 1 VOIP
	//private $onlyExampleCustomerID = '20010612';        // Standard Kunde 2 VOIP
	//private $onlyExampleCustomerID = '20010603';        // Standard Kunde 3 VOIP ... mit Telefonbuch - Eintrag in der Sub-Auswahl



	// Klassen - Konstruktor
	public function __construct($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi)
	{

		$this->myHost = $host;
		$this->myUsername = $username;
		$this->myPassword = $password;

		$this->hostRadi = $hostRadi;
		$this->usernameRadi = $usernameRadi;
		$this->passwordRadi = $passwordRadi;

	}   // END public function __construct(...)






	// Default Name - Methode
	public function myName($out = false)
	{

		if ($out)
			print (__CLASS__);

		return __CLASS__;
	}
	// END public function myName(...)



	/**
	 * Initial - Methode ... FTTC - Dienste an Konzeptum
	 *
	 * In dieser Methode steuere ich den weiteren Programmalauf
	 * Alle weiteren Methoden werden hier gestartet
	 */
	public function initialGetFTTCServices()
	{

		$this->outNow('Start', '', 'Info');

		// Export Typ FTTC oder FTTH gesetzt?
		if (!$this->setExportType) {
			print ('FEHLER: "$setExportType" muss auf FTTC oder FTTH gesetzt werden!<br>Programm stop!<br>');

			return false;
		}

		$this->outNow('Gewählter Daten-Typ:', 'FTTC', 'Info');


		flush();
		ob_flush();



		// Dimari DB Verbindung herstellen
		$this->flushByFunctionCall('createDimariDBConnection');


		// Customer einlesen die in der angegebenen GruppenID enthalten sind
		$this->flushByFunctionCall('getCustomerByGroupID');


		// Contracts einlesen die zu den ausgewählten Customer gehören
		$this->flushByFunctionCall('getContractsByCustomerID');


		// CO_Products einlesen die zu den Contracts gehören
		$this->flushByFunctionCall('getProductsByContractID');


		// VOIP Daten einlesen
		$this->flushByFunctionCall('getCOVoicedataByCOID');


		// Carrier Referenz einlesen
		$this->flushByFunctionCall('getCarrierRef');


		// VOIP - Telefonnummern einlesen
		$this->flushByFunctionCall('getSubscriberByCOVID');


		// Telefonbucheinträge ermitteln
		$this->flushByFunctionCall('getPhoneBookEntrysByCustomerID');



		// Daten erste Aufbereitung
		$this->flushByFunctionCall('initialDimariExport');



		// Customer Vorname - Nachname einlesen
		$this->flushByFunctionCall('getNamesFromCustomer');


		// Daten aus Radius Server holen
		$this->flushByFunctionCall('getDataFromRadiusServer');


		// Telefon-Daten aus Excelliste holen
		$this->flushByFunctionCall('getDataFromExcelPhone');


		// Transaction-Daten aus Excelliste holen
		$this->flushByFunctionCall('getDataFromExcelTransaction');



		// Letze Vorbereitung der Daten
		$this->flushByFunctionCall('preWriteToFiel');


		// Schreibe Daten in Excel-Datei
		$this->flushByFunctionCall('writeToExcel');


		print ('<br>Erzeugte Datei: <a href="uploads/exports/' . $this->globalLastFilename . '.csv">' . $this->globalLastFilename . '</a>');

//		echo "<pre>";
//		print_r($this->globalData);
//		echo "</pre><br>";


		$this->outNow('Ende', '', 'Info');

		return true;

	} // END public function initialGetFTTCServices()






	// Transaction-Daten aus Excelliste holen
	private function getDataFromExcelTransaction()
	{

		$myData = array();

		// Lese .csv - Datei ein
		$filepath = 'uploads/ParseMeTransaction.csv';
		$myData = $this->readDataFromExcelPhone($filepath);

//        echo "<pre>";
//        print_r($myData);
//        echo "</pre><br>";



		// Durchlauf Customer
		foreach($this->globalData as $preCurCustomerID => $mainCustomerArray) {


			foreach($mainCustomerArray['CUSTOMER_ID'] as $curCustomerID => $customerArray) {


				// Puretel Kunde?
				if ((isset($customerArray['VOIP_ACCOUNT_1_FROM_PURETEL'])) && ($customerArray['VOIP_ACCOUNT_1_FROM_PURETEL'] == 'yes')) {


					if (isset($customerArray['VOIP_ACCOUNT_1'])) {

						$curMatcher = $customerArray['VOIP_ACCOUNT_1'];

						// Durchlauf csv-Daten
						foreach($myData as $index => $valArray) {

							if ($curMatcher == $valArray[0]) {

								// VOIP_TRANSACTION_NO_1
								$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_TRANSACTION_NO_1'] = $valArray[1];

								// ??? break;
							}

						}

					}
				}

				// $this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_ACCOUNT_1_FROM_PURETEL']

			}

		}

		return true;

	}   // END // Transaction-Daten aus Excelliste holen






	// Telefon-Daten aus Excelliste holen
	private function getDataFromExcelPhone()
	{

		$myData = array();

		// Lese .csv - Datei ein
		$filepath = 'uploads/ParseMePurtel.csv';
		$myData = $this->readDataFromExcelPhone($filepath);


		// Durchlauf der Zeilen
		foreach($myData as $index => $dataSetArray) {

			$tryCustomerID = trim($dataSetArray[0]);

			$search = '/^(\d)+$/';

			// Haben wir eine mögliche Kundennummer?
			if (preg_match($search, $tryCustomerID)) {

				// Ja hier ist ein Purtel - Kunde

				$curCustomerID = trim($dataSetArray[0]);


				// Setzte TELEFONBUCHEINTRAG ... auf N bei Purtel Kunden
				if (!isset($this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG']))
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG'] = 'N';


				// VOIP_PORTIERUNG updaten?
				if (strlen($dataSetArray[14]) > 0) {
					if (isset($this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'])) {

						if ($this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] == 'J') {
							// Nichts machen haben schon Flag durch die Dimari DB!
						} else {

							// Setze aktuellen und richtigen Status
							$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'N';
						}
					} else {
						// DEFAULT
						// Lass ich offen... unklar was dann gesetzt werden müsste
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'N';
					}
				}   // END // VOIP_PORTIERUNG updaten?



				// VOIP_ACCOUNT_1
				if (strlen($dataSetArray[15]) > 0) {

					$curVOIP_ACCOUNT_1 = trim($dataSetArray[15]);

					// Setze VOIP_ACCOUNT_1
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_ACCOUNT_1'] = $curVOIP_ACCOUNT_1;

					// Flagge mir den Eintrag als Puretel - Kunde
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_ACCOUNT_1_FROM_PURETEL'] = 'yes';

					// Hardcode "VOIP_EXT_PRODUKT_ID"  -> 771
					// VOIP_EXT_PRODUKT_ID
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_EXT_PRODUKT_ID'] = '771';

					// Hardcode "VOIP_DIENST_BEZEICHNUNG" -> TKRZ Telefonie
					// VOIP_DIENST_BEZEICHNUNG
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEZEICHNUNG'] = 'TKRZ Telefonie';

					// Hardcode "VOIP_DIENST_BEMERKUNGB" -> Bei Purtel
					// VOIP_DIENST_BEMERKUNG
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEMERKUNG'] = ' ';

				}   // END // HAUPTVERTEILER



				// VOIP Kopfnummer und Vorwahl & PURTEL_KUNDENNUMMER
				if (strlen($dataSetArray[16]) > 0) {

					$tmpFullNumber = trim($dataSetArray[16]);

					// Gültige Telefonnumer?
					$search = '/^(\d)+$/';

					// Haben wir eine mögliche Kundennummer?
					if (preg_match($search, $tmpFullNumber)) {

						// Erste 5 = Vowahl
						// Rest = Kopfnummer

						$curVOIP_NATIONAL_VORWAHL_1 = substr($tmpFullNumber, 0, 4);
						$tmp = $curVOIP_NATIONAL_VORWAHL_1;
						$curVOIP_NATIONAL_VORWAHL_1 = '0' . $tmp;
						$curVOIP_KOPFNUMMER_1 = substr($tmpFullNumber, 4);

						// echo "$curCustomerID ... $tmpFullNumber => $curVOIP_NATIONAL_VORWAHL_1 => $curVOIP_KOPFNUMMER_1<br>";

						// Setze VOIP_KOPFNUMMER_1
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_KOPFNUMMER_1'] = $curVOIP_KOPFNUMMER_1;

						// Setze VOIP_NATIONAL_VORWAHL_1
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['VOIP_NATIONAL_VORWAHL_1'] = $curVOIP_NATIONAL_VORWAHL_1;
					}

					// Setze PURTEL_KUNDENNUMMER
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['PURTEL_KUNDENNUMMER'] = $curCustomerID;

				}   // END VOIP Kopfnummer und Vorwahl



				// HAUPTVERTEILER
				if (strlen($dataSetArray[9]) > 0) {

					$curHAUPTVERTEILER = trim($dataSetArray[9]);

					// Setze HAUPTVERTEILER
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['HAUPTVERTEILER'] = $curHAUPTVERTEILER;

				}   // END // HAUPTVERTEILER



				// KABELVERZWEIGER
				if (strlen($dataSetArray[7]) > 0) {

					$curKABELVERZWEIGER = trim($dataSetArray[7]);

					// Setze KABELVERZWEIGER
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['KABELVERZWEIGER'] = $curKABELVERZWEIGER;

				}   // END // KABELVERZWEIGER



				// DOPPELADER_1
				if (strlen($dataSetArray[10]) > 0) {

					$curDOPPELADER_1 = trim($dataSetArray[10]);

					// Setze DOPPELADER_1
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DOPPELADER_1'] = $curDOPPELADER_1;

				}   // END // DOPPELADER_1



				// DSLAM_PORT
				if (strlen($dataSetArray[11]) > 0) {

					$curDSLAM_PORT = trim($dataSetArray[11]);

					// Setze DSLAM_PORT
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DSLAM_PORT'] = $curDSLAM_PORT;

				}   // END // DSLAM_PORT



				// ROUTER_SERIEN_NR & ROUTER_MODELL
				if (strlen($dataSetArray[21]) > 0) {

					$curROUTER_SERIEN_NR = trim($dataSetArray[21]);

					// Setze ROUTER_SERIEN_NR
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['ROUTER_SERIEN_NR'] = $curROUTER_SERIEN_NR;


					// Hardcode Fritz Box 7xxx
					$curROUTER_MODELL = 'Fritz Box 7xxx';

					// Setze ROUTER_MODELL
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['ROUTER_MODELL'] = $curROUTER_MODELL;

				}   // END // ROUTER_SERIEN_NR & ROUTER_MODELL


				// PURTEL_HAUPTANSCHLUSS & PURTEL_KUNDENNUMMER
				if (strlen($dataSetArray[15]) > 0) {

					$curPURTEL_HAUPTANSCHLUSS = trim($dataSetArray[15]);

					// Setze PURTEL_HAUPTANSCHLUSS
					$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['PURTEL_HAUPTANSCHLUSS'] = $curPURTEL_HAUPTANSCHLUSS;

				}   // END // PURTEL_HAUPTANSCHLUSS & PURTEL_KUNDENNUMMER



			}   // END // Haben wir eine mögliche Kundennummer?

		}   // END // Durchlauf der Zeilen


//
//        echo "<pre>";
//        print_r($myData);
//        echo "</pre><br>";

		return true;

	}   // END private function getDataFromExcelPhone()






	// Lese Excelfile und gebe die Daten lesbar zurück
	private function readDataFromExcelPhone($filepath)
	{
		$myData = array();
		$myNewData = array();
		$preData = file($filepath);

		foreach($preData as $newLine) {
			$myNewData[][0] = $newLine;
		}
		$Data = $myNewData;

		foreach($Data as $index => $row) {

			$eachValueArray = str_getcsv($row[0], ";");

			$myData[$index] = $eachValueArray;
		}

		return $myData;

	}   // END private function readDataFromExcelPhone()






	// Daten vom Radius-Server lesen
	private function getDataFromRadiusServer()
	{


		$DBObject = new mysqli('p:' . $this->hostRadi, $this->usernameRadi, $this->passwordRadi, 'radius');


		// DB Verbindung fehlgeschlagen?
		if ($DBObject->connect_errno) {
			echo "Failed to connect to MySQL: (" . $DBObject->connect_errno . ") " . $DBObject->connect_error;

			exit;
		}

		$cntNoRadData = 0;

		// Durchlauf Main ... CustomerID
		foreach($this->globalData as $mainCustomerID => $customerArrayMain) {


			// Durchlauf CustomerID
			foreach($customerArrayMain['CUSTOMER_ID'] as $curCustomerID => $customerArray) {


				// DATEN EBENE

				// Radius Username vorhanden? ... Dann Daten aus Radius lesen
				if ((isset($customerArray['RADIUS_PRE_USERNAME'])) && (strlen($customerArray['RADIUS_PRE_USERNAME']) > 0)) {

					$query = "SELECT	ui.id		AS UserInfoID,
 										ui.username AS UserInfoUsername,
 										ui.lastname AS UserInfoLastname,
 										rc.id		AS RadcheckID,
 										rc.value	AS RadcheckValue
 								  FROM userinfo ui
 								   LEFT JOIN radcheck rc ON rc.id = ui.id
 								   WHERE ui.lastname = '".$curCustomerID."' LIMIT 1";

					$result = $DBObject->query($query);

					$num_rows = $result->num_rows;
					if ($num_rows == 1) {

						$row = $result->fetch_object();

						$curDaten_USERNAME = $row->UserInfoUsername;
						$curDATEN_USERPASSWORT = $row->RadcheckValue;
						$curUSERINFO_ID = $row->UserInfoID;

						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DATEN_USERNAME'] = $curDaten_USERNAME;
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DATEN_USERPASSWORT'] = $curDATEN_USERPASSWORT;
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['USERINFO_ID'] = $curUSERINFO_ID;

					} else {
						$cntNoRadData++;

						// echo $cntNoRadData . " Habe user nicht im Radiusserver gefunden: KDNr. " . $curCustomerID . " Authname " . $customerArray['RADIUS_PRE_USERNAME']."<br>";
						// Setze Flag auf ... noch nicht angelegt
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DATEN_USERNAME'] = '';
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['DATEN_USERPASSWORT'] = '';
						$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['USERINFO_ID'] = '-1';
					}

					mysqli_free_result($result);

				}


			}   // END   // Durchlauf CustomerID


		}   // END // Durchlauf Main ... CustomerID


		return true;

	}   // END private function getDataFromRadiusServer()






	// Customer Vorname - Nachname einlesen
	private function getNamesFromCustomer()
	{

		$query = "SELECT  * FROM CUSTOMER_ADDRESSES WHERE CUSTOMER_ID > '0' AND ADDRESS_TYPE_ID = '10010' ORDER BY ADDRESS_ID";
		$result = ibase_query($this->dbF, $query);

		$cnt = 0;
		while ($row = ibase_fetch_object($result)) {
			$cnt++;
			$curCustomerID = $row->CUSTOMER_ID;
			$curName = $row->NAME;
			$curFirstname = $row->FIRSTNAME;

			//echo $curCustomerID . "<br>";


			if (array_key_exists($curCustomerID, $this->globalData)) {

				// Daten hinzufügen:
				$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['RADIUS_PRE_FIRSTNAME'] 	= $row->FIRSTNAME;
				$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['RADIUS_PRE_SURNAME'] 		= $row->NAME;
				$radiusUsername = $this->getRadiusUsernameByFirstSurname($row->FIRSTNAME, $row->NAME);
				$this->globalData[$curCustomerID]['CUSTOMER_ID'][$curCustomerID]['RADIUS_PRE_USERNAME'] 	= $radiusUsername;
			}
		}

		ibase_free_result($result);

		return true;

	}   // END private function getNamesFromCustomer()






	// Liefert den potentiellen Usernamen für den Radiusserver aus Vor-Nachname
	private function getRadiusUsernameByFirstSurname($getFirstname, $getSurname)
	{

		$getFirstname = utf8_encode($getFirstname);
		$getSurname = utf8_encode($getSurname);

		// Namen zunächst formatieren und Sonderzeichen entfernen
		$getFirstname = $this->getRadiusUserFormated($getFirstname);
		$getSurname = $this->getRadiusUserFormated($getSurname);


		// Brauche nur das erste Zeichen vom Vornamen
		$oneLetterFirstname = substr($getFirstname, 0, 1);

		// Zeichenketten zusammensetzen
		$retAuthname = $oneLetterFirstname . $getSurname;

		return $retAuthname;

	}   // END private function getRadiusUsernameByFirstSurname($getFirstname, $getSurname)






	// Formatiert Vor-Nachnamen nach Radius - Standard
	private function getRadiusUserFormated($getName)
	{

		// Trim
		$getName = trim($getName);

		// Daten to lower
		$getName = strtolower($getName);

		// Sonderzeichen erstetzen ö ä ü ß `
		$getName = $this->cleanSpecialChar($getName);

		return $getName;

	}   // END private function getRadiusUserFormated(...)






	// Ersetzt Sonderzeichen
	private function cleanSpecialChar($getVal)
	{

		$getVal = str_replace("Ä", "Ae", $getVal);
		$getVal = str_replace("ä", "ae", $getVal);
		$getVal = str_replace("Ü", "Ue", $getVal);
		$getVal = str_replace("ü", "ue", $getVal);
		$getVal = str_replace("Ö", "Oe", $getVal);
		$getVal = str_replace("ö", "oe", $getVal);
		$getVal = str_replace("ß", "ss", $getVal);
		$getVal = str_replace("´", "", $getVal);
		$getVal = str_replace("-", "", $getVal);

		return $getVal;

	} // END private function cleanSpecialChar($getVal)






	// Telefonbucheinträge ermitteln
	private function getPhoneBookEntrysByCustomerID()
	{

		$cntSumPhoneNumbers = 0;
		$cntSumPhoneBookEntry = 0;

		// Durchlauf Customer_ID
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $curCustomerArray) {


			// Durchlauf Vertrag
			foreach($curCustomerArray['CONTRACT_ID'] as $curContractID => $curContractArray) {


				// DATEN EBENE


				// Haben wir grün aus Co_Voicedata?
				$boolA = false;
				if ((isset($curContractArray['PhoneBookFlagFromCO_VOICEDATA'])) && ($curContractArray['PhoneBookFlagFromCO_VOICEDATA'] == 'yes'))
					$boolA = true;


				// Haben wir grün aus Subscriber?
				$boolB = false;
				if ((isset($curContractArray['PhoneBookFlagFromSubscriber'])) && ($curContractArray['PhoneBookFlagFromSubscriber'] == 'yes'))
					$boolB = true;

				if ($boolA && $boolA) {


					// Durchlauf Produkte
					foreach($curContractArray['PRODUCT_ID'] as $curProductID => $curProductArray) {

						// Telefonbuch - Typ gesetzt?
						if (isset($curProductArray['TELEFONBUCHEINTRAG']))
							$phoneBookEntryType = $curProductArray['TELEFONBUCHEINTRAG'];
						else
							$phoneBookEntryType = 0;


						// Durchlauf COV_ID sprich CO_VOICEDATA - Eben
						if (isset($curProductArray['COV_ID'])) {
							foreach($curProductArray['COV_ID'] as $curCOV_ID => $curCOVArray) {


								// Durchlauf SUBS_ID
								if (isset($curCOVArray['SUBS_ID'])) {
									foreach($curCOVArray['SUBS_ID'] as $curSubID => $curSubArray) {

										$cntSumPhoneNumbers++;

										// Telefonbuch Eintrag?
										if ((isset($curSubArray['TELEFONBUCHEINTRAG'])) && ($curSubArray['TELEFONBUCHEINTRAG'] == 'J')) {

											if ($phoneBookEntryType > 0) {
												$retArray = $this->getAddressPhoneBoockByCustomerIDAndTypeID($curCustomerID, $phoneBookEntryType);

												if (count($retArray) > 0) {

													$cntSumPhoneBookEntry++;

													foreach($retArray as $keyName => $value) {
														$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$curSubID][$keyName] = $value;
													}
												}

											}


										}


									}
								}   // END // Durchlauf SUBS_ID


							}
						}   // END // Durchlauf COV_ID sprich CO_VOICEDATA - Eben

					}   // END // Durchlauf Produkte



				}   // END if ($boolA && $boolA){


			}   // END  // Durchlauf Vertrag

		}   // END // Durchlauf Customer_ID


		$cntExpCustomer = count($this->globalData['CUSTOMER_ID_Array']);


		$this->addMessage('&sum; Ermittelt TelB. Einträge ', $cntSumPhoneBookEntry, 'Info');
		$this->addMessage('&sum; Ermittelt TelB. Einträge ', $cntSumPhoneBookEntry, 'Sum');


		$this->addMessage('&sum; Exportfähige Kunden ', $cntExpCustomer, 'Sum');


		return true;

	}   // END private function getPhoneBookEntrysByCustomerID()






	// Ermittelt die Adressdaten für das Telefonbuch
	public function getAddressPhoneBoockByCustomerIDAndTypeID($getCustomerID, $phoneBookEntryType)
	{

		$retArray = array();

		// ID 10011 == Expliziet gewünschter Telefonbucheintrag
		$query = "SELECT * FROM CUSTOMER_ADDRESSES WHERE CUSTOMER_ID = '" . $getCustomerID . "' AND (ADDRESS_TYPE_ID = '10010' OR ADDRESS_TYPE_ID = '10011') ORDER BY ADDRESS_TYPE_ID";
		$result = ibase_query($this->dbF, $query);

		$cnt = 0;
		while ($row = ibase_fetch_object($result)) {
			$cnt++;

			// Nur wenn alle Daten gewünscht sind diese auch setzen
			if ($phoneBookEntryType == '10001') {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
				$retArray['TELEBUCH_STRASSE'] = $row->STREET . ' ' . $row->HOUSENO . ' ' . $row->HOUSENO_SUPPL;
				$retArray['TELEBUCH_PLZ'] = $row->CITYCODE;
				$retArray['TELEBUCH_ORT'] = $row->CITY;
				$retArray['TELEBUCH_FAX'] = $row->FAX;
			} else {
				$retArray['TELEBUCH_NACHNAME'] = $row->NAME;
				$retArray['TELEBUCH_VORNAME'] = $row->FIRSTNAME;
			}
		}

		ibase_free_result($result);

		return $retArray;
	}






	// VOIP - Telefonnummern einlesen
	private function getSubscriberByCOVID()
	{

		$cntSubscriber = 0;

		// Durchlauf Customer_ID
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $curCustomerArray) {


			// Durchlauf Vertrag
			foreach($curCustomerArray['CONTRACT_ID'] as $curContractID => $curContractArray) {

				// DATEN EBENE!!!
				//TODO ... hmmm sollte ich hier abfangen wenn garkeine PRODCT_ID vorhanden ist?

				// Durchlauf Produkte
				foreach($curContractArray['PRODUCT_ID'] as $curProductID => $curProductArray) {

					// Prüfen ob VOIP Daten für das Produkt vorhanden sein sollten
					if (in_array($curProductID, $this->setProductIDForVOIP[$this->setExportType])) {

						// Ja ist ein VOIP Produkt
						if (isset($curProductArray['COV_ID'])) {

							// Durchlauf COV_ID sprich CO_VOICEDATA - Eben
							foreach($curProductArray['COV_ID'] as $curCOV_ID => $curCOVArray) {

								$query = "SELECT * FROM SUBSCRIBER WHERE COV_ID = '" . $curCOV_ID . "' ORDER BY DISPLAY_POSITION";

								$result = ibase_query($this->dbF, $query);

								$cntInnerSubscriber = 0;
								while ($row = ibase_fetch_object($result)) {
									$cntSubscriber++;
									$cntInnerSubscriber++;

									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['SUBS_ID'] = $row->SUBS_ID;
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['SUBSCRIBER_ID'] = $row->SUBSCRIBER_ID;

									// TODO ... Sascha fragen...
									if ($row->DATE_PORTI_REQ)
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['VOIP_PORT_TERMIN'] = $this->getFormatDate($row->DATE_PORTI_REQ);


									if ($row->CARRIER_ID > 0) {
										$curCarrierCode = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'];
										$curCarrierID = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'];
										$curCarrierName = $this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'];

										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['CARRIER_ID'] = $curCarrierID;
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['CARRIER_CODE'] = $curCarrierCode;
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['CARRIER_NAME'] = $curCarrierName;
									}


									// Telefonbuch - Flag?
									if ($row->PHON_BOOK == '1') {
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'J';
										// Bool - Flag setzen:
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PhoneBookFlagFromSubscriber'] = 'yes';
									} else
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'N';


									// Telefonnummer inversuche sperren?
									if ($row->INVERS_SEARCH == '1')
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEBUCH_SPERRE_INVERS'] = 'N';
									else
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEBUCH_SPERRE_INVERS'] = 'J';



									// Elektr.Telefonbuch?
									if ($row->DIGITAL_MEDIA == '1')
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEBUCH_EINTRAG_ELEKT'] = 'J';
									else
										$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID]['TELEBUCH_EINTRAG_ELEKT'] = 'N';



									// SIP Authname
									$curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntInnerSubscriber;
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID][$curVOIP_ACCOUNT] = $row->SIP_AUTHNAME;

									// SIP Passwort
									$curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntInnerSubscriber;
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID][$curVOIP_ACCOUNT_PASSWORT] = $row->SIP_PASSWORD;

									// Vorwahl
									$curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntInnerSubscriber;
									$val = $this->getNatVorwahl($row->SUBSCRIBER_ID);
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID][$curVOIP_NATIONAL_VORWAHL] = $val;

									// Kopfnummer
									$curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntInnerSubscriber;
									$val = $this->getKopfnummer($row->SUBSCRIBER_ID);
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$row->SUBS_ID][$curVOIP_KOPFNUMMER] = $val;


								}   // END while

								ibase_free_result($result);

							}   // END // Durchlauf COV_ID sprich CO_VOICEDATA - Eben

						}   // END // Ja ist ein VOIP Produkt

					}   // END // Prüfen ob VOIP Daten für das Produkt vorhanden sein sollten


				}   // END // Durchlauf Produkte


			}   // END // Durchlauf Vertrag


		}   // END // Durchlauf Customer_ID

		$cntExpCustomer = count($this->globalData['CUSTOMER_ID_Array']);


		$this->addMessage('&sum; Ermittelt VOIP Nummern ', $cntSubscriber, 'Info');
		$this->addMessage('&sum; Ermittelt VOIP Nummern ', $cntSubscriber, 'Sum');


		$this->addMessage('&sum; Exportfähige Kunden ', $cntExpCustomer, 'Sum');


		return true;

	}   // END  private function getSubscriberByCOVID()






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






	// Carrier Referenz einlesen
	public function getCarrierRef()
	{

		$query = "SELECT * FROM CARRIER ORDER BY CARRIER_ID";

		$result = ibase_query($this->dbF, $query);

		while ($row = ibase_fetch_object($result)) {

			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'] = $row->CARRIER_ID;
			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'] = $row->NAME;
			$this->globalCarrierData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'] = $row->CARRIER_CODE;
		}

		ibase_free_result($result);

		return true;

	}   // END private function getCarrierRef()






	// VOIP Daten einlesen
	private function getCOVoicedataByCOID()
	{

		$cntVOIPData = 0;
		$cntInternetData = 0;
		$cntNoVOIPData = 0;


		// Durchlauf Customer_ID
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $curCustomerArray) {


			// Durchlauf Vertrag
			foreach($curCustomerArray['CONTRACT_ID'] as $curContractID => $curContractArray) {

				// DATEN EBENE!!!
				//TODO ... hmmm sollte ich hier abfangen wenn garkeine PRODCT_ID vorhanden ist?

				// Durchlauf Produkte
				foreach($curContractArray['PRODUCT_ID'] as $curProductID => $curProductArray) {

					// Prüfen ob VOIP Daten für das Produkt vorhanden sein sollten
					if (in_array($curProductID, $this->setProductIDForVOIP[$this->setExportType])) {

						// Ja ist ein VOIP Produkt

						$query = "SELECT * FROM CO_VOICEDATA WHERE CO_ID = '" . $curContractID . "' AND STATUS_ID > '0' ORDER BY COV_ID";
						$result = ibase_query($this->dbF, $query);

						$cntCOV_Data = 0;
						while ($row = ibase_fetch_object($result)) {

							$cntCOV_Data++;


							// EGN_VERFREMDUNG
							if ($row->IB_REDUCED == '1')
								$val = 'J';
							else
								$val = 'N';

							$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['EGN_VERFREMDUNG'] = $val;


							// $setPhoneBookIDs
							// TELEFONBUCHEINTRAG ... temporär setzen
							if (in_array($row->PHONE_BOOK_ENTRY_ID, $this->setPhoneBookIDs)) {
								$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['TELEFONBUCHEINTRAG'] = $row->PHONE_BOOK_ENTRY_ID;
								$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PhoneBookFlagFromCO_VOICEDATA'] = 'yes';

								if ($row->PHONE_BOOK_ENTRY_ID == '10001')
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['TELEFONBUCH_UMFANG'] = 'A';
								elseif ($row->PHONE_BOOK_ENTRY_ID == '10002')
									$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['TELEFONBUCH_UMFANG'] = 'V';

							} else
								$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['TELEFONBUCHEINTRAG'] = '';


							$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$row->COV_ID] = array('COV_ID' => $row->COV_ID);
						}

						ibase_free_result($result);


						if ($cntCOV_Data > 0)
							$cntVOIPData++;
						else {

							// Eventuell Purtel Kunde ... Produkt entfernen
							$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID] = '';
							unset($this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$curProductID]);

							$cntNoVOIPData++;

							// Fehlerhafte VOIP Information ausgeben?
							if ((isset($this->setNoVOIPDataOnScreen)) && ($this->setNoVOIPDataOnScreen == 'yes'))
								$this->addMessage('Keine VOIP Daten (Purtel KD?) bei Kunde ', $curCustomerID, 'Alert');


						}
					} else {

						// Kein VOIP Produkt

						$cntInternetData++;
					}

				}


			}


		}

		$cntExpCustomer = count($this->globalData['CUSTOMER_ID_Array']);


		$this->addMessage('&sum; Ermittelt VOIP ', $cntVOIPData, 'Info');
		$this->addMessage('&sum; Ermittelt VDSL ', $cntInternetData, 'Info');


		$this->addMessage('&sum; Ermittelt VOIP ', $cntVOIPData, 'Sum');
		$this->addMessage('&sum; Ermittelt VDSL ', $cntInternetData, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden ', $cntExpCustomer, 'Sum');

		if ($cntNoVOIPData > 0)
			$this->addMessage('Keine VOIP Daten (DPurtel KD?) x mal ', $cntNoVOIPData, 'Alert');

		return true;

	}   // END private function getCOVoicedataByCOID()






	// Products einlesen die zu den Contracts gehören
	private function getProductsByContractID()
	{


		// Einschränkung bei Internet?
		$addInternet = '';
		if ((isset($this->setProductIDForInternet[$this->setExportType])) && (count($this->setProductIDForInternet[$this->setExportType]) > 0)) {

			$addInternet .= " AND (";

			$bool = true;
			foreach($this->setProductIDForInternet[$this->setExportType] as $index => $curProductID) {

				if ($bool)
					$addInternet .= " p.PRODUCT_ID = '" . $curProductID . "' ";
				else
					$addInternet .= " OR p.PRODUCT_ID = '" . $curProductID . "' ";

				$bool = false;

				$this->addMessage('Einschränkung: Internet nur Produkt ID', $curProductID, 'Info');
			}

		}



		// Einschränkung bei Telefon?
		$addPhone = $addInternet;
		if ((isset($this->setProductIDForVOIP[$this->setExportType])) && (count($this->setProductIDForVOIP[$this->setExportType]) > 0)) {


			// Wenn ich keine Einschränkung bei Internet habe... Flag hier richtig setzen
			if (strlen($addPhone) < 1) {
				$addPhone .= " AND (";
				$bool = true;
			}

			foreach($this->setProductIDForVOIP[$this->setExportType] as $index => $curProductID) {

				if ($bool)
					$addPhone .= " p.PRODUCT_ID = '" . $curProductID . "' ";
				else
					$addPhone .= " OR p.PRODUCT_ID = '" . $curProductID . "' ";

				$bool = false;

				$this->addMessage('Einschränkung: Telefon nur Produkt ID', $curProductID, 'Info');
			}

		}


		// Wenn ich Phone habe... muss ich die Klammer zu machen... addInternet ist vorher gelaufen
		if (strlen($addPhone) > 0)
			$addPhone .= ")";


		// Zähler falsch zugewiesen Kunden FTTH auf FTTC
		$cntFailFTTHIsFTTC = 0;

		$cntProducts = 0;

		// Durchlauf Customer_ID
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $curCustomerArray) {

			$boolGotData = false;


			// Durchlauf Vertrag
			foreach($curCustomerArray['CONTRACT_ID'] as $curContractID => $curContractArray) {

				// DATEN EBENE!!!

				$add = " WHERE cop.CO_ID = '" . $curContractID . "' ";


				// Einschränkungen Telefon und Intertent Product_ID
				$add .= $addPhone;


				$query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID,
                                 cop.DATE_ACTIVE    AS COPDATE_ACTIVE,
                                 cop.DATE_DEACTIVE  AS COPDATE_DEACTIVE,
                                 a.ACCOUNTNO        AS ACCOUNTNO,
                                 a.DESCRIPTION      AS ADESCRIPTION
                            FROM CO_PRODUCTS cop
                              LEFT JOIN PRODUCTS p  ON p.PRODUCT_ID  = cop.PRODUCT_ID
                              LEFT JOIN ACCOUNTS a  ON a.ACCOUNTNO   = p.ACCOUNTNO
                            " . $add . "
                            ORDER BY cop.CO_PRODUCT_ID";


				$result = ibase_query($this->dbF, $query);

				$boolGotProductID = false;
				while ($row = ibase_fetch_object($result)) {

					$cntProducts++;

					//echo "Product: " .$row->DESCRIPTION . "<br>";

					$boolGotProductID = true;


					$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
					$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_Name'] = $row->DESCRIPTION;

					$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['COPDATE_ACTIVE'] = $row->COPDATE_ACTIVE;
					$this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['DATE_DEACTIVE'] = $row->COPDATE_DEACTIVE;

					$boolGotData = true;

				}


				// Kunden aus Daten-Array löschen
				if (!$boolGotProductID) {
					$this->globalData['CUSTOMER_ID_Array'][$curCustomerID] = '';
					unset($this->globalData['CUSTOMER_ID_Array'][$curCustomerID]);
				}

				ibase_free_result($result);

			}


			// Falsch zugewiesener Kunde... ist FTTH statt wie gewollt FTTC
			if (!$boolGotData) {
				$cntFailFTTHIsFTTC++;
				if ((isset($this->setFTTHtoFTTCWrongCustomerOnScreen)) && ($this->setFTTHtoFTTCWrongCustomerOnScreen == 'yes'))
					$this->addMessage('FTTH auf FTTC eingestellt KdNr.', $curCustomerID, 'Alert');
			}

		}


		// Alert Message bei falsch zugewiesene Kunden (FFH ist auf FTTC)
		if ($cntFailFTTHIsFTTC > 0)
			$this->addMessage('FTTH auf FTTC eingestellt x mal', $cntFailFTTHIsFTTC, 'Alert');


		$cntExpCustomer = count($this->globalData['CUSTOMER_ID_Array']);

		$this->addMessage('&sum; Ermittelte Produkte ', $cntProducts, 'Info');
		$this->addMessage('&sum; Ermittelte Produkte ', $cntProducts, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden ', $cntExpCustomer, 'Sum');

		return true;

	}   // END private function getProductsByContractID()






	// Contracts einlesen die zu den ausgewählten Customer gehören
	private function getContractsByCustomerID()
	{

		$boolOnlySignedContracts = false;

		// Nur unterzeichnete Vertäge einlesen?
		if ((isset($this->setReadOnlySignedContracts)) && ($this->setReadOnlySignedContracts == 'yes')) {
			$this->addMessage('Einschränkung: Nur unterzeichnete Verträge', 'ja', 'Info');
			$boolOnlySignedContracts = true;
		}


		$cntContracts = 0;
		$cntCustomerHasNoContract = 0;

		// Durchlauf Customer_ID
		foreach($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $curCustomerArray) {

			$cntContractsPerCustomer = 0;

			$add = '';  // Query Abfrage - Zusatz

			$query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '" . $curCustomerID . "' AND STATUS_ID > '0' " . $add . " ORDER BY CO_ID";

			$result = ibase_query($this->dbF, $query);

			// Gibt es gültige Verträge zu dem Customer?
			if ($this->ibase_num_rows($result) < 1) {
				$cntCustomerHasNoContract++;

				if ((isset($this->setNoContractCustomerOnScreen)) && ($this->setNoContractCustomerOnScreen == 'yes'))
					$this->addMessage('Kein Vertag KdNr.', $curCustomerID, 'Alert');

				continue;
			}

			ibase_free_result($result);

			$result = ibase_query($this->dbF, $query);

			while ($row = ibase_fetch_object($result)) {
				$cntContracts++;
				$cntContractsPerCustomer++;

				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CUSTOMER_ID'] = $curCustomerID;
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CONTRACT_ID'] = $row->CO_ID;

				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['GUELTIG_VON'] = $this->getFormatDate($row->DATE_ACTIVE);
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['INSTALLATIONSTERMIN'] = $this->getFormatDate($row->DATE_ACTIVE);
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['GUELTIG_BIS'] = $this->getFormatDate($row->DATE_DEACTIVE);
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['ERFASST_AM'] = $this->getFormatDate($row->DATE_CREATED);
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['UNTERZEICHNET_AM'] = $this->getFormatDate($row->DATE_SIGNED);

				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CONTR_DATE_ACTIVE_REQ'] = $this->getFormatDate($row->DATE_ACTIVE_REQ);
				$this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CONTR_STATUS_ID'] = $row->STATUS_ID;

			}

			ibase_free_result($result);

		}

		// Global Var - Transfer
		$this->globalData = '';
		$this->globalData = $this->globalDataTMP;
		$this->globalDataTMP = '';


		// Wieviel Kunden haben wir jetzt noch?
		$cntCustomerToExport = count($this->globalData['CUSTOMER_ID_Array']);

		// Status:
		$this->outNow('&sum; Ermittelte Verträge', $cntContracts, 'Info');
		$this->addMessage('&sum; Ermittelte Verträge', $cntContracts, 'Sum');
		$this->addMessage('&sum; Exportfähige Kunden', $cntCustomerToExport, 'Sum');

		// Summenausgabebei Alert
		if ($cntCustomerHasNoContract > 0)
			$this->addMessage('Kein Vertag für Kunde x mal', $cntCustomerHasNoContract, 'Alert');

		return true;

	}   // END private function getContractsByCustomeerID()






	// Customer einlesen die in der angegebenen GruppenID enthalten sind
	private function getCustomerByGroupID()
	{

		$add = '';

		// Nur Customer einlesen die in der Gruppe x sind?
		if ((isset($this->setCustomerByGroupID[$this->setExportType])) && (count($this->setCustomerByGroupID[$this->setExportType]) > 0)) {
			$bool = false;
			foreach($this->setCustomerByGroupID[$this->setExportType] as $curGroupID) {

				if (!$bool)
					$add .= " WHERE GROUP_ID = '" . $curGroupID . "' ";
				else
					$add .= " OR GROUP_ID = '" . $curGroupID . "' ";

				$bool = true;

				$this->addMessage('Einschränkung: Nur Gruppen_ID', $curGroupID, 'Info');

			}
		} else
			$add .= " WHERE CUSTOMER_ID > '1' ";



		// Nur Customer einlesen die NICHT Status x haben?
		if ((isset($this->setNoCustomerInStatusID[$this->setExportType])) && (count($this->setNoCustomerInStatusID[$this->setExportType]) > 0)) {
			foreach($this->setNoCustomerInStatusID[$this->setExportType] as $curStatusID) {
				$add .= " AND STATUS_ID != '" . $curStatusID . "' ";

				$this->addMessage('Einschränkung: Nicht Status_ID', $curStatusID, 'Info');
			}
		}



		// Nur Customer einlesen die Kundennummer x besitzen?
		if ((isset($this->onlyExampleCustomerID)) && ($this->onlyExampleCustomerID > 0)) {

			$this->addMessage('Einschränkung: Nur KdNr.', $this->onlyExampleCustomerID, 'Info');

			$add .= " AND CUSTOMER_ID = '" . $this->onlyExampleCustomerID . "' ";

		}


		// Limitierung gesetzt?
		if ((isset($this->setReadLimitCustomer)) && ($this->setReadLimitCustomer > 0)) {

			$addFirst = 'FIRST ' . $this->setReadLimitCustomer . ' SKIP 0 ';
			$this->addMessage('Einschränkung: Limit Kunden', $this->setReadLimitCustomer, 'Info');

		} else
			$addFirst = '';



		$query = "SELECT " . $addFirst . " * FROM CUSTOMER " . $add . " ORDER BY CUSTOMER_ID";


		$result = ibase_query($this->dbF, $query);


		$cntCustomer = 0;
		while ($row = ibase_fetch_object($result)) {

			$cntCustomer++;

			$this->globalData['CUSTOMER_ID_Array'][$row->CUSTOMER_ID]['CUSTOMER_ID'] = $row->CUSTOMER_ID;

		}



		ibase_free_result($result);

		// Status:
		$this->outNow('&sum; Ermittelte Kunden', $cntCustomer, 'Info');
		$this->addMessage('&sum; Ermittelte Kunden', $cntCustomer, 'Sum');

		return true;

	}   // END private function getCustomerByGroupID()



	////////////////////////// START DIVERSE BLOCK ///////////////////////////////////


	// Ruft Methoden mit flush auf
	public function flushByFunctionCall($getFunction, $hExp = false)
	{

		flush();
		ob_flush();
		$this->outNow($getFunction, '...');
		flush();
		ob_flush();

		$callBy = $this;
		if ($hExp)
			$callBy = $hExp;

		// Methode aufrufen
		if (!$callBy->$getFunction()) {
			$this->outNow($getFunction, 'FAIL');
			flush();
			ob_flush();

			return false;
		}

		flush();
		ob_flush();
		$this->outNow($getFunction, 'OK');
		flush();
		ob_flush();

		return true;

	}   // END private function flushByFunctionCall(...)






	// Dimari Datenbankverbindung herstellen
	public function createDimariDBConnection()
	{

		$host = $this->myHost;
		$username = $this->myUsername;
		$password = $this->myPassword;

		if (!($dbF = ibase_pconnect($host, $username, $password, 'ISO8859_1', 0, 3)))
			die('Could not connect: ' . ibase_errmsg());

		$this->dbF = $dbF;

		// Status
		//$this->outNow('DB Verbindung!', 'OK', 'Info');

		return true;

	}   // END private function createDimariDBConnection()






	// Ibase num_rows
	public function ibase_num_rows($result)
	{

		$myResult = $result;

		$cnt = 0;

		while ($row = @ibase_fetch_row($myResult))
			$cnt++;

		return $cnt;

	}   // END private function ibase_num_rows(...)






	// Datum passend formatieren
	public function getFormatDate($getDate = null)
	{

		if (strlen($getDate) > 0)
			$getDate = date("d.m.Y ", strToTime($getDate));

		return $getDate;

	}   // END private function getFormatDate(...)


	////////////////////////// END DIVERSE BLOCK ///////////////////////////////////



	////////////////////////// START MESSAGE BLOCK ///////////////////////////////////


	// Status hinzufügen und jetzt ausgeben
	public function outNow($messageValue, $messageStatus = 'unset', $messageType = 'Runtime')
	{

		// Message hinzufügen
		$this->addMessage($messageValue, $messageStatus, $messageType);


		// Messages ausgeben
		flush();
		ob_flush();
		$this->showStatus();
		flush();
		ob_flush();

	}   // END private function outNow(...)






	// Status ausgeben:
	public function showStatus()
	{

		$cntCategorie = 0;

		print ('<div style="position: fixed; overflow: auto; top: 5px; bottom: 20px; min-width: 600px; right: 5px; background-color: beige"><table>');

		// Durchlauf der Message - Kategorien
		foreach($this->myMessage as $messageType => $curMessageArray) {

			// Sind Einträge in der Kategorie vorhanden?
			if (count($curMessageArray) > 0) {

				$cntCategorie++;

				// Kategorienamen ausgeben
				if ($cntCategorie > 1)
					print ('<tr><th colspan="3" style="padding-top: 30px;">' . $messageType . '</th></td></tr>');
				else
					print ('<tr><th colspan="3">' . $messageType . '</th></td></tr>');


				// SpaltenTyp ausgeben:
				print ('<tr><td style="min-width: 40px;" class="bottomLine">Cnt</td><td style="min-width: 260px;" class="bottomLine">Event</td><td class="bottomLine">Status</td></tr>');

				foreach($curMessageArray['messageValue'] as $fieldIndex => $value) {

					print ('<tr><td class="bottomLine">');

					$infoCnt = '# ' . ($fieldIndex + 1);
					print ($infoCnt);


					print ('</td><td class="bottomLine">');
					print ($value);
					print ('</td>');


					// Wenn der Status der aktuelle Message gesetzt ist ... und er nicht 'unset' ist... dann ausgeben
					if ((isset($curMessageArray['messageStatus'][$fieldIndex])) && ($curMessageArray['messageStatus'][$fieldIndex] != 'unset'))
						print ('<td class="bottomLine">' . $curMessageArray['messageStatus'][$fieldIndex] . '</td>');
					else
						print ('<td class="bottomLine">&nbsp;</td>');

					print ('</tr>');

				}

			}

		}

		print ('</table></div>');

	}   // END private function showStatus()






	// Hänge ein Message an die ggf. schon bestehenden Messages
	public function addMessage($messageValue, $messageStatus = 'unset', $messageType = 'Runtime')
	{

		$this->myMessage[$messageType]['messageValue'][] = $messageValue;
		$this->myMessage[$messageType]['messageStatus'][] = $messageStatus;

		return true;

	}   // END private function addMessage($messageType, $messageValue)


	////////////////////////// END MESSAGE BLOCK ///////////////////////////////////



}   // END class Dimari
