<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 15:09
 */
class OutData extends CollectData
{

	// Klassen - Konstruktor
	public function __construct($host, $username, $password)
	{

		parent::__construct($host, $username, $password);

	}   // END public function __construct(...)










	// Methode ist die Haupt - Methode für das Daten - Einlesen und für das Ausgeben
	function initialOutDataFullHandling()
	{

		// Starte Daten einlesen:
		if (!$this->initialCollectData())
			exit;
		$this->showStatus();


		// Starte DatenExport (Datei-erstellen):
		if (!$this->writeToExcel())
			exit;
		$this->showStatus();


	}    // END function initialOutDataFullHandling()










	public function custExpSetFillStepOne()
	{

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			$curCustObj->custExpSet['DIENST_ART'] = $this->setExportType;    // 2

			$getDienstDataArray = $this->getDienstDataByCustomerID($curCustomerID);
			$curCustObj->custExpSet['DIENST_BEZEICHNUNG'] = $getDienstDataArray['DIENST_BEZEICHNUNG'];
			$curCustObj->custExpSet['DIENST_BEMERKUNG'] = $getDienstDataArray['DIENST_BEMERKUNG'];

			$curCustObj->custExpSet['DATEN_USERNAME'] = '';            // 5
			$curCustObj->custExpSet['DATEN_USERPASSWORT'] = '';
			$curCustObj->custExpSet['NAT_BETREIBEREBENE'] = 'N';
			$curCustObj->custExpSet['CLIENT_ID'] = '';
			$curCustObj->custExpSet['USERINFO_ID'] = '';

			$getRouterDataArray = $this->getRouterInformationByCustomerID($curCustomerID);
			$curCustObj->custExpSet['ROUTER_MODELL'] = $getRouterDataArray['ROUTER_MODELL'];             // 10
			$curCustObj->custExpSet['ROUTER_SERIEN_NR'] = $getRouterDataArray['ROUTER_SERIEN_NR'];
			$curCustObj->custExpSet['ACS_ID'] = '';
			$curCustObj->custExpSet['EXT_PRODUKT_ID'] = $getDienstDataArray['EXT_PRODUKT_ID'];

			$getOptionDataArray = $this->getOptionsByCustomerID($curCustomerID);
			$curCustObj->custExpSet['OPTION_1'] = $getOptionDataArray['OPTION_1'];
			$curCustObj->custExpSet['OPTION_2'] = $getOptionDataArray['OPTION_2'];                  // 15
			$curCustObj->custExpSet['OPTION_3'] = $getOptionDataArray['OPTION_3'];

			$getContractDataArray = $this->handleContractsByCustomerID($curCustomerID);
			$curCustObj->custExpSet['GUELTIG_VON'] = $getContractDataArray['GUELTIG_VON'];
			$curCustObj->custExpSet['GUELTIG_BIS'] = $getContractDataArray['GUELTIG_BIS'];
			$curCustObj->custExpSet['ERFASST_AM'] = $getContractDataArray['ERFASST_AM'];
			$curCustObj->custExpSet['UNTERZEICHNET_AM'] = $getContractDataArray['UNTERZEICHNET_AM'];              // 20
			$curCustObj->custExpSet['WIDERRUFEN_AM'] = $getContractDataArray['WIDERRUFEN_AM'];
			$curCustObj->custExpSet['GEKUENDIGT_AM'] = $getContractDataArray['GEKUENDIGT_AM'];

			$curCustObj->custExpSet['STANDORT'] = 'KD';
			$curCustObj->custExpSet['INSTALLATIONSTERMIN'] = $getContractDataArray['INSTALLATIONSTERMIN'];

			$getMFGUnitPortDataArray = $this->getMFGUnitPortDataByCustomerID($curCustomerID);

			$curCustObj->custExpSet['HAUPTVERTEILER'] = $getMFGUnitPortDataArray['HAUPTVERTEILER'];                // 25
			$curCustObj->custExpSet['KABELVERZWEIGER'] = $getMFGUnitPortDataArray['KABELVERZWEIGER'];
			$curCustObj->custExpSet['DOPPELADER_1'] = '';
			$curCustObj->custExpSet['DOPPELADER_2'] = '';

			$getVOIPDataArray = $this->getVOIPDataByCustomerID($curCustomerID);
			$curCustObj->custExpSet['VOIP_DIENST_BEZEICHNUNG'] = $getVOIPDataArray['VOIP_DIENST_BEZEICHNUNG'];
			$curCustObj->custExpSet['VOIP_DIENST_BEMERKUNG'] = '';         // 30
			$curCustObj->custExpSet['VOIP_EXT_PRODUKT_ID'] = $getVOIPDataArray['VOIP_EXT_PRODUKT_ID'];
			$curCustObj->custExpSet['SPERRE_0900'] = 'J';
			$curCustObj->custExpSet['UEBERMITTLUNG_RUFNR'] = 'J';
			$curCustObj->custExpSet['PURTEL_KUNDENNUMMER'] = '';
			$curCustObj->custExpSet['PURTEL_HAUPTANSCHLUSS'] = '';         // 35
			$curCustObj->custExpSet['VOIP_SPERRE_AKTIV'] = 'N';

			$curVOIP_PORTIERUNG = $this->getVOIP_PORTIERUNGByCustomerID($curCustomerID);
			$curCustObj->custExpSet['VOIP_PORTIERUNG'] = $curVOIP_PORTIERUNG;

			$curVOIP_PORT_TERMIN = $this->getVOIP_PORT_TERMINByCustomerID($curCustomerID);
			$curCustObj->custExpSet['VOIP_PORT_TERMIN'] = $curVOIP_PORT_TERMIN;
			$curVOIP_PORT_ABG_CARRIER = $this->getVOIP_PORT_ABG_CARRIERByCustomerID($curCustomerID);
			$curCustObj->custExpSet['VOIP_PORT_ABG_CARRIER'] = $curVOIP_PORT_ABG_CARRIER;
			$curCustObj->custExpSet['VOIP_PORT_REST_MSN_KUENDIGEN'] = 'N';  // 40

			for($i = 1; $i <= 3; $i++) {

				$getPhoneDataArray = $this->getPhoneNumbersByCustomerIDAndNumber($curCustomerID, $i);

				$cntVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $i;
				$cntVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $i;
				$cntVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $i;
				$cntVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $i;
				$cntVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $i;

				$curCustObj->custExpSet['VOIP_ACCOUNT_' . $i] = $getPhoneDataArray[$cntVOIP_ACCOUNT];
				$curCustObj->custExpSet['VOIP_ACCOUNT_PASSWORT_' . $i] = $getPhoneDataArray[$cntVOIP_ACCOUNT_PASSWORT];
				$curCustObj->custExpSet['VOIP_NATIONAL_VORWAHL_' . $i] = $getPhoneDataArray[$cntVOIP_NATIONAL_VORWAHL];
				$curCustObj->custExpSet['VOIP_KOPFNUMMER_' . $i] = $getPhoneDataArray[$cntVOIP_KOPFNUMMER];
				$curCustObj->custExpSet['VOIP_TRANSACTION_NO_' . $i] = $getPhoneDataArray[$cntVOIP_TRANSACTION_NO];
			}

			$curEGN_VERFREMDUNG = $this->getEGN_VERFREMDUNGByCustomerID($curCustomerID);
			$curCustObj->custExpSet['EGN_VERFREMDUNG'] = $curEGN_VERFREMDUNG;

			$getTelefonbuchData = $this->getTelefonbuchDataByCustomerID($curCustomerID);
			$curCustObj->custExpSet['TELEFONBUCHEINTRAG'] = $getTelefonbuchData['TELEFONBUCHEINTRAG'];
			$curCustObj->custExpSet['TELEBUCH_NACHNAME'] = $getTelefonbuchData['TELEBUCH_NACHNAME'];
			$curCustObj->custExpSet['TELEBUCH_VORNAME'] = $getTelefonbuchData['TELEBUCH_VORNAME'];
			$curCustObj->custExpSet['TELEBUCH_STRASSE'] = $getTelefonbuchData['TELEBUCH_STRASSE'];    // 60
			$curCustObj->custExpSet['TELEBUCH_PLZ'] = $getTelefonbuchData['TELEBUCH_PLZ'];
			$curCustObj->custExpSet['TELEBUCH_ORT'] = $getTelefonbuchData['TELEBUCH_ORT'];  // 64 (!)
			$curCustObj->custExpSet['TELEBUCH_TEL'] = $getTelefonbuchData['TELEBUCH_TEL'];
			$curCustObj->custExpSet['TELEBUCH_FAX'] = $getTelefonbuchData['TELEBUCH_FAX'];
			$curCustObj->custExpSet['TELEBUCH_SPERRE_INVERS'] = $getTelefonbuchData['TELEBUCH_SPERRE_INVERS'];
			$curCustObj->custExpSet['TELEBUCH_EINTRAG_ELEKT'] = $getTelefonbuchData['TELEBUCH_EINTRAG_ELEKT'];

			for($i = 4; $i <= 10; $i++) {
				$curCustObj->custExpSet['VOIP_ACCOUNT_' . $i] = '';
				$curCustObj->custExpSet['VOIP_ACCOUNT_PASSWORT_' . $i] = '';
				$curCustObj->custExpSet['VOIP_NATIONAL_VORWAHL_' . $i] = '';
				$curCustObj->custExpSet['VOIP_KOPFNUMMER_' . $i] = '';
				$curCustObj->custExpSet['VOIP_TRANSACTION_NO_' . $i] = '';
			}

			$curCustObj->custExpSet['VOIP_ABG_PORT_TERMIN'] = '';
			$curCustObj->custExpSet['VOIP_ABG_PORT_AUF_CARRIER'] = '';
			$curCustObj->custExpSet['DSLAM_PORT'] = $getMFGUnitPortDataArray['DSLAM_PORT'];
			$curCustObj->custExpSet['TELEFONBUCH_UMFANG'] = $getTelefonbuchData['TELEFONBUCH_UMFANG'];


			$curCustObj->custExpSet['TV_DIENSTE'] = $getVOIPDataArray['TV_DIENSTE'];
			$curCustObj->custExpSet['ROUTER_MAC_ADR'] = $getRouterDataArray['ROUTER_MAC_ADR'];

			$getContractNumber = $this->getContractNumberByCustomerID($curCustomerID);
			$curCustObj->custExpSet['FTTH_CUST_ID'] = $getContractNumber;
			if ($curCustObj->custModemType == 'DOCSIS')
				$curCustObj->custExpSet['DOCSIS'] = 'J';
			else
				$curCustObj->custExpSet['DOCSIS'] = 'N';

			$getBridgeDataArray = $this->getBridgeModeByCustomerID($curCustomerID);
			$curCustObj->custExpSet['BRIDGE_MODE'] = $getBridgeDataArray['BRIDGE_MODE'];

			$getElvisDataArray = $this->getElvisByCustomerID($curCustomerID);
			$curCustObj->custExpSet['ELVIS_HAUPT_ACCOUNT'] = $getElvisDataArray['ELVIS_HAUPT_ACCOUNT'];
			$curCustObj->custExpSet['CPE_VOIP_ACCOUNT_2'] = $getElvisDataArray ['CPE_VOIP_ACCOUNT_2'];


			$getBandbreiteDataArray = $this->getBandbreiteByCustomerID($curCustomerID);
			$curCustObj->custExpSet['BANDBREITE'] = $getBandbreiteDataArray['BANDBREITE'];
			$curCustObj->custExpSet['MANDANT_ID'] = $this->setMandantID;

		}

		return true;
	}










	private function getOptionsByCustomerID($curCustomerID)
	{

		$return = array('OPTION_1' => '',
						'OPTION_2' => '',
						'OPTION_3' => '',
		);

		// Anhang - Klassen Objekt erzeugen
		$hAnhang = new Anhang();

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Wenn keine extern-Produkt-ID (also neu Produkt-ID gesetzt ist... dann Methode verlassen)
		if ((!isset($curCustomerObj->custExpSet['EXT_PRODUKT_ID'])) || (strlen($curCustomerObj->custExpSet['EXT_PRODUKT_ID']) < 1))
			return $return;

		// Externe ProduktID des Kunden ermitteln
		$curExtProdID = $curCustomerObj->custExpSet['EXT_PRODUKT_ID'];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;

		$optionCnt = 0;

		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			$nKey = '';

			// Aktuell ProduktID in der Options gesetzt?
			if (!key_exists($curProductID, $hAnhang->productOptions[$this->setMandantID]))
				continue;

			// Ja es gibt eine Option für das aktuelle Produkt ... Inhalt sprich Schlüssel ermitteln
			$getValKey = $hAnhang->productOptions[$this->setMandantID][$curProductID];


			// Suche den passenden Key
			$nKey = array_search($getValKey, $hAnhang->productIDtoOptionIDtoDesc[$curExtProdID]);

//			echo "Cnt: $optionCnt nKey: $nKey key: $curExtProdID " . " Val: " . $getValKey . "<br>";

			// Option zuweisen
			if (strlen($nKey) > 0) {

				$optionCnt++;

				$curOption = 'OPTION_' . $optionCnt;

				$return[$curOption] = $nKey;
			}

//			echo "OPTKION: $nKey";
		}    // END // Durchlauf Produkte


		// HARDCODE Fiber2home Web+
		// Fiber2home web+ Check
		// Wenn der Kunde nur Web (ExtID 832) + Speedupdate (ID 29 in eine der Optionen) hat
		if ($curCustomerObj->custExpSet['EXT_PRODUKT_ID'] == '832') {
			// '836' => 'FIBER2home WEB+',

			if (in_array('29', $return)) {
				$curCustomerObj->custExpSet['EXT_PRODUKT_ID'] = '836';
				$curCustomerObj->custExpSet['DIENST_BEZEICHNUNG'] = $hAnhang->productIDtoDesc[$this->setMandantID]['836'];

				// Option jetzt wieder löschen
				foreach($return as $key => $value) {
					if ($value == '29')
						$return[$key] = '';
				}

				// Optionen wieder nacheinander füllen (3 Durchläufe weil 3 Optionen notwendig)
				$return = $this->sortOption($return);
				$return = $this->sortOption($return);
				$return = $this->sortOption($return);

			}

			if (in_array('85', $return)) {

				// Option jetzt wieder löschen
				foreach($return as $key => $value) {
					if ($value == '85')
						$return[$key] = '';
				}

				if (strlen($return['OPTION_1']) < 1)
					$return['OPTION_1'] = '63';
				elseif (strlen($return['OPTION_2']) < 1)
					$return['OPTION_2'] = '63';
				elseif (strlen($return['OPTION_3']) < 1)
					$return['OPTION_3'] = '63';

				// Optionen wieder nacheinander füllen (3 Durchläufe weil 3 Optionen notwendig)
				$return = $this->sortOption($return);
				$return = $this->sortOption($return);
				$return = $this->sortOption($return);

			}
		}

		return $return;

	}    // END private function getOptionsByCustomerID($curCustomerID)










	// Sortiert bzw. setzt die Optionen lückenlos
	private function sortOption($return)
	{

		// Optionen neu sortieren
		for($i = 1; $i <= 3; $i++) {

			$curOption = 'OPTION_' . $i;

			$prevCalc = $i - 1;
			$prevOption = 'OPTION_' . $prevCalc;

			// Aktuelle Option gesetzt?
			if (strlen($return[$curOption]) >= 1) {

				if ($prevCalc < 1)
					continue;

				// Vorherige Option frei?
				if (strlen($return[$prevOption]) < 1) {
					// Auf vorheriges schieben
					$return[$prevOption] = $return[$curOption];

					// Aktuelles freigeben
					$return[$curOption] = '';
				}
			}

		}

		return $return;

	}










	// Bandbreite ermitteln
	private function getBandbreiteByCustomerID($curCustomerID)
	{

		$return = array('BANDBREITE' => '0/0');

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;

		$boolGotWeb = false;
		if ($this->setMandantID == '0')        // Kunde von TKRZ
			$curBandbreite = '100/10';
		elseif ($this->setMandantID == '1')    // Kunde von RheiNet
			$curBandbreite = '50/10';
		else
			$curBandbreite = '25/2,5';        // Kunde von X

		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			// Überhaupt ein Internet-Produkt vorhanden?
			if ($this->setMandantID == '0') {
				if ($productArray['ACCOUNTNO'] == '85300')
					$boolGotWeb = true;
			}

			// Sonderfall bei RheiNet gehe ich über die ProduktID der GENEXIS BOX
			if ($this->setMandantID == '1') {
				if ($productArray['PRODUCT_ID'] == '10043')
					$boolGotWeb = true;
			}

			// Sonderfall bei Schüttorf gehe ich über ProduktID 10027 (SWS.net-WEB - Internet 25/2,5)
			if ($this->setMandantID == '3') {
				if ($productArray['PRODUCT_ID'] == '10027')
					$boolGotWeb = true;
			}

			// Speedupgrade TKRZ?
			if ($this->setMandantID == '0') {
				if (($curProductID == '10072') || ($curProductID == '10028'))
					$curBandbreite = '200/20';
			}

			// RheiNet?
			if ($this->setMandantID == '1') {
				//if (($curProductID == '10062') || ($curProductID == 'xxx'))
				if ($curProductID == '10062')
					$curBandbreite = '100/20';
			}

			// Schüttorf? SPEEDUPGRADE?
			if ($this->setMandantID == '3') {
				if ($curProductID == '10028')
					$curBandbreite = '50/5';
			}


		}    // END // Durchlauf Produkte

		if (!$boolGotWeb)
			return $return;

		$return['BANDBREITE'] = $curBandbreite;

		return $return;

	}    // END private function getBandbreiteByCustomerID($curCustomerID)










	// BridgeMode ermitteln
	private function getBridgeModeByCustomerID($curCustomerID)
	{

		$return = array('BRIDGE_MODE' => 'KEIN');

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;

		// Wenn DOCSIS ... gibt es kein Bridge-Mode... kann also Methode verlassen
		if ($curCustomerObj->custModemType == 'DOCSIS')
			return $return;


		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			if ((isset($productArray['SR_RP_ID'])) && (strlen($productArray['SR_RP_ID']) > 1)) {

				// Eintrag A abhandeln
				$GotA = '';
				if (isset($productArray['INFO_TEXT1'])) {
					if ($productArray['INFO_TEXT1'] == 'Ja')
						$GotA = 'J';
					elseif ($productArray['INFO_TEXT1'] == 'Nein')
						$GotA = 'N';
				}


				// Eintrag B abhandeln
				$GotB = '';
				if (isset($productArray['INFO_TEXT2'])) {
					if ($productArray['INFO_TEXT2'] == 'Ja')
						$GotB = 'J';
					elseif ($productArray['INFO_TEXT2'] == 'Nein')
						$GotB = 'N';
				}

				// Bridge? = N N
				if (($GotA == 'N') && ($GotB == 'N'))
					$curBridge = 'BRIDGE';    // N und N

				elseif (($GotA == 'N') && ($GotB == 'J'))
					$curBridge = 'IPFON';    // N und J

				else
					$curBridge = 'KEIN';    // sonstig

				$return['BRIDGE_MODE'] = $curBridge;

				return $return;

			}

		}    // END // Durchlauf Produkte


		return $return;

	}    // END private function getBridgeModeByCustomerID($curCustomerID)










	// MFG UNIT PORT
	private function getMFGUnitPortDataByCustomerID($curCustomerID)
	{

		$return = array('DSLAM_PORT'      => '',
						'KABELVERZWEIGER' => '',
						'HAUPTVERTEILER'  => ''
		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;


		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			if ((isset($productArray['HAUPTVERTEILER'])) && (strlen($productArray['HAUPTVERTEILER']) > 0)) {

				$return['HAUPTVERTEILER'] = $productArray['HAUPTVERTEILER'];
				$return['KABELVERZWEIGER'] = $productArray['KABELVERZWEIGER'];

				$data = $productArray['DSLAM_PORT'];
				$search = '/(\d+)/';

				if (preg_match($search, $data, $match))
					$val = $match[1];
				else
					$val = $productArray['DSLAM_PORT'];

				$return['DSLAM_PORT'] = $val;

				return $return;
			}

		}

		return $return;

	}    // END private function getMFGUnitPortDataByCustomerID($curCustomerID)










	private function getElvisByCustomerID($curCustomerID)
	{

		$return = array('ELVIS_HAUPT_ACCOUNT' => '',
						'CPE_VOIP_ACCOUNT_2'  => ''
		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custSubIDSet
		if ((!isset($curCustomerObj->custSubIDSet)) || (count($curCustomerObj->custSubIDSet) < 1))
			return $return;

		$n = count($curCustomerObj->custSubIDSet);

		if ($n == 1) {
			$return['ELVIS_HAUPT_ACCOUNT'] = '1';
		}
		elseif ($n >= 2) {
			$return['ELVIS_HAUPT_ACCOUNT'] = '1';
			$return['CPE_VOIP_ACCOUNT_2'] = '2';
		}

		return $return;

	}    // END private function getElvisByCustomerID($curCustomerID)










	private function getRouterInformationByCustomerID($curCustomerID)
	{

		$return = array('ROUTER_MODELL'    => '',
						'ROUTER_SERIEN_NR' => '',
						'ROUTER_MAC_ADR'   => ''
		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;



		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			if ((!isset($productArray['SR_RP_ID'])) || (strlen($productArray['SR_RP_ID']) < 1))
				continue;

			// Mandant TKRZ
			if ($this->setMandantID == '0') {
				if ($curCustomerObj->custModemType == 'DOCSIS') {
					$return['ROUTER_MODELL'] = $productArray['PRODUCT_NAME'];

					// DATA 1 = MAC WEB
					$return['ROUTER_MAC_ADR'] = $productArray['SR_DATA_1'];

					// DATA 3 = Serial No.
					$return['ROUTER_SERIEN_NR'] = $productArray['SR_DATA_3'];
				}

				elseif ($curCustomerObj->custModemType == 'GENEXIS') {
					$return['ROUTER_MODELL'] = $productArray['PRODUCT_NAME'];

					// DATA 1 = MAC WEB
					$return['ROUTER_MAC_ADR'] = $productArray['SR_DATA_1'];

					// DATA 3 = Serial No.
					if (strlen($productArray['SR_DATA_3']) > 0)
						$return['ROUTER_SERIEN_NR'] = $productArray['SR_DATA_3'];
					else
						$return['ROUTER_SERIEN_NR'] = '';
				}
			}
			else {
				if ($curCustomerObj->custModemType == 'DOCSIS') {
					$return['ROUTER_MODELL'] = $productArray['PRODUCT_NAME'];

					// DATA 1 = MAC WEB
					$return['ROUTER_MAC_ADR'] = $productArray['SR_DATA_1'];

					// DATA 3 = Serial No.
					$return['ROUTER_SERIEN_NR'] = $productArray['SR_DATA_3'];
				}

				elseif ($curCustomerObj->custModemType == 'GENEXIS') {
					$return['ROUTER_MODELL'] = $productArray['PRODUCT_NAME'];

					// DATA 1 = MAC WEB
					$return['ROUTER_MAC_ADR'] = $productArray['SR_DATA_1'];

					// DATA 3 = Serial No.
					if (strlen($productArray['SR_DATA_3']) > 0)
						$return['ROUTER_SERIEN_NR'] = $productArray['SR_DATA_3'];
					else
						$return['ROUTER_SERIEN_NR'] = '';
				}
			}


		}    // END // Durchlauf Produkte

		return $return;

	}    // END 	private function getRouterInformationByCustomerID($curCustomerID)










	// Liefert die ContractID
	private function getContractNumberByCustomerID($curCustomerID)
	{

		$return = '';


		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custContractSet
		if ((!isset($curCustomerObj->custContractSet)) || (count($curCustomerObj->custContractSet) < 1))
			return $return;


		// Durchlauf Verträge
		foreach($curCustomerObj->custContractSet as $curContractID => $contractArray) {

			return $curContractID;

		}

		return $return;

	}    // END private function getContractNumberByCustomerID($curCustomerID)










	// Liefert VOIP Daten
	private function getVOIPDataByCustomerID($curCustomerID)
	{

		$return = array('VOIP_DIENST_BEZEICHNUNG' => '',
						'VOIP_EXT_PRODUKT_ID'     => '',
						'TV_DIENSTE'              => 'N'
		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;


		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			if ((isset($productArray['ACCOUNTNO'])) && (strlen($productArray['ACCOUNTNO']) > 0)) {

				// Telefon - Produkt?
				if ($productArray['ACCOUNTNO'] == '85200') {

					// Docsis FON oder FIBER4business VoIP
					$return['VOIP_DIENST_BEZEICHNUNG'] = 'FTTx VoIP';
					if ((isset($productArray['PRODUCT_NAME'])) && (strlen($productArray['PRODUCT_NAME']) > 0))
						$return['VOIP_DIENST_BEZEICHNUNG'] = $productArray['PRODUCT_NAME'];

					$return['VOIP_EXT_PRODUKT_ID'] = '771';

				}

				// TV - Produkt?
				if ($productArray['ACCOUNTNO'] == '85400') {
					$return['TV_DIENSTE'] = 'J';
				}


			}

		}    // END // Durchlauf Produkte


		// Explizite VOIP - Prüfung TKRZ:
		if ($this->setMandantID == '0') {
			if (isset($curCustomerObj->custSubIDSet)) {
				if (count($curCustomerObj->custSubIDSet) > 0) {
					// Genexis Kunde?
					if ($curCustomerObj->custModemType == 'GENEXIS')
						$return['VOIP_DIENST_BEZEICHNUNG'] = 'FTTx VoIP';
					else
						$return['VOIP_DIENST_BEZEICHNUNG'] = 'fiberFON';

					$return['VOIP_EXT_PRODUKT_ID'] = '771';
				}
			}
		}

		// Explizite VOIP - Prüfung RheiNet:
		if ($this->setMandantID == '1') {
			if (isset($curCustomerObj->custSubIDSet)) {
				if (count($curCustomerObj->custSubIDSet) > 0) {
					$return['VOIP_DIENST_BEZEICHNUNG'] = 'FTTx VoIP';
					$return['VOIP_EXT_PRODUKT_ID'] = '771';
				}
			}
		}

		// Explizite VOIP - Prüfung Schüttorf:
		if ($this->setMandantID == '3') {
			if (isset($curCustomerObj->custSubIDSet)) {
				if (count($curCustomerObj->custSubIDSet) > 0) {
					$return['VOIP_DIENST_BEZEICHNUNG'] = 'FTTx VoIP';
					$return['VOIP_EXT_PRODUKT_ID'] = '771';
				}
			}
		}



		return $return;

	}    // END private function getVOIPDataByCustomerID($curCustomerID)










	// Liefert den Dienst
	private function getDienstDataByCustomerID($curCustomerID)
	{

		$hAnhang = new Anhang();

		$return = array('DIENST_BEZEICHNUNG' => '',
						'DIENST_BEMERKUNG'   => '',
						'EXT_PRODUKT_ID'     => ''
		);


		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Prüfung auf custProductSet
		if ((!isset($curCustomerObj->custProductSet)) || (count($curCustomerObj->custProductSet) < 1))
			return $return;

		// Modem Typ:
		$curModemType = $curCustomerObj->custModemType;

		// in_Array Name:
		$curInArray = 'productIDTo_' . $curModemType . '_' . $this->setMandantID;

		$myProds = array();


		// Durchlauf Produkte
		foreach($curCustomerObj->custProductSet as $curProductID => $productArray) {

			if (key_exists($curProductID, $hAnhang->$curInArray)) {

				// echo "hier $curProductID<br>";

				// Array mit neuen Produkt - IDs erstellen
				$myProds[] = $hAnhang->$curInArray[$curProductID];
			}
			else {
				$otherProds[] = $curProductID;
			}

		}    // END // Durchlauf Produkte


//		if (count($otherProds) > 0){
//
////			$noProdID = array('10043', '10046' , '10022', '10047' ,'10044' , '10057');
//			$noProdID = array('10043', '10046' , '10022', '10047' ,'10044');
//
//			foreach ($otherProds as $key => $prodID){
//				if (!in_array($prodID,  $noProdID)){
//					echo "PRODUKT_ID $prodID<br>";
//					// IDEBUG pre - tag
//					echo "<pre><hr>";
//					print_r($curCustomerObj);
//					echo "<hr></pre><br>";
//
//					exit;
//				}
//			}
//		}




		$newProdID = '';
		// TODO auch für Schüttorf prüfen
		if ($this->setMandantID == '0') {    // TKRZ

			if (count($myProds) == 1) {    // Wenn 1 Produkt dann ... aus Anhang-Liste nehmen
				$newProdID = $myProds[0];
			}
			elseif (count($myProds) == 2) {    // Wenn 2 Produkte dann ... Zusammen - Wert holen
				$newProdID = $hAnhang->$curInArray['together'];
			}
			elseif (count($myProds) == 3) {    // + TV MUSS GENEXIS sein
				$newProdID = $hAnhang->$curInArray['10025'];
			}
			elseif (count($myProds) == 4) {    // + TV MUSS GENEXIS sein
				$newProdID = $hAnhang->$curInArray['10025'];
			}
			else {
				echo "TKRZ Kann Dienst-Bezeichnung nicht festlegen für CustomerID: $curCustomerID<br>";
			}
		}
		elseif ($this->setMandantID == '1') {    // RheiNet
			if (count($myProds) == 1) {    // Wenn 1 Produkt dann ... aus Anhang-Liste nehmen
				$newProdID = $myProds[0];
			}
			else {
				echo "RheiNet Kann Dienst-Bezeichnung nicht festlegen für CustomerID: $curCustomerID<br>";
			}
		}
		elseif ($this->setMandantID == '3') {    // Schüttorf
			if (count($myProds) == 1) {    // Wenn 1 Produkt dann ... aus Anhang-Liste nehmen
				$newProdID = $myProds[0];
			}
			else {
				echo "Schüttorf Kann Dienst-Bezeichnung nicht festlegen für CustomerID: $curCustomerID<br>";
			}
		}

		$return['EXT_PRODUKT_ID'] = $newProdID;

		// DIENST_BEZEICHNUNG
		$return['DIENST_BEZEICHNUNG'] = $hAnhang->getProductDescByProductID($this->setMandantID, $newProdID);

		return $return;

	}    // END private function getDienstDataByCustomerID($curCustomerID)










	// Lifert den ... VOIP_PORT_ABG_CARRIER
	private function getVOIP_PORT_ABG_CARRIERByCustomerID($curCustomerID)
	{

		$return = '';

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Prüfung auf custSubIDSet
		if ((!isset($curCustomerObj->custSubIDSet)) || (count($curCustomerObj->custSubIDSet) < 1))
			return $return;

		foreach($curCustomerObj->custSubIDSet as $curSubID => $subIDArray) {

			if ((isset($subIDArray['CARRIER_CODE'])) && (strlen($subIDArray['CARRIER_CODE']) > 0))
				return $subIDArray['CARRIER_CODE'];

		}

		return $return;

	}    // END private function getVOIP_PORT_ABG_CARRIERByCustomerID($curCustomerID)










	// Liefert die Telefonbuch-Daten zu einem Kunden
	private function getTelefonbuchDataByCustomerID($curCustomerID)
	{

		$return = array('TELEFONBUCHEINTRAG'     => 'N',
						'TELEBUCH_NACHNAME'      => '',
						'TELEBUCH_VORNAME'       => '',
						'TELEBUCH_STRASSE'       => '',
						'TELEBUCH_PLZ'           => '',
						'TELEBUCH_ORT'           => '',
						'TELEBUCH_TEL'           => '',
						'TELEBUCH_FAX'           => '',
						'TELEBUCH_SPERRE_INVERS' => 'J',
						'TELEBUCH_EINTRAG_ELEKT' => 'N',
						'TELEFONBUCH_UMFANG'     => '',
		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Prüfung auf custVOIPSet (VOIP - Daten)
		if ((!isset($curCustomerObj->custVOIPSet)) || (count($curCustomerObj->custVOIPSet) < 1))
			return $return;


		// Frage klären ob Telefonbuch - Eintrag ja oder nein
		// Dafür mus in VOIP Data der Eintrag auf J stehen auch in den Telefondaten
		foreach($curCustomerObj->custVOIPSet as $curVOIPID => $voipArray) {

			if ((isset($voipArray['TELEFONBUCHEINTRAG'])) && ($voipArray['TELEFONBUCHEINTRAG'] == 'J')) {

				if (isset($voipArray['TELEFONBUCH_UMFANG']))
					$return['TELEFONBUCH_UMFANG'] = $voipArray['TELEFONBUCH_UMFANG'];

				// VOIP Daten sagen Ja TelB-Eintrag... was mit den Telefondaten?
				if ((isset($curCustomerObj->custSubIDSet)) && (count($curCustomerObj->custSubIDSet) > 0)) {

					// Durchlauf Telefonnummern
					foreach($curCustomerObj->custSubIDSet as $curSUBID => $subIDArray) {

						// Aktueller Durchlauf passt zum voip-Set?
						if ($subIDArray['COV_ID'] == $curVOIPID) {
							if ($subIDArray['TELEFONBUCHEINTRAG'] == 'J') {
								// Ja liegt vor

								// Key Durchlauf
								foreach($return as $key => $defValue) {

									if (isset($subIDArray[$key]))
										$return[$key] = $subIDArray[$key];
									else
										$return[$key] = $defValue;
								}

								return $return;
							}

						}    // END // Aktueller Durchlauf passt zum voip-Set?

					}    // END // Durchlauf Telefonnummern

				}    // END if subSet (Telefonnumern) existieren

			}    // END if Telfonbuch im Voip-Data


		}    // END Durchlauf VOIPSet

		return $return;

	}    // END private function getTelefonbuchData($curCustomerID)










	// Liefert die EGN_VERFREMDUNG zu einem Kunden
	private function getEGN_VERFREMDUNGByCustomerID($curCustomerID)
	{

		// EGN_VERFREMDUNG
		$return = 'N';        // Default auf N

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Wenn wir keine Telefondaten haben... können wir leer zurückliefern
		if ((!isset($curCustomerObj->custVOIPSet)) || (count($curCustomerObj->custVOIPSet) < 1))
			return $return;

		// Durchlauf custVOIPSet
		foreach($curCustomerObj->custVOIPSet as $curVOIPID => $subVOIPArray) {

			if ((isset($subVOIPArray['EGN_VERFREMDUNG'])) && (strlen($subVOIPArray['EGN_VERFREMDUNG']) > 0))
				return $subVOIPArray['EGN_VERFREMDUNG'];

		}    // END // Durchlauf custVOIPSet


		return $return;

	}    // END private function getEGN_VERFREMDUNGByCustomerID($curCustomerID)










	// Liefert VOIP - Telefondaten wie Kopfnummer und Vorwahl
	private function getPhoneNumbersByCustomerIDAndNumber($curCustomerID, $curNumnber)
	{

		$cntVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $curNumnber;
		$cntVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $curNumnber;
		$cntVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $curNumnber;
		$cntVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $curNumnber;
		$cntVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $curNumnber;

		$return = array($cntVOIP_ACCOUNT          => '',
						$cntVOIP_ACCOUNT_PASSWORT => '',
						$cntVOIP_NATIONAL_VORWAHL => '',
						$cntVOIP_KOPFNUMMER       => '',
						$cntVOIP_TRANSACTION_NO   => '',

		);

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Wenn wir keine Telefondaten haben... können wir leer zurückliefern
		if ((!isset($curCustomerObj->custSubIDSet)) || (count($curCustomerObj->custSubIDSet) < 1))
			return $return;

		$myCnt = 1;
		foreach($curCustomerObj->custSubIDSet as $curSubID => $subIDArray) {

			// Aktueller Durchlauf ist != gesuchtem ... dann nächster Eintrag
			if ($myCnt == $curNumnber) {

				if (isset($subIDArray[$cntVOIP_ACCOUNT]))
					$return[$cntVOIP_ACCOUNT] = $subIDArray[$cntVOIP_ACCOUNT];
				else
					$return[$cntVOIP_ACCOUNT] = '';


				if (isset($subIDArray[$cntVOIP_ACCOUNT_PASSWORT]))
					$return[$cntVOIP_ACCOUNT_PASSWORT] = $subIDArray[$cntVOIP_ACCOUNT_PASSWORT];
				else
					$return[$cntVOIP_ACCOUNT_PASSWORT] = '';


				if (isset($subIDArray[$cntVOIP_NATIONAL_VORWAHL]))
					$return[$cntVOIP_NATIONAL_VORWAHL] = $subIDArray[$cntVOIP_NATIONAL_VORWAHL];
				else
					$return[$cntVOIP_NATIONAL_VORWAHL] = '';


				if (isset($subIDArray[$cntVOIP_KOPFNUMMER]))
					$return[$cntVOIP_KOPFNUMMER] = $subIDArray[$cntVOIP_KOPFNUMMER];
				else
					$return[$cntVOIP_KOPFNUMMER] = '';


				if (isset($subIDArray[$cntVOIP_TRANSACTION_NO]))
					$return[$cntVOIP_TRANSACTION_NO] = $subIDArray[$cntVOIP_TRANSACTION_NO];
				else
					$return[$cntVOIP_TRANSACTION_NO] = '';

			}

			// Telefonnummer- Eintrag hochzählen
			$myCnt++;


		}


		return $return;

	}    // END private function getPhoneNumbersByCustomerIDAndNumber($curCustomerID, $curNumnber)










	// Liefer den VOIU_PORT_TERMIN für eine Kundennummer
	private function getVOIP_PORT_TERMINByCustomerID($curCustomerID)
	{

		// VOIP_PORT_TERMIN

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		$VOIP_PORT_TERMIN = '';

		// Telefonnummern gar nicht vorhanden? ... dann leer zurück geben
		if (!isset($curCustomerObj->custSubIDSet))
			return $VOIP_PORT_TERMIN;


		// Durchlauf Telefonnummern
		foreach($curCustomerObj->custSubIDSet as $curSubID => $subIDArray) {

			if ((isset($subIDArray['VOIP_PORT_TERMIN'])) && (strlen($subIDArray['VOIP_PORT_TERMIN']) > 0)) {

				// Haben einen VOIP_Termin... Abbruch bzw. Methode verlassen
				return $subIDArray['VOIP_PORT_TERMIN'];

			}
		}

		return $VOIP_PORT_TERMIN;

	}    // END private function getVOIP_PORT_TERMINByCustomerID($curCustomerID)










	// Liefert den VOIP_PORTIERUNG - Wert
	private function getVOIP_PORTIERUNGByCustomerID($curCustomerID)
	{

		// VOIP_PORTIERUNG

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];


		$boolGotCarrierCode = false;
		foreach($curCustomerObj->custSubIDSet as $curSubID => $subIDArray) {

			if ((isset($subIDArray['CARRIER_CODE'])) && (strlen($subIDArray['CARRIER_CODE']) > 0))
				$boolGotCarrierCode = true;

		}

		// Wenn kein Abg Carrier ... dann keine Portierung
		if (!$boolGotCarrierCode)
			return 'N';

		// Wenn SUBS_ID ... Portierung J
		if ((isset($curCustomerObj->custSubIDSet)) && (count($curCustomerObj->custSubIDSet) > 0))
			return 'J';

		return 'N';

	}    // END private function getVOIP_PORTIERUNGByCustomerID($curCustomerID)










	// Verarbeitet / Setzt Vertrag-Daten zu einer Kundennummer
	private function handleContractsByCustomerID($curCustomerID)
	{

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		// Return Array
		$getContractDataArray = array('GUELTIG_VON'         => '',
									  'GUELTIG_BIS'         => '',
									  'ERFASST_AM'          => '',
									  'UNTERZEICHNET_AM'    => '',
									  'WIDERRUFEN_AM'       => '',
									  'GEKUENDIGT_AM'       => '',
									  'INSTALLATIONSTERMIN' => '',
									  'VOIP_PORT_TERMIN'    => ''
		);


		// Wenn kein Vertrag vorhanden ist... können wir "Leer-Daten" zurückliefern
		if ((!isset($curCustomerObj->custContractSet)) || (count($curCustomerObj->custContractSet) < 1))
			return $getContractDataArray;


		// Durchlauf Vertärge:
		// TODO ... Sollte eigentlich nur ein gültiger Vertag sein... ich kann nur einmal Datum - Informationen senden
		foreach($curCustomerObj->custContractSet as $curContractID => $contractArray) {

			$getContractDataArray['GUELTIG_VON'] = $curCustomerObj->custContractSet[$curContractID]['GUELTIG_VON'];
			$getContractDataArray['GUELTIG_BIS'] = $curCustomerObj->custContractSet[$curContractID]['GUELTIG_BIS'];
			$getContractDataArray['ERFASST_AM'] = $curCustomerObj->custContractSet[$curContractID]['ERFASST_AM'];
			$getContractDataArray['UNTERZEICHNET_AM'] = $curCustomerObj->custContractSet[$curContractID]['UNTERZEICHNET_AM'];
			$getContractDataArray['WIDERRUFEN_AM'] = $curCustomerObj->custContractSet[$curContractID]['WIDERRUFEN_AM'];
			$getContractDataArray['GEKUENDIGT_AM'] = $curCustomerObj->custContractSet[$curContractID]['GEKUENDIGT_AM'];

			// Gekündigter Datensatz? ... dann das gekündigt am berechnen
			if (strlen($getContractDataArray['GUELTIG_BIS']) > 0) {

				// Zwischenspeichern für einfachere Handhabung
				$curGueltigBis = $getContractDataArray['GUELTIG_BIS'];


				$strTime = strtotime($curGueltigBis);
				$checkGueltigBis = date("Y-m-d", $strTime);
				$today = date("Y-m-d");
				if ($checkGueltigBis < $today) {

					$curGekuendigtAm = date('d.m.Y', strtotime('-3 month', strtotime($curGueltigBis)));

					$getContractDataArray['GEKUENDIGT_AM'] = $curGekuendigtAm;

				}
				else {
					$curGekuendigtAm = date('d.m.Y', strtotime('-6 month', strtotime($curGueltigBis)));

					$getContractDataArray['GEKUENDIGT_AM'] = $curGekuendigtAm;
				}
			}



			// Nur wenn noch kein Installationstermin des Kunden gestzt ist:
			if ((!isset($curCustomerObj->custExpSet['INSTALLATIONSTERMIN'])) || (strlen($curCustomerObj->custExpSet['INSTALLATIONSTERMIN']) < 1)) {
				// Setze INSTALLATIONSTERMIN Doppelt abgefangen
				// Hab noch keinen Installationstermin gesetz, weiche aus auf ... GUELTIG_VON... VOIP_PORT_TERMIN... wenn möglich
				if ((!isset($curCustomerObj->custContractSet[$curContractID]['INSTALLATIONSTERMIN'])) || (count($curCustomerObj->custContractSet[$curContractID]) < 1)) {

					// VOIP_PORT_TERMIN für diesen Vertrag ermitteln
					$curVOIPPortTermin = $this->getVOIPPortTerminByContractID($curCustomerID, $curContractID);

					// GUELTIG_VON eventuell gesetzt?
					if ((isset($curCustomerObj->custContractSet[$curContractID]['GUELTIG_VON'])) && (strlen($curCustomerObj->custContractSet[$curContractID]['GUELTIG_VON']) > 0))
						$curCustomerObj->custContractSet[$curContractID]['INSTALLATIONSTERMIN'] = $curCustomerObj->custContractSet[$curContractID]['GUELTIG_VON'];
					else {
						if ((isset($curVOIPPortTermin)) && (strlen($curVOIPPortTermin) > 0))
							$curCustomerObj->custContractSet[$curContractID]['INSTALLATIONSTERMIN'] = $curVOIPPortTermin;
						else
							$curCustomerObj->custContractSet[$curContractID]['INSTALLATIONSTERMIN'] = '';
					}
				}

				$getContractDataArray['INSTALLATIONSTERMIN'] = $curCustomerObj->custContractSet[$curContractID]['INSTALLATIONSTERMIN'];
			}
			else
				$getContractDataArray['INSTALLATIONSTERMIN'] = $curCustomerObj->custExpSet['INSTALLATIONSTERMIN'];

		}    // END // Durchlauf Vertärge:

		return $getContractDataArray;

	}    // END private function handleContractsByCustomerID($curCustomerID)










	// Liefert den VOIP_PORT_TERMIN zu einem Kunden und zu einem Vertrag
	private function getVOIPPortTerminByContractID($curCustomerID, $curContractID)
	{

		// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
		$curCustomerObj = $this->custArray[$curCustomerID];

		$return = '';

		// Wenn keine Telefonnummer vorhanden ist... können wir "Leer-Daten" zurückliefern
		if ((!isset($curCustomerObj->custSubIDSet)) || (count($curCustomerObj->custSubIDSet) < 1))
			return $return;


		// Durchlauf Telefonnummer
		foreach($curCustomerObj->custSubIDSet as $curSubID => $subIDArray) {

			// Durchlauf bis ich die aktuellen Telefondaten zum Vertrag gefunden habe
			if ($subIDArray['COV_ID'] == $curContractID) {

				// Wenn hier kein VOIP_PORT_TERMIN gesetzt ist, können wir den Durchlauf beenden
				if ((!isset($subIDArray['VOIP_PORT_TERMIN'])) || (strlen($subIDArray['VOIP_PORT_TERMIN']) < 1))
					break;

				return $subIDArray['VOIP_PORT_TERMIN'];
			}

		}    // END // Durchlauf Telefonnummer

		return $return;

	}    // END private function getVOIPPortTerminByContractID($curContractID)










	public function writeToExcel()
	{

		$excel = $this->writeToExcelHeadline();

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
			$curCustomerObj = $this->custArray[$curCustomerID];

			// echo "Zeile: " . $cntRow . "<br>";

			$leadingPipe = false;

			foreach($curCustomerObj->custExpSet as $fieldname => $value) {

				//echo "Feldname: " . $fieldname . " => Value: " . $value . "<br>";

				// Trennzeichen setzen?
				if ($leadingPipe)
					$excel .= ';';

				$excel .= '"' . utf8_encode(trim($value)) . '"';

				// Ab jetzt Trennzeichen setzen!
				$leadingPipe = true;

			}

			$excel .= "\r\n";

		}


//        echo "<br><hr>";
//        echo "<pre>";
//        print_r($excel);
//        echo "</pre><br>";

		// Datei schreiben:
		$curFilename = $this->writeFile($this->setExportType, $excel);

		return true;
	}










	public function writeToExcelHeadline()
	{

		$excel = '';

		// Duchlauf Customer - Handler
		foreach($this->custArray as $customerIDFromObject => $curCustObj) {

			// Aktuelle KundenNummer
			$curCustomerID = $curCustObj->custExpSet['KUNDEN_NR'];

			// Aktuelle Customer-Klassen-Objekt zuweisen ... Grund: einfachere weitere Bearbeitung im Code
			$curCustomerObj = $this->custArray[$curCustomerID];

			// Prüfung auf custExpSet
			if ((!isset($curCustomerObj->custExpSet)) || (count($curCustomerObj->custExpSet) < 1))
				return false;

			$leadingPipe = false;

			foreach($curCustomerObj->custExpSet as $fieldname => $value) {

				// echo "Feldname: " . $fieldname . " => Value: " . $value . "<br>";

				// Trennzeichen setzen?
				if ($leadingPipe)
					$excel .= ';';

				$excel .= '"' . utf8_encode(trim($fieldname)) . '"';

				// Ab jetzt Trennzeichen setzen!
				$leadingPipe = true;

			}

			$excel .= "\r\n";

			break;

		}    // END // Duchlauf Customer - Handler

		return $excel;

	}










	// Schreibt die Export Datei mit Format Version und Datum
	private function writeFile($type, $content, $filename = false)
	{

		if (!$filename)
			$filename = 'DimariDiensteExp_' . $type . '_' . $this->setMandant . '_' . 'V001_' . date('Ymd');

		// '/var/www/html/www/uploads/';
		$fullFilePathAndName = 'uploads/exports/' . $filename . '.csv';


		// Existiert Datei schon? ... wenn ja, Version erhöhen
		if (file_exists($fullFilePathAndName)) {

			// Versionsnummer ermitteln
			preg_match('/(_V(\d+))/', $filename, $matches);
			$fileVersion = $matches[2];

			$nextVersion = $fileVersion + 1;
			$nextVersion = sprintf("%'.03d", $nextVersion);

			$filename = 'DimariDiensteExp_' . $type . '_' . $this->setMandant . '_V' . $nextVersion . '_' . date('Ymd');

			// Für die Info-Ausgabe
			$this->globalLastFilename = $filename;

			// Selbstaufruf ... endet wenn freie Versionsnummer gefunden wurde
			$this->writeFile($type, $content, $filename);
		}
		else {
			// Für die Info-Ausgabe
			$this->globalLastFilename = $filename;

			if ($this->setExpFileCreation == 'yes') {
				$fp = fopen($fullFilePathAndName, 'w');
				fwrite($fp, $content);
				fclose($fp);
			}
		}

		return true;

	}    // END public function writeFile($type, $content, $filename=false)


}   // END class OutData