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
	public $setReadLimitCustomer = 0;


	// Customer einlesen die in der Customer Gruppe ... sind
	// Tabelle: CUSTOMER_GROUP
	// Feld:    GROUP_ID
	// Format: Mandant => GROUP_ID
	public $setCustomerByGroupID = array('XYZ' => array('100011'),
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
	public $setNoCustomerInStatusID = array('XYZ' => array('10004', '2'),
											'TKRZ' => array('10004', '2')
	);


	// Datenbank Variable ... werden durch den Construktor gesetzt
	private $myHost;
	private $myUsername;
	private $myPassword;

	// Datenbank Object
	private $dbF;




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
		if ( (!isset($this->setMandant)) || (strlen($this->setMandant) < 1) ){

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






		return true;

	}    // END function initialCollectData()









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

			$this->globalData['CUSTOMER_ID_Array'][$row->CUSTOMER_ID]['CUSTOMER_ID'] = $row->CUSTOMER_ID;

			$hCustomer[$row->CUSTOMER_ID] = new Customer();
			$hCustomer[$row->CUSTOMER_ID]->CUSTOMER_ID = $row->CUSTOMER_ID;
		}



		ibase_free_result($result);

		// Status:
		$this->outNow('&sum; Ermittelte Kunden', $cntCustomer, 'Info');
		$this->addMessage('&sum; Ermittelte Kunden', $cntCustomer, 'Sum');

		// IDEBUG pre - tag
		echo "<pre><hr>";
		print_r($hCustomer);
		echo "<hr></pre><br>";

		return true;

	}   // END private function getCustomerByGroupID()


















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


}   // END class CollectData