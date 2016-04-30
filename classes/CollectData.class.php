<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 15:08
 */
abstract class CollectData extends Message
{

	// TKRZ / RheiNet / Schuettorf
	public $setMandant = 'TKRZ';

	// Export Typ: FTTC oder FTTH
	public $setExportType = 'FTTH';


	// Nur Customer einlesen der Kundennummer x besitzt?
	public $setOnlyExampleCustomerID = '';


	// Wie viele Customer sollen eingelesen werden?
	// 0 für keine Einschränkung beim Limit
	public $setReadLimitCustomer = '2';


	// Customer einlesen die in der Customer Gruppe ... sind
	// Tabelle: CUSTOMER_GROUP
	// Feld:    GROUP_ID
	// Format: Mandant => GROUP_ID
	public $setCustomerByGroupID = array('XYZ'  => array('100011'),
										 'TKRZ' => array('100001',
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
	public $setNoCustomerInStatusID = array('XYZ'  => array('10004', '2'),
											'TKRZ' => array('10004', '2')
	);


	// Customer mit folgender Nummer NICHT einlesen
	public $setDoNotReadThisCustomerIDs = array('20010000');


	// Sollen nur unterzeichnete Verträge ermittelt werden? (bool var)
	// Abschnitt nicht programmiert!!!
	// private $setReadOnlySignedContracts = true;


	// Vertragstatus muss grösser Null sein? (bool var)
	private $setReadOnlyContractStatusAboveNull = false;


	////////////////////////////////// Do not edit below this line!!! /////////////////////////////


	// Datenbank Variable ... werden durch den Construktor gesetzt
	private $myHost;
	private $myUsername;
	private $myPassword;

	// Datenbank Object
	private $dbF;

	// Var-Array enthält alle Customer/Kunden Objekt-Handler
	public $custArray = array();










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








//		// IDEBUG pre - tag
		echo "<pre><hr>";
		print_r($this->custArray);
		echo "<hr></pre><br>";


		// Info Datenerfassung start
		$this->addMessage('Datenerfassung', '... DONE', 'Runtime');

		return true;

	}    // END function initialCollectData()
















	// CO_Products einlesen die zu den Contracts gehören
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

				$query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID,
                                 p.PRODUCT_CODE		AS COS_ID,
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

				while ($row = ibase_fetch_object($result)) {

					// Habe ein Produkt für einen Vertrag des Kunden... Flag setzen
					$boolGotSomeProductForCustomer = true;

					// Habe ein Produkt für diesen Vertrag des Kunden ... Flag setzen
					$boolGotProductForContract = true;

					$cntProducts++;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['CONTRACT_ID'] = $curContractID;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['PRODUCT_NAME'] = $row->DESCRIPTION;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['COPDATE_ACTIVE'] = $row->COPDATE_ACTIVE;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['DATE_DEACTIVE'] = $row->COPDATE_DEACTIVE;

					$curCustObj->custProductSet[$row->PRODUCT_ID]['COS_ID'] = $row->COS_ID;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTNO'] = $row->ACCOUNTNO;
					$curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTDESC'] = $row->ADESCRIPTION;
					// $curCustObj->custProductSet[$row->PRODUCT_ID]['ACCOUNTDESC'] = utf8_encode($row->ADESCRIPTION);
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










	// Contracts einlesen die zu den ausgewählten Customer gehören
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
			if ($this->setReadOnlyContractStatusAboveNull) {
				$query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '" . $curCustomerID . "' AND STATUS_ID > '0' " . $addQueryFilter . " ORDER BY CO_ID";
				$this->addMessage('Nur Verträge deren Status_ID > 0 (Null) ist', 'ja', 'Filter');
			}
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
				$curCustObj->custContractSet[$row->CO_ID]['CONTR_STATUS_ID'] = $row->STATUS_ID;
				$curCustObj->custContractSet[$row->CO_ID]['CONTR_DATE_ACTIVE_REQ'] = $this->getFormatDate($row->DATE_ACTIVE_REQ);

				// Weil ein Kunde mehrere Verträge habe kann, muss ich die (eigentlichen) Basis-Daten pro Vertrag festhalten
				$curCustObj->custContractSet[$row->CO_ID]['GUELTIG_VON'] = $this->getFormatDate($row->DATE_ACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['INSTALLATIONSTERMIN'] = $this->getFormatDate($row->DATE_ACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['GUELTIG_BIS'] = $this->getFormatDate($row->DATE_DEACTIVE);
				$curCustObj->custContractSet[$row->CO_ID]['ERFASST_AM'] = $this->getFormatDate($row->DATE_CREATED);
				$curCustObj->custContractSet[$row->CO_ID]['UNTERZEICHNET_AM'] = $this->getFormatDate($row->DATE_SIGNED);
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










	// Customer einlesen die in der angegebenen GruppenID enthalten sind
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










	// Datum passend formatieren
	public function getFormatDate($getDate = null)
	{

		if (strlen($getDate) > 0)
			$getDate = date("d.m.Y ", strToTime($getDate));

		return $getDate;

	}   // END private function getFormatDate(...)










	// Datenbankverbindung zum Dimari-System aufbauen
	function createDimariDBConnection()
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