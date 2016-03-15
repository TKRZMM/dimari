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
    private $setCustomerByGroupID = array('FTTC' => array('100011'),
                                            'FTTH' => array()
                                            );



    // Customer einlesen die NICHT StatusID x haben
    // Tabelle: CUSTOMER_STATUS
    // Feld:    STATUS_ID
    private $setNoCustomerInStatusID = array('FTTC' => array(),
                                                'FTTH' => array()
                                                    );
//    private $etNoCustomerInStatusID = array('FTTC' => array('10004', '2'),
//                                                'FTTH' => array()
//                                            );


    // Nummern für Produkte die für Internet wichtig sind ... u.a. nur diese Daten werden gezogen
    private $setProductIDForInternet = array ('FTTC' => array('10070','10059',),
                                            'FTTH' => array()
                                            );

    // Nummern für Produkte die für VOIP wichtig sind ... u.a. nur diese Daten werden gezogen
    private $setProductIDForVOIP = array ('FTTC' => array('10033','10004',),
                                            'FTTH' => array()
                                            );



    // Message Handler
    private $myMessage = array('Info' => array(),
                                'Runtime' => array()
                               );





    // Globaler HAUPT - Handler für Datenverarbeitung
    public $globalData = array();
    public $globalDataTMP = array();


    // Export Typ wird hier gespeichert (FTTC oder FTTH)
    // !!! Muss ausserhalb der Klasse gesetzt werden
    public $setExportType;



    // Datenbank Variable ... werden durch den Construktor gesetzt
    private $myHost;
    private $myUsername;
    private $myPassword;

    // Datenbank Object
    private $dbF;



    // Kunden ohne Vertrag aber aktiv (Details) ausgeben?
    private $setFTTHtoFTTCWrongCustomerOnScreen = 'no';



    // Falsch zugewiesene FTTH zu FTTC Kunden (Details) ausgeben?
    private $setNoContractCustomerOnScreen = 'no';



    // Wie viele Customer sollen eingelesen werden?
    // 0 für keine Einschränkung beim Limit
    private $setReadLimitCustomer = 0;







    //private $onlyExampleCustomerID = '20010120';        // Matthias Brumm(!) ... Chaos

    //private $onlyExampleCustomerID = '20010003';        // FTTC Gruppe ist aber FTTH!!!
    //private $onlyExampleCustomerID = '20010686';        // Kein VOIP Username - Passwort ... Vertrag nicht unterschrieben!

    //private $onlyExampleCustomerID = '20010190';        // Vertrag in Zukunft ... nicht unterzeichnet(!) bzw. Portierung bestätigt 4 VOIP

    // private $onlyExampleCustomerID = '20010003';       // Standard Kunde 1 VOIP
    //private $onlyExampleCustomerID = '20010612';        // Standard Kunde 2 VOIP
    //private $onlyExampleCustomerID = '20010603';        // Standard Kunde 3 VOIP ... mit Telefonbuch - Eintrag in der Sub-Auswahl












    // Klassen - Konstruktor
    public function __construct($host, $username, $password)
    {
        $this->myHost     = $host;
        $this->myUsername = $username;
        $this->myPassword = $password;
    }   // END public function __construct(...)





    // Default Name - Methode

    public function myName($out=false)
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
        if (!$this->setExportType){
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






//        echo "<pre>";
//        print_r($this->myMessage);
//        echo "</pre><br>";


        $this->outNow('Ende', '', 'Info');

        return true;

    } // END public function initialGetFTTCServices()




















    // Products einlesen die zu den Contracts gehören
    private function getProductsByContractID()
    {


        // Einschränkung bei Internet?
        $addInternet = '';
        if ( (isset($this->setProductIDForInternet[$this->setExportType])) && (count($this->setProductIDForInternet[$this->setExportType])> 0) ){

            $addInternet .= " AND (";

            $bool = true;
            foreach ($this->setProductIDForInternet[$this->setExportType] as $index=>$curProductID){

                if ($bool)
                    $addInternet .= " p.PRODUCT_ID = '".$curProductID."' ";
                else
                    $addInternet .= " OR p.PRODUCT_ID = '".$curProductID."' ";

                $bool = false;

                $this->addMessage('Einschränkung: Internet nur Produkt ID', $curProductID, 'Info');
            }

        }




        // Einschränkung bei Telefon?
        $addPhone = $addInternet;
        if ( (isset($this->setProductIDForVOIP[$this->setExportType])) && (count($this->setProductIDForVOIP[$this->setExportType])> 0) ){


            // Wenn ich keine Einschränkung bei Internet habe... Flag hier richtig setzen
            if (strlen($addPhone) < 1){
                $addPhone .= " AND (";
                $bool = true;
            }

            foreach ($this->setProductIDForVOIP[$this->setExportType] as $index=>$curProductID){

                if ($bool)
                    $addPhone .= " p.PRODUCT_ID = '".$curProductID."' ";
                else
                    $addPhone .= " OR p.PRODUCT_ID = '".$curProductID."' ";

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
        foreach ($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID=>$curCustomerArray){

            $boolGotData = false;


            // Durchlauf Vertrag
            foreach ($curCustomerArray['CONTRACT_ID'] as $curContractID=>$curContractArray){

                // DATEN EBENE!!!

                $add = " WHERE cop.CO_ID = '".$curContractID."' ";


                // Einschränkungen Telefon und Intertent Product_ID
                $add .= $addPhone;


                $query = "SELECT cop.CO_ID          AS CO_ID,
                                 cop.CO_PRODUCT_ID  AS CO_PRODUCT_ID,
                                 p.DESCRIPTION      AS DESCRIPTION,
                                 p.PRODUCT_ID       AS PRODUCT_ID,
                                 a.ACCOUNTNO        AS ACCOUNTNO,
                                 a.DESCRIPTION      AS ADESCRIPTION
                            FROM CO_PRODUCTS cop
                              LEFT JOIN PRODUCTS p  ON p.PRODUCT_ID  = cop.PRODUCT_ID
                              LEFT JOIN ACCOUNTS a  ON a.ACCOUNTNO   = p.ACCOUNTNO
                            ".$add."
                            ORDER BY cop.CO_PRODUCT_ID";


                $result = ibase_query($this->dbF, $query);

                $boolGotProductID = false;
                while ($row = ibase_fetch_object($result)) {

                    $cntProducts++;

                    //echo "Product: " .$row->DESCRIPTION . "<br>";

                    $boolGotProductID = true;
                    if ($row->PRODUCT_ID < 1){
                        echo "hier";
                        exit;

                    }


                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_Name'] = $row->DESCRIPTION;
                    /*
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
                    $this->globalData['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$curContractID]['PRODUCT_ID'][$row->PRODUCT_ID]['PRODUCT_ID'] = $row->PRODUCT_ID;
*/

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
            if(!$boolGotData){
                $cntFailFTTHIsFTTC++;
                if ( (isset($this->setFTTHtoFTTCWrongCustomerOnScreen)) && ($this->setFTTHtoFTTCWrongCustomerOnScreen == 'yes') )
                    $this->addMessage('FTTH auf FTTC eingestellt KdNr.',$curCustomerID, 'Alert');
            }

        }


        // Alert Message bei falsch zugewiesene Kunden (FFH ist auf FTTC)
        if ($cntFailFTTHIsFTTC > 0)
            $this->addMessage('FTTH auf FTTC eingestellt x mal',$cntFailFTTHIsFTTC, 'Alert');


        $cntExpCustomer = count($this->globalData['CUSTOMER_ID_Array']);

        $this->addMessage('&sum; Ermittelte Produkte ',$cntProducts, 'Info');
        $this->addMessage('&sum; Ermittelte Produkte ',$cntProducts, 'Sum');
        $this->addMessage('&sum; Exportfähige Kunden ',$cntExpCustomer, 'Sum');

        return true;

    }   // END private function getProductsByContractID()



















































    // Contracts einlesen die zu den ausgewählten Customer gehören
    private function getContractsByCustomerID()
    {

        $boolOnlySignedContracts = false;

        // Nur unterzeichnete Vertäge einlesen?
        if ( (isset($this->setReadOnlySignedContracts)) && ($this->setReadOnlySignedContracts == 'yes') ){
            $this->addMessage('Einschränkung: Nur unterzeichnete Verträge', 'ja', 'Info');
            $boolOnlySignedContracts = true;
        }


        $cntContracts = 0;
        $cntCustomerHasNoContract = 0;

        // Durchlauf Customer_ID
        foreach ($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID=>$curCustomerArray){

            $cntContractsPerCustomer = 0;

            $add = '';  // Query Abfrage - Zusatz

            $query = "SELECT * FROM CONTRACTS WHERE CUSTOMER_ID = '".$curCustomerID."' AND STATUS_ID > '0' " . $add . " ORDER BY CO_ID";

            $result = ibase_query($this->dbF, $query);

            // Gibt es gültige Verträge zu dem Customer?
            if ($this->ibase_num_rows($result)<1){
                $cntCustomerHasNoContract++;

                if ( (isset($this->setNoContractCustomerOnScreen)) && ($this->setNoContractCustomerOnScreen == 'yes') )
                    $this->addMessage('Kein Vertag KdNr.',$curCustomerID, 'Alert');

                continue;
            }

            ibase_free_result($result);

            $result = ibase_query($this->dbF, $query);

            while ($row = ibase_fetch_object($result)) {
                $cntContracts++;
                $cntContractsPerCustomer++;

                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CUSTOMER_ID']        = $curCustomerID;
                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['CONTRACT_ID']        = $row->CO_ID;

                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['GUELTIG_VON']        = $this->getFormatDate($row->DATE_ACTIVE);
                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['GUELTIG_BIS']        = $this->getFormatDate($row->DATE_DEACTIVE);
                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['ERFASST_AM']         = $this->getFormatDate($row->DATE_CREATED);
                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['UNTERZEICHNET_AM']   = $this->getFormatDate($row->DATE_SIGNED);

                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['DATE_ACTIVE_REQ']    = $this->getFormatDate($row->DATE_ACTIVE_REQ);
                $this->globalDataTMP['CUSTOMER_ID_Array'][$curCustomerID]['CONTRACT_ID'][$row->CO_ID]['STATUS_ID']          = $row->STATUS_ID;

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

        // $setNoContractCustomerOnScreen
        $this->addMessage('Kein Vertag für Kunde x mal',$cntCustomerHasNoContract, 'Alert');

        return true;

    }   // END private function getContractsByCustomeerID()





























    // Customer einlesen die in der angegebenen GruppenID enthalten sind
    private function getCustomerByGroupID()
    {

        $add = '';

        // Nur Customer einlesen die in der Gruppe x sind?
        if ( (isset($this->setCustomerByGroupID[$this->setExportType])) && (count($this->setCustomerByGroupID[$this->setExportType])>0) ) {
            $bool = false;
            foreach ($this->setCustomerByGroupID[$this->setExportType] as $curGroupID) {

                if (!$bool)
                    $add .= " WHERE GROUP_ID = '" . $curGroupID . "' ";
                else
                    $add .= " OR GROUP_ID = '" . $curGroupID . "' ";

                $bool = true;

                $this->addMessage('Einschränkung: Nur Gruppen_ID', $curGroupID, 'Info');

            }
        }
        else
            $add .= " WHERE CUSTOMER_ID > '1' ";



        // Nur Customer einlesen die NICHT Status x haben?
        if ( (isset($this->setNoCustomerInStatusID[$this->setExportType])) && (count($this->setNoCustomerInStatusID[$this->setExportType])>0) ){
            foreach ($this->setNoCustomerInStatusID[$this->setExportType] as $curStatusID) {
                $add .= " AND STATUS_ID != '".$curStatusID."' ";

                $this->addMessage('Einschränkung: Nicht Status_ID', $curStatusID, 'Info');
            }
        }



        // Nur Customer einlesen die Kundennummer x besitzen?
        if ( (isset($this->onlyExampleCustomerID)) && ($this->onlyExampleCustomerID > 0) ){

            $this->addMessage('Einschränkung: Nur KdNr.', $this->onlyExampleCustomerID, 'Info');

            $add .= " AND CUSTOMER_ID = '".$this->onlyExampleCustomerID."' ";

        }


        // Limitierung gesetzt?
        if ( (isset($this->setReadLimitCustomer)) && ($this->setReadLimitCustomer > 0)){

            $addFirst = 'FIRST ' . $this->setReadLimitCustomer . ' SKIP 0 ';
            $this->addMessage('Einschränkung: Limit Kunden', $this->setReadLimitCustomer, 'Info');

        }
        else
            $addFirst = '';



        $query = "SELECT ".$addFirst ." * FROM CUSTOMER " . $add . " ORDER BY CUSTOMER_ID";


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
    private function flushByFunctionCall($getFunction, $hExp=false)
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
        if (!$callBy->$getFunction()){
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
    private function createDimariDBConnection()
    {

        $host       = $this->myHost;
        $username   = $this->myUsername;
        $password   = $this->myPassword;

        if (!($dbF=ibase_pconnect($host, $username, $password, 'ISO8859_1', 0, 3)))
            die('Could not connect: ' .  ibase_errmsg());

        $this->dbF = $dbF;

        // Status
        //$this->outNow('DB Verbindung!', 'OK', 'Info');

        return true;

    }   // END private function createDimariDBConnection()



    // Ibase num_rows
    private function ibase_num_rows($result)
    {
        $myResult = $result;

        $cnt = 0;

        while ($row = @ibase_fetch_row($myResult))
            $cnt++;

        return $cnt;

    }   // END private function ibase_num_rows(...)



    // Datum passend formatieren
    private function getFormatDate($getDate=null)
    {

        if (strlen($getDate) > 0)
            $getDate = date("d.m.Y ", strToTime($getDate));

        return $getDate;

    }   // END private function getFormatDate(...)


    ////////////////////////// END DIVERSE BLOCK ///////////////////////////////////









    ////////////////////////// START MESSAGE BLOCK ///////////////////////////////////


    // Status hinzufügen und jetzt ausgeben
    private function outNow($messageValue, $messageStatus='unset', $messageType='Runtime')
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
    private function showStatus()
    {

        $cntCategorie = 0;

        print ('<div style="position: fixed; overflow: auto; top: 5px; bottom: 20px; min-width: 600px; right: 5px; background-color: beige"><table>');

        // Durchlauf der Message - Kategorien
        foreach ($this->myMessage as $messageType=>$curMessageArray){

            // Sind Einträge in der Kategorie vorhanden?
            if (count($curMessageArray) > 0){

                $cntCategorie++;

                // Kategorienamen ausgeben
                if ($cntCategorie > 1)
                    print ('<tr><th colspan="3" style="padding-top: 30px;">'.$messageType.'</th></td></tr>');
                else
                    print ('<tr><th colspan="3">'.$messageType.'</th></td></tr>');


                // SpaltenTyp ausgeben:
                print ('<tr><td style="min-width: 40px;" class="bottomLine">Cnt</td><td style="min-width: 260px;" class="bottomLine">Event</td><td class="bottomLine">Status</td></tr>');

                foreach ($curMessageArray['messageValue'] as $fieldIndex=>$value){

                    print ('<tr><td class="bottomLine">');

                    $infoCnt = '# ' . ($fieldIndex + 1);
                    print ($infoCnt);


                    print ('</td><td class="bottomLine">');
                    print ($value);
                    print ('</td>');


                    // Wenn der Status der aktuelle Message gesetzt ist ... und er nicht 'unset' ist... dann ausgeben
                    if ( (isset($curMessageArray['messageStatus'][$fieldIndex])) && ($curMessageArray['messageStatus'][$fieldIndex] != 'unset') )
                        print ('<td class="bottomLine">'.$curMessageArray['messageStatus'][$fieldIndex].'</td>');
                    else
                        print ('<td class="bottomLine">&nbsp;</td>');

                    print ('</tr>');

                }

            }

        }

        print ('</table></div>');

    }   // END private function showStatus()





    // Hänge ein Message an die ggf. schon bestehenden Messages
    private function addMessage($messageValue, $messageStatus='unset', $messageType='Runtime')
    {

        $this->myMessage[$messageType]['messageValue'][]  = $messageValue;
        $this->myMessage[$messageType]['messageStatus'][] = $messageStatus;

        return true;

    }   // END private function addMessage($messageType, $messageValue)


    ////////////////////////// END MESSAGE BLOCK ///////////////////////////////////









    
}   // END class Dimari
