<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 09.03.2016
 * Time: 08:29
 */
class Dimari
{
    // Initial Variable
    public $globalMessage = array();    // Status Messages
    public $globalData = array();       // Data zum verarbeiten
    public $globalTarget = array();     // Ziel Ausgabe


    // Gruppen-Typ der verarbeitet werden soll FFTC / FTTH ... steuert $SET_GROUP_ID_BY_TYPE
    public $getGroupType;

    // Gruppen-Typ der verarbeitet werden soll FFTC / FTTH ... steuert $SET_ACCOUNT_ID_BY_TYPE
    public $getAccountType;

    // Datenbank - Verbindung zu Dimari
    private $dbF;

    // Group IDs für diesen Export:
    private $SET_GROUP_ID_BY_TYPE = array('FTTC' => array('100011'),
                                          'FTTH' => array('100005','100006','100007','100008','100002','100001'));


    // AccountNo IDs für diesen Export: (Tabelle ACCOUNTS)
    private $SET_ACCOUNT_ID_BY_TYPE = array('FTTC' => array('85200','85300','85600'),
                                            'FTTH' => array(''));

    // Nummer für Telefonie in der ACCOUNTS
    private $SET_ACCOUNT_ID_FOR_PHONE = array ('FTTC' => array('85200','85500','15001'));


    // StatusID der Customer!
    private $SET_ACCOUNT_BY_STATUS_ID = array('FTTC' => array('10004','2'),
                                               'FTTH' => array(''));


    // Nur unterzeichnete Vertärge exportieren?
    private  $SET_TRANSFER_ONLY_SIGNED_DATA = 'no';

    private $SET_PHONE_BOOK_ENTRY_IDS = array('10002', '10001');

    private $host;
    private $username;
    private $password;



    //private $SET_EXAMPLE_CUSTOMER_ID = '20010190';
    //private $SET_EXAMPLE_CUSTOMER_ID = '20010120';  // Matthias Brumm(!)
    //private $SET_EXAMPLE_CUSTOMER_ID = '20010612';
    //private $SET_EXAMPLE_CUSTOMER_ID = '20010603';
    //private $SET_EXAMPLE_CUSTOMER_ID = '20011102';


    public function __construct($host, $username, $password)
    {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
    }



    public function myName($out=false)
    {
        if ($out)
            print (__CLASS__);

        return __CLASS__;
    }




    // Initial - Methode ... FTTC - Dienste an Konzeptum
    public function initialGetFTTCServices()
    {
        // Status festhalten:
        $this->globalMessage['Status'][] = 'START';

        // Status festhalten:
        $this->globalMessage['Status'][] = 'Typ: ' . $this->getGroupType;

        echo "0 START<br>";
        flush();
        ob_flush();


        // Dimari DB Verbindung herstellen
        if (!$this->createDimariDBConnection())
            return false;

        echo "1 ... createDimariDBConnection ... done<br>";
        flush();
        ob_flush();


        // Customer einlesen die in der angegebenen GruppenID enthalten sind
        if (!$this->getCustomerByGroupID())
            return false;

        echo "2 ... getCustomerByGroupID ... done<br>";
        flush();
        ob_flush();


        // Contracts einlesen die zu den ausgewählten Customer gehören
        if (!$this->getContractsByCustomeerID())
            return false;

        echo "3 ... getContractsByCustomeerID ... done<br>";
        flush();
        ob_flush();


        // CO_Products einlesen die zu den Contracts gehören
        if (!$this->getProductsByContractID())
            return false;

        echo "4 .. getProductsByContractID ... done<br>";
        flush();
        ob_flush();


        // VOIP Daten einlesen
        if (!$this->getCOVoicedataByCOID())
          return false;

        echo "5 ... getCOVoicedataByCOID ... done<br>";
        flush();
        ob_flush();


        // Carrier Referenz einlesen
        if (!$this->getCarrierRef())
            return false;
        echo "6 ... getCarrierRef ... done<br>";
        flush();
        ob_flush();



        // VOIP - Telefonnummern einlesen
        // Die Telefonnummer die ggf. eingetragen wird kommt aus der Subscriber... weiter unten!!!
        if (!$this->getSubscriberByCOVID())
            return false;
        echo "7 ... getSubscriberByCOVID ... done<br>";
        flush();
        ob_flush();



        // Telefonbucheinträge ermitteln
        if (!$this->getPhoneBookEntrysByCustomerID())
           return false;

        echo "8 ... getPhoneBookEntrysByCustomerID ... done<br>";
        flush();
        ob_flush();


        // Daten in Datei exportieren
        $hExp = new DimariExp($this->globalTarget);

        $hExp->initialDimariExport();
        echo "9 ... initialDimariExport ... done<br>";
        flush();
        ob_flush();


    }







    // Carrier Referenz einlesen
    private function getCarrierRef()
    {

        $query = "SELECT * FROM CARRIER ORDER BY CARRIER_ID";

        $result = ibase_query($this->dbF, $query);

        while ($row = ibase_fetch_object($result)) {

            $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'] = $row->CARRIER_ID;
            $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'] = $row->NAME;
            $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'] = $row->CARRIER_CODE;
        }

        ibase_free_result($result);

        return true;
    }















    // Telefonbuch Einträge?
    private function getSubscriberByCOVID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalTarget['CUSTOMER_ID']))
            return false;

        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$contractArray) {
            // echo "CustomerID: $curCustomerID --> <br>";

            // Durchlauf Contracts
            foreach ($contractArray['CO_ID'] as $curContractID => $someDataArray) {

                // Aktuelle Product_ID in der Telefongruppe?
                // Durchlauf PRODUCT_ID
                foreach ($someDataArray as $dataBezeichnung=>$dynDataType){

                    if ($dataBezeichnung == 'PRODUCT_ID') {
                    //if ( ($dataBezeichnung == 'PRODUCT_ID') && (isset($dynDataType['COV_ID'])) ) {

                        // Durchlauf Produkt IDs
                        foreach ($dynDataType as $curProductID => $someProductDescriptions) {


                            // Ist das Produkt ein Telefonprodukt?
                            if ( (in_array($someProductDescriptions['ACCOUNT_NO'], $this->SET_ACCOUNT_ID_FOR_PHONE[$this->getGroupType])) && (isset($someProductDescriptions['COV_ID'])) ) {
                                // Ja ... Telefonprodukt!

                                foreach ($someProductDescriptions['COV_ID'] as $covIDIndex=>$curCOV_IDArray){

                                    $query = "SELECT * FROM SUBSCRIBER WHERE COV_ID = '".$covIDIndex."' ORDER BY DISPLAY_POSITION";

                                    $result = ibase_query($this->dbF, $query);

                                    $cnt = 0;
                                    $cntNumbers = 0;
                                    while ($row = ibase_fetch_object($result)) {
                                        $cnt++;
                                        $cntNumbers++;

                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['SUBS_ID'] = $row->SUBS_ID;
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['SUBSCRIBER_ID'] = $row->SUBSCRIBER_ID;

                                        if ($row->DATE_PORTI_REQ)
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['VOIP_PORT_TERMIN'] = $this->getFormatDate($row->DATE_PORTI_REQ);

                                        if ($row->CARRIER_ID > 0)
                                        {
                                            $curCarrierCode = $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_CODE'];
                                            $curCarrierID   = $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_ID'];
                                            $curCarrierName = $this->globalData['CARRIER'][$row->CARRIER_ID]['CARRIER_NAME'];

                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['CARRIER_ID'] = $curCarrierID;
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['CARRIER_CODE'] = $curCarrierCode;
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['CARRIER_NAME'] = $curCarrierName;
                                        }



                                        // Telefonbuch - Flag?
                                        if ($row->PHON_BOOK == '1'){
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'J';
                                            // Bool - Flag setzen:
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromSubscriber'] = 'yes';
                                        }
                                        else
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCHEINTRAG'] = 'N';



                                        // Telefonnummer inversuche sperren?
                                        if ($row->INVERS_SEARCH == '1')
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCH_SPERRE_INVERS'] = 'N';
                                        else
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCH_SPERRE_INVERS'] = 'J';



                                        // Elektr.Telefonbuch?
                                        if ($row->DIGITAL_MEDIA == '1')
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCH_EINTRAG_ELEKT'] = 'J';
                                        else
                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID]['TELEFONBUCH_EINTRAG_ELEKT'] = 'N';



                                        // SIP Authname
                                        $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntNumbers;
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID][$curVOIP_ACCOUNT] = $row->SIP_AUTHNAME;

                                        // SIP Passwort
                                        $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntNumbers;
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID][$curVOIP_ACCOUNT_PASSWORT] = $row->SIP_PASSWORD;

                                        // Vorwahl
                                        $curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHLT_' . $cntNumbers;
                                        $val = $this->getNatVorwahl($row->SUBSCRIBER_ID);
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID][$curVOIP_NATIONAL_VORWAHL] = $val;

                                        // Kopfnummer
                                        $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntNumbers;
                                        $val = $this->getKopfnummer($row->SUBSCRIBER_ID);
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$covIDIndex]['SUBS_ID'][$row->SUBS_ID][$curVOIP_KOPFNUMMER] = $val;


                                        // echo $curVOIP_ACCOUNT ."<br>";
                                    }

                                    ibase_free_result($result);

                                }


                            }
                        }
                    }

                }

            }

        }

        // Status festhalten:
        //$this->globalMessage['Status'][] = $cntPhoneBEntrysA . ' / '.$cntPhoneBEntrysB.' (Wunsch / Standard) Telefonbucheinträge wurden ermittelt!';

        return true;

    }   // END private function getPhoneBookEntrysByCustomerID









    // Vorwahl extrahieren
    private function getNatVorwahl($arg=0)
    {
        $val = 0;
        $pattern = $arg;
        $search = '/(.*\d+)+( )(\d+)( )(.\d+)/';
        $matches[1] = '';
        $matches[3] = '';

        preg_match($search, $pattern, $matches);
        if ( (isset($matches[3])) && (strlen($matches[3] > 0)) )
            $val = '0'.$matches[3];

        return trim($val);
    }



    // Vorwahl extrahieren
    private function getKopfnummer($arg=0)
    {
        $val = 0;
        $pattern = $arg;
        $search = '/(.*\d+)+( )(\d+)( )(.\d+)/';
        $matches[5] = '';

        preg_match($search, $pattern, $matches);

        if ( (isset($matches[5])) && (strlen($matches[5] > 0)) )
            $val = $matches[5];

        return trim($val);
    }









    // Telefonbuch Einträge?
    private function getPhoneBookEntrysByCustomerID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalTarget['CUSTOMER_ID']))
            return false;

        $cntPhoneBEntrysA = 0;
        $cntPhoneBEntrysB = 0;

        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$contractArray) {
            // echo "CustomerID: $curCustomerID --> <br>";


            // Haben wir grün aus Co_Voicedata?
            $boolA = false;
            if ( (isset($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromCO_VOICEDATA'])) && ($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromCO_VOICEDATA'] == 'yes') )
                $boolA = true;

            // Haben wir grün aus Subscriber?
            $boolB = false;
            if ( (isset($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromSubscriber'])) && ($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromSubscriber'] == 'yes') )
                $boolB = true;

            if ($boolA && $boolA){

                // Durchlauf Contracts
                foreach ($contractArray['CO_ID'] as $curContractID => $someDataArray) {

                    if (isset($someDataArray['PRODUCT_ID'])){
                        foreach ($someDataArray['PRODUCT_ID'] as $curProductID=>$cov_array){
                            if (isset($cov_array['TELEFONBUCHEINTRAG']))
                                $phoneBookEntryType = $cov_array['TELEFONBUCHEINTRAG'];

                            if (isset($cov_array['COV_ID'])){
                                foreach ($cov_array['COV_ID'] as $curCOV_ID=>$nextArrayA){

                                    if (isset($nextArrayA['SUBS_ID'])){
                                        foreach ($nextArrayA['SUBS_ID'] as $curSubID=>$subIDArray){

                                            if ( (isset($subIDArray['TELEFONBUCHEINTRAG'])) && ($subIDArray['TELEFONBUCHEINTRAG'] == 'J') ){

                                                if ($phoneBookEntryType > 0){
                                                    $retArray = $this->getAddressPhoneBoockByCustomerIDAndTypeID($curCustomerID, $phoneBookEntryType);

                                                    if (count($retArray) > 0){
                                                        foreach ($retArray as $keyName=>$value){
                                                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$curCOV_ID]['SUBS_ID'][$curSubID][$keyName] = $value;
                                                        }
                                                    }

                                                }

                                            }


                                        }
                                    }

                                }
                            }


                        }
                    }


                }

            }

        }

        // Status festhalten:
        //$this->globalMessage['Status'][] = $cntPhoneBEntrysA . ' / '.$cntPhoneBEntrysB.' (Wunsch / Standard) Telefonbucheinträge wurden ermittelt!';

        return true;

    }   // END private function getPhoneBookEntrysByCustomerID








    // Ermittelt die Adressdaten für das Telefonbuch
    private function getAddressPhoneBoockByCustomerIDAndTypeID($getCustomerID, $phoneBookEntryType)
    {
        $retArray = array();

        // ID 10011 == Expliziet gewünschter Telefonbucheintrag
        $query = "SELECT * FROM CUSTOMER_ADDRESSES WHERE CUSTOMER_ID = '".$getCustomerID."' AND (ADDRESS_TYPE_ID = '10010' OR ADDRESS_TYPE_ID = '10011') ORDER BY ADDRESS_TYPE_ID";
        $result = ibase_query($this->dbF, $query);

        $cnt = 0;
        while ($row = ibase_fetch_object($result)) {
         $cnt++;

            // Nur wenn alle Daten gewünscht sind diese auch setzen
            if ($phoneBookEntryType == '10001'){
                $retArray['TELEFONBUCH_NACHNAME']   = $row->NAME;
                $retArray['TELEFONBUCH_VORNAME']    = $row->FIRSTNAME;
                $retArray['TELEFONBUCH_STRASSE']    = $row->STREET . ' ' . $row->HOUSENO . ' ' . $row->HOUSENO_SUPPL;
                $retArray['TELEFONBUCH_PLZ']        = $row->CITYCODE;
                $retArray['TELEFONBUCH_ORT']        = $row->CITY;
                $retArray['TELEFONBUCH_FAX']        = $row->FAX;
            }
            else {
                $retArray['TELEFONBUCH_NACHNAME']   = $row->NAME;
                $retArray['TELEFONBUCH_VORNAME']    = $row->FIRSTNAME;
            }
        }

        ibase_free_result($result);

        return $retArray;
    }










    // VOIP Daten einlesen
    private function getCOVoicedataByCOID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalTarget['CUSTOMER_ID']))
            return false;

        $cntVoiceData = 0;

        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$contractArray) {
            // echo "CustomerID: $curCustomerID --> <br>";


            // Durchlauf Contracts
            foreach ($contractArray['CO_ID'] as $curContractID => $someDataArray) {
                // echo "ContractID: $curContractID ---> <br>";


                // Durchlauf PRODUCT_ID
                foreach ($someDataArray as $dataBezeichnung=>$dynDataType){

                    if ($dataBezeichnung == 'PRODUCT_ID'){

                        // Durchlauf Produkt IDs
                        foreach ($dynDataType as $curProductID=>$someProductDescriptions){


                            // Ist das Produkt ein Telefonprodukt?
                            if (in_array($someProductDescriptions['ACCOUNT_NO'], $this->SET_ACCOUNT_ID_FOR_PHONE[$this->getGroupType])){
                                // Ja ... Telefonprodukt!

                                $query = "SELECT * FROM CO_VOICEDATA WHERE CO_ID = '".$curContractID."' AND STATUS_ID > '0' ORDER BY CO_ID";
                                $result = ibase_query($this->dbF, $query);

                                $cnt = 0;
                                while ($row = ibase_fetch_object($result)) {
                                    $cnt++;
                                    $cntVoiceData++;

                                    // EGN_VERFREMDUNG
                                    if ($row->ANONYMISATION == 1)
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['EGN_VERFREMDUNG'] = 'J';
                                    else
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['EGN_VERFREMDUNG'] = 'N';


                                    // TELEFONBUCHEINTRAG ... temporär setzen
                                    if (in_array($row->PHONE_BOOK_ENTRY_ID, $this->SET_PHONE_BOOK_ENTRY_IDS)){
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['TELEFONBUCHEINTRAG'] = $row->PHONE_BOOK_ENTRY_ID;
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['PhoneBookFlagFromCO_VOICEDATA'] = 'yes';
                                    }
                                    else
                                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['TELEFONBUCHEINTRAG'] = '';

                                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$curProductID]['COV_ID'][$row->COV_ID] = array('COV_ID'=>$row->COV_ID);

                                }

                                ibase_free_result($result);

                            }

                        }

                    }

                }

            }

        }

        // Status festhalten:
        $this->globalMessage['Status'][] = $cntVoiceData . ' VoiceData IDs wurden ermittelt!';

        return true;

    }   // END private function getCOVoicedataByCOID()



















    // Products einlesen die zu den Contracts gehören
    private function getProductsByContractID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalTarget['CUSTOMER_ID']))
            return false;

        $cntProducts = 0;

        // Für jeden Contracts den Produkt-Eintrag ermitteln
        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$contractArray){
            // echo "CustomerID: $curCustomerID --> <br>";

            // Durchlauf Contracts
            foreach ($contractArray['CO_ID'] as $curContractID=>$someDataArray){
                // echo "ContractID: $curContractID ---> <br>";

                // Dynamische Gruppen WHERE - Abfrage
                $bool = false;
                $add = '';
                /*
                foreach ($this->SET_ACCOUNT_ID_BY_TYPE[$this->getAccountType] as $curACCOUNTNO){

                    if (!$bool)
                        $add .= " WHERE cop.CO_ID = '".$curContractID."' AND (p.ACCOUNTNO = '".$curACCOUNTNO."' ";
                    else
                        $add .= " OR p.ACCOUNTNO = '".$curACCOUNTNO."' ";

                    $bool = true;

                }
                $add .= ')';
                */

                $add = " WHERE cop.CO_ID = '".$curContractID."' ";

                // $SET_ACCOUNT_ID_FOR_PHONE

                $query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID,
                                 a.ACCOUNTNO        AS ACCOUNTNO,
                                 a.DESCRIPTION      AS ADESCRIPTION
                            FROM CO_PRODUCTS cop
                              LEFT JOIN PRODUCTS p ON p.PRODUCT_ID = cop.PRODUCT_ID
                              LEFT JOIN ACCOUNTS a ON a.ACCOUNTNO = p.ACCOUNTNO
                            ".$add."
                            ORDER BY cop.CO_PRODUCT_ID";

                $result = ibase_query($this->dbF, $query);

                // UEBERMITTLUNG_RUFNR (DEFAULT)
//                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['UEBERMITTLUNG_RUFNR'] = 'J';

                $cnt = 0;
                $bool_check_phonesend = true;
                while ($row = ibase_fetch_object($result)) {
                    $cnt++;
                    $cntProducts++;

                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['DIENST_BEZEICHNUNG'] = $row->DESCRIPTION;

                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['ACCOUNT_NO'] = $row->ACCOUNTNO;
                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['ACCOUNT_BEZEICHNUNG'] = $row->ADESCRIPTION;


                    // Ist das aktuelle Produkt ein Telefonprodukt?
                    if (in_array($row->ACCOUNTNO, $this->SET_ACCOUNT_ID_FOR_PHONE[$this->getGroupType])){

                        // 0900 Sperre?
                        // if ($row->PRODUCT_ID == '10003')
                           //  $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['SPERRE_0900'] = 'J';

                        // TODO ... 0900 Sperren setze ich hier per Hardcode auf J ... Aussae Lars K. 9.3.2016 ... Bestätigung Alex
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['SPERRE_0900'] = 'J';

                        // UEBERMITTLUNG_RUFNR prüfen solange bis ich eine Aussage dazu gefunden habe
                        if ($bool_check_phonesend) {

                            // Default Wert ... Rufnummer übermitteln
                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['UEBERMITTLUNG_RUFNR'] = 'J';

                            if ($row->PRODUCT_ID == '1002') {
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['UEBERMITTLUNG_RUFNR'] = 'N';
                                $bool_check_phonesend = false;
                            }
                        }

                    }

                }

                ibase_free_result($result);

            }
        }

        // Status festhalten:
        $this->globalMessage['Status'][] = $cntProducts . ' Product IDs wurden ermittelt!';

        return true;

    }   // END private function getProductsByContractID()














    // Contracts einlesen die zu den ausgewählten Customer gehören
    private function getContractsByCustomeerID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalData['fromDB']['CUSTOMER']['CUSTOMER_ID']))
            return false;

        $cntContracts = 0;
        //$x = 0;
        foreach ($this->globalData['fromDB']['CUSTOMER']['CUSTOMER_ID'] as $index=>$curCustomerID){

            $query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '".$curCustomerID."' ORDER BY CO_ID";
            $result = ibase_query($this->dbF, $query);

            $cnt = 0;
            while ($row = ibase_fetch_object($result)) {

                //$x++;
                //echo $x . " ". $row->CO_ID . " " . $row->DATE_SIGNED . "<br>";

                // Nur unterzeichnete Daten übermitteln
                if ($this->SET_TRANSFER_ONLY_SIGNED_DATA == 'yes')
                {
                    if ($row->DATE_SIGNED > 1) {
                        $cnt++;
                        $cntContracts++;

                        // Daten dem Ziel-Datensatz hinzufügen
                        $this->addTarget($curCustomerID, $row->CO_ID, 'CO_ID',              $row->CO_ID);
                        $this->addTarget($curCustomerID, $row->CO_ID, 'DIENST_ART',         $this->getGroupType);
                        $this->addTarget($curCustomerID, $row->CO_ID, 'NAT_BETREIBEREBENE', 'N');
                        $this->addTarget($curCustomerID, $row->CO_ID, 'GUELTIG_VON',        $this->getFormatDate($row->DATE_ACTIVE));
                        $this->addTarget($curCustomerID, $row->CO_ID, 'GUELTIG_BIS',        $this->getFormatDate($row->DATE_DEACTIVE));
                        $this->addTarget($curCustomerID, $row->CO_ID, 'ERFASST_AM',         $this->getFormatDate($row->DATE_CREATED));
                        $this->addTarget($curCustomerID, $row->CO_ID, 'UNTERZEICHNET_AM',   $this->getFormatDate($row->DATE_SIGNED));
                    }
                }
                else {
                    $cnt++;
                    $cntContracts++;

                    // Daten dem Ziel-Datensatz hinzufügen
                    $this->addTarget($curCustomerID, $row->CO_ID, 'CO_ID',              $row->CO_ID);
                    $this->addTarget($curCustomerID, $row->CO_ID, 'DIENST_ART',         $this->getGroupType);
                    $this->addTarget($curCustomerID, $row->CO_ID, 'NAT_BETREIBEREBENE', 'N');
                    $this->addTarget($curCustomerID, $row->CO_ID, 'GUELTIG_VON',        $this->getFormatDate($row->DATE_ACTIVE));
                    $this->addTarget($curCustomerID, $row->CO_ID, 'GUELTIG_BIS',        $this->getFormatDate($row->DATE_DEACTIVE));
                    $this->addTarget($curCustomerID, $row->CO_ID, 'ERFASST_AM',         $this->getFormatDate($row->DATE_CREATED));
                    $this->addTarget($curCustomerID, $row->CO_ID, 'UNTERZEICHNET_AM',   $this->getFormatDate($row->DATE_SIGNED));
                }

            }

            ibase_free_result($result);
        }

        // Status festhalten:
        $this->globalMessage['Status'][] = $cntContracts . ' Contracts IDs wurden ermittelt!';


        // Speicher für globalData freigeben!
        $this->globalData = '';


        return true;

    }   // END private function getContractsByCustomeerID()









    // Fügt einen Datensatz der Target-Variable hinzu
    private function addTarget($curCustomerID, $curCOID, $field, $value)
    {

        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curCOID][$field]  = $value;

        return true;

    }











    // Customer einlesen die in der angegebenen GruppenID enthalten sind
    private function getCustomerByGroupID()
    {

        // Benötigte Daten vorhanden?
        if ( (!isset($this->getGroupType)) || (strlen($this->getGroupType) < 1) )
            return false;


        // Dynamische Gruppen WHERE - Abfrage
        $bool = false;
        $add = '';
        foreach ($this->SET_GROUP_ID_BY_TYPE[$this->getGroupType] as $curGroupID){

            if (!$bool)
                $add .= " WHERE GROUP_ID = '".$curGroupID."' ";
            else
                $add .= " OR GROUP_ID = '".$curGroupID."' ";

            $bool = true;

        }

        // Nur Customer einlesen die den entsprechenden Status haben
        foreach ($this->SET_ACCOUNT_BY_STATUS_ID[$this->getGroupType] as $curStatusID) {
            $add .= " AND STATUS_ID != '".$curStatusID."' ";
        }

            // Nur ein Beispielkunde?
        if ( (isset($this->SET_EXAMPLE_CUSTOMER_ID)) && ($this->SET_EXAMPLE_CUSTOMER_ID > 0) ){
            $add .= " AND CUSTOMER_ID = '".$this->SET_EXAMPLE_CUSTOMER_ID."' ";
            $query = "SELECT * FROM CUSTOMER ". $add . " ORDER BY CUSTOMER_ID";
        }
        else
            $query = "SELECT * FROM CUSTOMER " . $add . " ORDER BY CUSTOMER_ID";


        $result = ibase_query($this->dbF, $query);

        $cnt = 0;
        while ($row = ibase_fetch_object($result)) {
            $cnt++;

            $this->globalData['fromDB']['CUSTOMER']['CUSTOMER_ID'][] = $row->CUSTOMER_ID;
        }

        ibase_free_result($result);

        // Status festhalten:
        $this->globalMessage['Status'][] = $cnt . ' Customer IDs wurden ausgewählt!';

        return true;

    }   // END private function getCustomerByGroupID()

























    // Dimari Datenbankverbindung herstellen
    private function createDimariDBConnection()
    {

        $host = $this->host;
        $username = $this->username;
        $password = $this->password;

        if (!($dbF=ibase_pconnect($host, $username, $password, 'ISO8859_1', 0, 3)))
            die('Could not connect: ' .  ibase_errmsg());

        $this->dbF = $dbF;

        // Status festhalten:
        $this->globalMessage['Status'][] = 'DB Verbindung Done!';

        return true;

    }   // END private function createDimariDBConnection()






    private function getFormatDate($getDate=null)
    {

        if (strlen($getDate) > 0)
            $getDate = date("d.m.Y ", strToTime($getDate));

//        else
//            $getDate = '';

        return $getDate;

    }   // END private function getFormatDate($getDate)





}   // END class Dimari