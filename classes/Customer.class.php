<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 29.04.2016
 * Time: 13:04
 */
class Customer
{

	// Variablen Array mit allen Feldern die exportiert werden sollen
	// Siehe Methode: initVarSet();
	public $custExpSet = array();


	// Vertrags Array des Kunden
	public $custContractSet = array();


	// Produkt Array des Kunden
	public $custProductSet = array();


	// VOIP Array des Kunden
	public $custVOIPSet = array();

	// SubID Array des Kunden	(Telefonnummer)
	public $custSubIDPSet = array();









	// Klassen - Konstruktor
	public function __construct()
	{

		// Aufruf Initial - Methode initialisiert die benötigten Variablen
		$this->initVarSet();

	}   // END public function __construct(...)










	// Initial - Methode initialisiert die benötigten Variablen
	private function initVarSet()
	{

		$this->custExpSet['KUNDEN_NR'] = '';                 // 1
//		$this->custExpSet['DIENST_ART'] = '';                // 2
//		$this->custExpSet['DIENST_BEZEICHNUNG'] = '';
//		$this->custExpSet['DIENST_BEMERKUNG'] = '';
//		$this->custExpSet['DATEN_USERNAME'] = '';            // 5
//		$this->custExpSet['DATEN_USERPASSWORT'] = '';
//		$this->custExpSet['NAT_BETREIBEREBENE'] = '';
//		$this->custExpSet['CLIENT_ID'] = '';
//		$this->custExpSet['USERINFO_ID'] = '';
//		$this->custExpSet['ROUTER_MODELL'] = '';             // 10
//		$this->custExpSet['ROUTER_SERIEN_NR'] = '';
//		$this->custExpSet['ACS_ID'] = '';
//		$this->custExpSet['EXT_PRODUKT_ID'] = '';
//		$this->custExpSet['OPTION_1'] = '';
//		$this->custExpSet['OPTION_2'] = '';                  // 15
//		$this->custExpSet['OPTION_3'] = '';
//		$this->custExpSet['GUELTIG_VON'] = '';
//		$this->custExpSet['GUELTIG_BIS'] = '';
//		$this->custExpSet['ERFASST_AM'] = '';
//		$this->custExpSet['UNTERZEICHNET_AM'] = '';              // 20
//		$this->custExpSet['WIDERRUFEN_AM'] = '';
//		$this->custExpSet['GEKUENDIGT_AM'] = '';
//		$this->custExpSet['STANDORT'] = '';
//		$this->custExpSet['INSTALLATIONSTERMIN'] = '';
//		$this->custExpSet['HAUPTVERTEILER'] = '';                // 25
//		$this->custExpSet['KABELVERZWEIGER'] = '';
//		$this->custExpSet['DOPPELADER_1'] = '';
//		$this->custExpSet['DOPPELADER_2'] = '';
//		$this->custExpSet['VOIP_DIENST_BEZEICHNUNG'] = '';
//		$this->custExpSet['VOIP_DIENST_BEMERKUNG'] = '';         // 30
//		$this->custExpSet['VOIP_EXT_PRODUKT_ID'] = '';
//		$this->custExpSet['SPERRE_0900'] = '';
//		$this->custExpSet['UEBERMITTLUNG_RUFNR'] = '';
//		$this->custExpSet['PURTEL_KUNDENNUMMER'] = '';
//		$this->custExpSet['PURTEL_HAUPTANSCHLUSS'] = '';         // 35
//		$this->custExpSet['VOIP_SPERRE_AKTIV'] = '';
//		$this->custExpSet['VOIP_PORTIERUNG'] = '';
//		$this->custExpSet['VOIP_PORT_TERMIN'] = '';
//		$this->custExpSet['VOIP_PORT_ABG_CARRIER'] = '';
//		$this->custExpSet['VOIP_PORT_REST_MSN_KUENDIGEN'] = '';  // 40
//
//		for($i = 1; $i <= 3; $i++) {
//			$this->custExpSet['VOIP_ACCOUNT_' . $i] = '';
//			$this->custExpSet['VOIP_ACCOUNT_PASSWORT_' . $i] = '';
//			$this->custExpSet['VOIP_NATIONAL_VORWAHL_' . $i] = '';
//			$this->custExpSet['VOIP_KOPFNUMMER_' . $i] = '';
//			$this->custExpSet['VOIP_TRANSACTION_NO_' . $i] = '';
//		}
//
//		$this->custExpSet['EGN_VERFREMDUNG'] = '';
//		$this->custExpSet['TELEFONBUCHEINTRAG'] = 'N';
//		$this->custExpSet['TELEBUCH_NACHNAME'] = '';
//		$this->custExpSet['TELEBUCH_VORNAME'] = '';
//		$this->custExpSet['TELEBUCH_STRASSE'] = '';    // 60
//		$this->custExpSet['TELEBUCH_PLZ'] = '';
//
//		$this->custExpSet['TELEBUCH_ORT'] = '';  // 64 (!)
//		$this->custExpSet['TELEBUCH_TEL'] = '';
//		$this->custExpSet['TELEBUCH_FAX'] = '';
//		$this->custExpSet['TELEBUCH_SPERRE_INVERS'] = '';
//		$this->custExpSet['TELEBUCH_EINTRAG_ELEKT'] = '';
//
//		for($i = 4; $i <= 10; $i++) {
//			$this->custExpSet['VOIP_ACCOUNT_' . $i] = '';
//			$this->custExpSet['VOIP_ACCOUNT_PASSWORT_' . $i] = '';
//			$this->custExpSet['VOIP_NATIONAL_VORWAHL_' . $i] = '';
//			$this->custExpSet['VOIP_KOPFNUMMER_' . $i] = '';
//			$this->custExpSet['VOIP_TRANSACTION_NO_' . $i] = '';
//		}
//
//		$this->custExpSet['VOIP_ABG_PORT_TERMIN'] = '';
//		$this->custExpSet['VOIP_ABG_PORT_AUF_CARRIER'] = '';
//		$this->custExpSet['DSLAM_PORT'] = '';
//		$this->custExpSet['TELEFONBUCH_UMFANG'] = '';
//
//
//		$this->custExpSet['TV_DIENSTE'] = '';
//		$this->custExpSet['ROUTER_MAC_ADR'] = '';
//		$this->custExpSet['FTTH_CUST_ID'] = '';
//		$this->custExpSet['DOCSIS'] = '';
//		$this->custExpSet['BRIDGE_MODE'] = '';
//		$this->custExpSet['ELVIS_HAUPT_ACCOUNT'] = '';
//		$this->custExpSet['CPE_VOIP_ACCOUNT_2'] = '';
//		$this->custExpSet['BANDBREITE'] = '';
//		$this->custExpSet['CUST_ID'] = '';

	}    // END private function initVarSet()










	public function setThis($var, $val)
	{

		$this->$var = $val;
	}

}   // END class Customer