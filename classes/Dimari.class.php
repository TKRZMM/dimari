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


    // Gruppen-Typ der verarbeitet werden soll ... steuert $SET_GROUP_ID_BY_TYPE
    public $getGroupType;

    // Gruppen-Typ der verarbeitet werden soll ... steuert $SET_ACCOUNT_ID_BY_TYPE
    public $getAccountType;

    // Datenbank - Verbindung zu Dimari
    private $dbF;

    // Group IDs für diesen Export:
    private $SET_GROUP_ID_BY_TYPE = array('FTTC' => array('100011'),
                                          'FTTH' => array('100005','100006','100007','100008','100002','100001'));


    // AccountNo IDs für diesen Export: (Tabelle ACCOUNTS)
    private $SET_ACCOUNT_ID_BY_TYPE = array('FTTC' => array('85200','85300','85600'),
                                            'FTTH' => array(''));


    // Nur unterzeichnete Vertärge exportieren?
    private  $SET_TRANSFER_ONLY_SIGNED_DATA = 'yes';


    private $SET_PHONE_BOOK_ENTRY_IDS = array('10002', '10001');

    //private $SET_EXAMPLE_CUSTOMER_ID = '20010190';
    private $SET_EXAMPLE_CUSTOMER_ID = '20010120';  // Matthias Brumm(!)


    public function __construct()
    {

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

        // Dimari DB Verbindung herstellen
        if (!$this->createDimariDBConnection())
            return false;


        // Customer einlesen die in der angegebenen GruppenID enthalten sind
        if (!$this->getCustomerByGroupID())
            return false;


        // Contracts einlesen die zu den ausgewählten Customer gehören
        if (!$this->getContractsByCustomeerID())
            return false;


        // CO_Products einlesen die zu den Contracts gehören
        if (!$this->getProductsByContractID())
            return false;


        // VOIP Daten einlesen
        if (!$this->getCOVoicedataByCOID())
            return false;


        // Telefonbuch Einträge? ...
        // Die Telefonnummer die ggf. eingetragen wird kommt aus der Subscriber... weiter unten!!!
        if (!$this->getPhoneBookEntrysByCustomerID())
            return false;


        // VOIP - Telefonnummern einlesen
        if (!$this->getSubscriberByCOVID())
           return false;



    }









    // VOIP - Telefonnummern einlesen
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
                // echo "ContractID: $curContractID ---> <br>";


                // Durchlauf Voice Data
                foreach ($someDataArray['COV_ID'] as $index=>$curCOVID)
                {

                    $query = "SELECT * FROM SUBSCRIBER WHERE COV_ID = '".$curCOVID."' ORDER BY SUBS_ID, DISPLAY_POSITION";
                    $result = ibase_query($this->dbF, $query);


                    $cntCurSubscriber = 0;
                    while ($row = ibase_fetch_object($result)) {
                        $cntCurSubscriber++;

                        // Telefonnummer für Telefonbucheintag?
                        if ($row->PHON_BOOK)
                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_TEL'] = $row->SUBSCRIBER_ID;
                    }

                    ibase_free_result($result);

                }

            }

        }


        return true;

    }   // END private function getSubscriberByCOVID()











    // Telefonbuch Einträge?
    private function getPhoneBookEntrysByCustomerID()
    {

        // Benötigte Daten vorhanden?
        if (!isset($this->globalTarget['CUSTOMER_ID']))
            return false;

        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$contractArray) {
            // echo "CustomerID: $curCustomerID --> <br>";

            // Durchlauf Contracts
            foreach ($contractArray['CO_ID'] as $curContractID => $someDataArray) {
                // echo "ContractID: $curContractID ---> <br>";

                if ( (isset($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'])) && ($this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'] > 0) ){

                    // 10001 Standardeintrag
                    // 10002 NUR Name und Rufnr.
                    // Daten aus CUSTOMER_ADRESSES einlesen

                    $query = "SELECT * FROM CUSTOMER_ADDRESSES WHERE CUSTOMER_ID = '".$curCustomerID."' AND ADDRESS_TYPE_ID > '0' ORDER BY ADDRESS_ID";

                    $result = ibase_query($this->dbF, $query);

                    $cnt = 0;
                    $bool = false;

                    $curType = $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'];
                    while ($row = ibase_fetch_object($result)) {

                        // Haben wir eine echte Telefonbuch-Adresse?
                        if ($row->ADDRESS_TYPE_ID == '10011'){
                            // Habe echte Telefonbucheinträge und nehme die Daten

                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_NACHNAME'] = $row->NAME;

                            // Nur wenn alle Daten gewünscht sind diese auch setzen
                            if ($curType == '10001'){
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_VORNAME'] = $row->FIRSTNAME;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_STRASSE'] = $row->STREET . ' ' . $row->HOUSENO . ' ' . $row->HOUSENO_SUPPL;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_PLZ'] = $row->CITYCODE;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_ORT'] = $row->CITY;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_FAX'] = $row->FAX;
                            }
                            else {
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_VORNAME'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_STRASSE'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_PLZ'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_ORT'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_FAX'] = '';
                            }

                            // Die Telefonnummer ermittel ich aus der Subscriber!!!
                            /*
                            $phone = '';
                            if (strlen($row->PHONE) > 0)
                                $phone = $row->PHONE;
                            elseif (strlen($row->PHONE2) > 0)
                                $phone = $row->PHONE2;
                            elseif (strlen($row->CELL_PHONE) > 0)
                                $phone = $row->CELL_PHONE;
                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_TEL'] = $phone;
                            */

                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'] = 'J';

                            $bool = true;
                            break;
                        }


                        // Habe Anschlussinhaber und nehme die Daten
                        elseif ($row->ADDRESS_TYPE_ID == '10010'){
                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_NACHNAME'] = $row->NAME;

                            // Nur wenn alle Daten gewünscht sind diese auch setzen
                            if ($curType == '10001'){
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_VORNAME'] = $row->FIRSTNAME;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_STRASSE'] = $row->STREET . ' ' . $row->HOUSENO . ' ' . $row->HOUSENO_SUPPL;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_PLZ'] = $row->CITYCODE;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_ORT'] = $row->CITY;
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_FAX'] = $row->FAX;
                            }
                            else {
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_VORNAME'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_STRASSE'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_PLZ'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_ORT'] = '';
                                $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_FAX'] = '';
                            }


                            // Die Telefonnummer ermittel ich aus der Subscriber!!!
                            /*
                            $phone = '';
                            if (strlen($row->PHONE) > 0)
                                $phone = $row->PHONE;
                            elseif (strlen($row->PHONE2) > 0)
                                $phone = $row->PHONE2;
                            elseif (strlen($row->CELL_PHONE) > 0)
                                $phone = $row->CELL_PHONE;
                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCH_TEL'] = $phone;
                            */

                            $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'] = 'J';

                            $bool = true;
                        }
                    }

                    ibase_free_result($result);

                    if (!$bool)
                        $this->globalData['FehlerBei'][] = $curCustomerID;

                }

            }

        }

        return true;

    }   // END private function getPhoneBookEntrysByCustomerID











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

                $query = "SELECT * FROM CO_VOICEDATA WHERE CO_ID = '".$curContractID."' AND STATUS_ID > '0' ORDER BY CO_ID";
                $result = ibase_query($this->dbF, $query);

                $cnt = 0;
                while ($row = ibase_fetch_object($result)) {
                    $cnt++;
                    $cntVoiceData++;

                    if ($row->ANONYMISATION == 1)
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['EGN_VERFREMDUNG'] = 'J';
                    else
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['EGN_VERFREMDUNG'] = 'N';

                    //echo "...." . $query . "<br>";

                    if (in_array($row->PHONE_BOOK_ENTRY_ID, $this->SET_PHONE_BOOK_ENTRY_IDS))
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'] = $row->PHONE_BOOK_ENTRY_ID;
                    else
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['TELEFONBUCHEINTRAG'] = '';

                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['COV_ID'][] = $row->COV_ID;
                }

                ibase_free_result($result);
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
                foreach ($this->SET_ACCOUNT_ID_BY_TYPE[$this->getAccountType] as $curACCOUNTNO){

                    if (!$bool)
                        $add .= " WHERE cop.CO_ID = '".$curContractID."' AND (p.ACCOUNTNO = '".$curACCOUNTNO."' ";
                    else
                        $add .= " OR p.ACCOUNTNO = '".$curACCOUNTNO."' ";

                    $bool = true;

                }
                $add .= ')';

                $add = " WHERE cop.CO_ID = '".$curContractID."' ";

                $query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID
                            FROM CO_PRODUCTS cop
                              LEFT JOIN PRODUCTS p ON p.PRODUCT_ID = cop.PRODUCT_ID
                            ".$add."
                            ORDER BY cop.CO_PRODUCT_ID";

                $result = ibase_query($this->dbF, $query);

                $cnt = 0;
                while ($row = ibase_fetch_object($result)) {
                    $cnt++;
                    $cntProducts++;

                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['DIENST_BEZEICHNUNG'] = $row->DESCRIPTION;

                    // 0900 Sperre?
                    if ($row->PRODUCT_ID == '10003')
                        $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['SPERRE_0900'] = 'J';

                    // TODO ... 0900 Sperren setze ich hier per Hardcode auf J
                    $this->globalTarget['CUSTOMER_ID'][$curCustomerID]['CO_ID'][$curContractID]['SPERRE_0900'] = 'J';

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
        foreach ($this->globalData['fromDB']['CUSTOMER']['CUSTOMER_ID'] as $index=>$curCustomerID){

            $query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '".$curCustomerID."' ORDER BY CO_ID";
            $result = ibase_query($this->dbF, $query);

            $cnt = 0;
            while ($row = ibase_fetch_object($result)) {

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
                        $this->addTarget($curCustomerID, $row->CO_ID, 'SPERRE_0900',        'N');
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
                    $this->addTarget($curCustomerID, $row->CO_ID, 'SPERRE_0900',        'N');
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

        // Nur ein Beispielkunde?
        if ( (isset($this->SET_EXAMPLE_CUSTOMER_ID)) && ($this->SET_EXAMPLE_CUSTOMER_ID > 0) )
            $query = "SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = '".$this->SET_EXAMPLE_CUSTOMER_ID."' ORDER BY CUSTOMER_ID";
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

        $host = '192.168.235.2:E:\variobill\production_tkrz\data\TKRZ_VARIOBILL.FDB';
        $username = 'SYSDBA';
        $password = 'Guiffez9';

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