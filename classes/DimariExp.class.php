<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 10.03.2016
 * Time: 15:32
 */
class DimariExp extends Dimari
{
//    public $globalData = array();

    public $globalOut = array();        // Vor Auslieferung




    // Klassen - Konstruktor
    public function __construct($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi)
    {
        parent::__construct($host, $username, $password, $hostRadi, $usernameRadi, $passwordRadi);

    }   // END public function __construct(...)





    // Initial Methode!!
    public function initialDimariExport()
    {

        //$this->mainToExcel();
        $this->flushByFunctionCall('mainToExcel');


        // globalDataTMP brauche icht mehr... gebe Speicher frei:
        $this->globalData = $this->globalDataTMP;
        $this->globalDataTMP = '';




//        echo "<pre>";
//        print_r($this->globalData);
//        echo "</pre><br>";

        return true;

    }   // END public function initialDimariExport()





    // Haupt - Methode für den Excelexport
    public function mainToExcel()
    {

        // Cnt
        $cntCustomerOnStart = 0;
        $cntContractOnStart = 0;
        $cntProductOnStart = 0;
        $cntCovIDOnStart = 0;
        $cntSubIDOnStart = 0;


        // Durchlauf Customer
        foreach ($this->globalData['CUSTOMER_ID_Array'] as $curCustomerID => $customerArray){

            $cntSubOnSingleCustomer = 0;


            $cntCustomerOnStart++;
            // echo "$curCustomerID<br>";


            // Neue Datenzeile erzeugen
            $curDataSet = array();
            unset ($curDataSet);


            // Setze Kundennummer
            $curDataSet['CUSTOMER_ID'][$curCustomerID]['KUNDEN_NR'] = $curCustomerID;


            // Setze Dienstart (FTTC oder FTTH)
            $curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_ART'] = $this->setExportType;


            // Setze STANDORT
            // HARDCODE
            $curDataSet['CUSTOMER_ID'][$curCustomerID]['STANDORT'] = 'KD';


            // Durchlauf Contract
            foreach ($customerArray['CONTRACT_ID'] as $curContractID => $contractArray){

                $cntContractOnStart++;
                // echo "$curContractID<br>";


                // DATEN EBENE CONTRACT


                // Setze GUELTIG_VON
                if (isset($contractArray['GUELTIG_VON']))
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'] = $contractArray['GUELTIG_VON'];
                else
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'] = '';



                // Setze GUELTIG_BIS
                if (isset($contractArray['GUELTIG_BIS']))
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_BIS'] = $contractArray['GUELTIG_BIS'];
                else
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_BIS'] = '';



                // Setze ERFASST_AM
                if (isset($contractArray['ERFASST_AM']))
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['ERFASST_AM'] = $contractArray['ERFASST_AM'];
                else
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['ERFASST_AM'] = '';



                // Setze UNTERZEICHNET_AM
                if (isset($contractArray['UNTERZEICHNET_AM']))
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['UNTERZEICHNET_AM'] = $contractArray['UNTERZEICHNET_AM'];
                else
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['UNTERZEICHNET_AM'] = '';









                // Durchlauf Products
                if ( (isset($contractArray['PRODUCT_ID'])) && (count($contractArray['PRODUCT_ID']) > 0) ){
                    foreach ($contractArray['PRODUCT_ID'] as $curProductID => $productArray){

                        $cntProductOnStart++;
                        // echo "$curProductID<br>";


                        // DATEN EBEME PRODUCT


                        // VDSL Produkt?
                        if (in_array($curProductID, $this->setProductIDForInternet[$this->setExportType])){

                            // Setze einige Werte für den Export die vornehmlich VDSL bestimmt sind
                            $this->preExcelVDSL($curDataSet, $curCustomerID, $curProductID, $productArray);

                        }



                        // Setze ClientID ... laut S. Reimann ist die gleich der Kundennummer
                        $curDataSet['CUSTOMER_ID'][$curCustomerID]['CLIENT_ID'] = $curCustomerID;


                        // Setze EGN_VERFREMDUNG
                        if ( (isset($productArray['EGN_VERFREMDUNG'])) && ($productArray['EGN_VERFREMDUNG'] == '1') )
                            $curDataSet['CUSTOMER_ID'][$curCustomerID]['EGN_VERFREMDUNG'] = 'J';
                        else
                            $curDataSet['CUSTOMER_ID'][$curCustomerID]['EGN_VERFREMDUNG'] = 'N';



                        // Durchlauf COV_ID
                        if ( (isset($productArray['COV_ID'])) && (count($productArray['COV_ID']) > 0) ){
                            foreach ($productArray['COV_ID'] as $curCOV_ID => $covIDArray){

                                $cntCovIDOnStart++;
                                // echo "$curCOV_ID<br>";


                                // SETZE VOIP_DIENST_BEZEICHNUNG
                                // HARDCODE TKRZ Telefonie
                                if (in_array($curProductID, $this->setProductIDForVOIP[$this->setExportType]))
                                    $curName = 'TKRZ Telefonie';
                                else
                                    $curName = $productArray['PRODUCT_Name'];

                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEZEICHNUNG']   = $curName;
                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_DIENST_BEMERKUNG']      = '';


                                // Setze Ext_Produkt_ID
                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_EXT_PRODUKT_ID']        = $this->setExtProdServiceID['setProductIDForVOIP'];



                                // Durchlauf SUBS_ID
                                if ( (isset($covIDArray['SUBS_ID'])) && (count($covIDArray['SUBS_ID']) > 0) ){
                                    foreach ($covIDArray['SUBS_ID'] as $curSUBS_ID => $subsIDArray){

                                        $cntSubIDOnStart++;
                                         // echo "$curSUBS_ID<br>";


                                        // DATEN EBENE SUBS


                                        // VOIP_PORT_TERMIN
                                        if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'])){

                                            if (isset($subsIDArray['VOIP_PORT_TERMIN']))
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = $subsIDArray['VOIP_PORT_TERMIN'];
                                            else
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = '';
                                        }
                                        else {
                                            if ( (strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN']) < 1) && (isset($subsIDArray['VOIP_PORT_TERMIN'])) )
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'] = $subsIDArray['VOIP_PORT_TERMIN'];
                                        }




                                        // VOIP_PORT_ABG_CARRIER
                                        if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'])){

                                            if (isset($subsIDArray['CARRIER_CODE']))
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = $subsIDArray['CARRIER_CODE'];
                                            else
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = '';

                                        }
                                        else {
                                            if ( (strlen($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER']) < 1) && (isset($subsIDArray['CARRIER_CODE'])) )
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_ABG_CARRIER'] = $subsIDArray['CARRIER_CODE'];
                                        }




                                        // VOIP Rufnummer verarbeiten
                                        $cntSubOnSingleCustomer++;

                                        // VOIP_ACCOUNT_X
                                        $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntSubOnSingleCustomer;
                                        $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_ACCOUNT);


                                        // VOIP_ACCOUNT_PASSWORT_X
                                        $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntSubOnSingleCustomer;
                                        $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_ACCOUNT_PASSWORT);


                                        // VOIP_NATIONAL_VORWAHL__X
                                        $curVOIP_NATIONAL_VORWAHL = 'VOIP_NATIONAL_VORWAHL_' . $cntSubOnSingleCustomer;
                                        $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_NATIONAL_VORWAHL);


                                        // VOIP_KOPFNUMMER__X
                                        $curVOIP_KOPFNUMMERL = 'VOIP_KOPFNUMMER_' . $cntSubOnSingleCustomer;
                                        $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, $curVOIP_KOPFNUMMERL);


                                        // Telefonbucheintrag?
                                        if ( (isset($subsIDArray['TELEFONBUCHEINTRAG'])) && ($subsIDArray['TELEFONBUCHEINTRAG'] == 'J') ){

                                            $curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG'] = 'J';

                                            // Wenn ich noch keine Daten habe... übernehme ich diese
                                            if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCH_NACHNAME'])){


                                                // TELEBUCH_NACHNAME
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_NACHNAME');


                                                // TELEBUCH_VORNAME
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_VORNAME');


                                                // TELEBUCH_STRASSE
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_STRASSE');


                                                // TELEBUCH_PLZ
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_PLZ');


                                                // TELEBUCH_ORT
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_ORT');


                                                // TELEBUCH_TEL
                                                $myNum = $curDataSet['CUSTOMER_ID'][$curCustomerID][$curVOIP_NATIONAL_VORWAHL] . '/' . $curDataSet['CUSTOMER_ID'][$curCustomerID][$curVOIP_KOPFNUMMERL];
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEBUCH_TEL'] = $myNum;


                                                // TELEBUCH_FAX
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_FAX');


                                                // TELEBUCH_SPERRE_INVERS
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_SPERRE_INVERS');


                                                // TELEBUCH_EINTRAG_ELEKT
                                                $this->addDataToDataSetBySubsIDArray($curDataSet, $curCustomerID, $subsIDArray, 'TELEBUCH_EINTRAG_ELEKT');

                                            }
                                        }
                                        else {
                                            if (!isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG']))
                                                $curDataSet['CUSTOMER_ID'][$curCustomerID]['TELEFONBUCHEINTRAG'] = 'N';
                                        }




                                    }
                                }   // END // Durchlauf SUBS_ID




                            }
                        }   // END // Durchlauf COV_ID


                        // Setze alles was wir nicht haben... noch unbekannt... oder leer bleiben kann
                        $this->preExcelAllUnknown($curDataSet, $curCustomerID, $curProductID, $productArray);


                    }
                }   // END // Durchlauf Products



            }   // END // Durchlauf Contract



            // DATEN EBENE CUSTOMER

            // Setze INSTALLATIONSTERMIN
            if (isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN']))
                $curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORT_TERMIN'];
            else{

                if (isset($curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON']))
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = $curDataSet['CUSTOMER_ID'][$curCustomerID]['GUELTIG_VON'];
                else
                    $curDataSet['CUSTOMER_ID'][$curCustomerID]['INSTALLATIONSTERMIN'] = '';
            }



            // Setzte VOIP_PORTIERUNG ... ggf. später noch mit der Excelliste von Sasch überschreibbar
            // Wenn keine SUBS_ID && Excel Feld "O" == ja ... Portierung N
            // Wenn SUBS_ID ... Portierung J
            // Default wird hier gesetzt auf N
            if ($cntSubOnSingleCustomer > 0)
                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'J';
            else
                $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_PORTIERUNG'] = 'N';


            // In globalen Array speichern
            $this->globalDataTMP[$curCustomerID] = $curDataSet;

        }   // END // Durchlauf Customer


        return true;

    }   // END  private function mainToExcel()










    // Fügt Daten dem Datensatz hinzu
    private function addDataToDataSetBySubsIDArray(& $curDataSet, $curCustomerID, $subsIDArray, $fiedlname)
    {

        // Setze Wert
        if (isset($subsIDArray[$fiedlname]))
            $curDataSet['CUSTOMER_ID'][$curCustomerID][$fiedlname] = $subsIDArray[$fiedlname];
        else
            $curDataSet['CUSTOMER_ID'][$curCustomerID][$fiedlname] = '';

        return true;

    } // END private function addDataToDataSet(& $curDataSet, $curCustomerID, $subsIDArray)











    // Setze alles was wir nicht haben... noch unbekannt... oder leer bleiben kann
    // HARDCODE Diverses
    private function preExcelAllUnknown(& $curDataSet, $curCustomerID, $curProductID, $productArray)
    {

        // ROUTER_MODELL
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['ROUTER_MODELL'] = '';

        // ROUTER_SERIEN_NR
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['ROUTER_SERIEN_NR'] = '';

        // ACS_ID
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['ACS_ID'] = '';


        // WIDERRUFEN_AM
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['WIDERRUFEN_AM'] = '';

        // GEKUENDIGT_AM
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['WIDERRUFEN_AM'] = '';


        // SPERRE_0900 ... laut L. Koschin immer sperren
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['SPERRE_0900'] = 'J';

        // UEBERMITTLUNG_RUFNR ... laut L. Koschin immer übermitteln
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['UEBERMITTLUNG_RUFNR'] = 'J';

        // VOIP_SPERRE_AKTIV ... laut L. Koschin nie
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['VOIP_SPERRE_AKTIV'] = 'N';


//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';
//        $curDataSet['CUSTOMER_ID'][$curCustomerID]['xxx'] = '';


        return true;

    }   // END private function preExcelAllUnknown(...)




















    // Setze einige VDSL Werte
    private function preExcelVDSL(& $curDataSet, $curCustomerID, $curProductID, $productArray)
    {

        // Setze Optionen:
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_1'] = '';
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_2'] = '';
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_3'] = '';


        // Setze Dienst-Bezeichnung
        // HARDCODE VDSL4me
        if ($curProductID == '10059'){

            $curName = 'VDSL4me';

            // Energiekunde! ... Trage das unter Optionen ein
            $curDataSet['CUSTOMER_ID'][$curCustomerID]['OPTION_1'] = '21';
        }
        elseif ($curProductID == '10070')
            $curName = 'VDSL4me';
        else
            $curName = $productArray['PRODUCT_Name'];

        $curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_BEZZEICHNUNG']   = $curName;
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['DIENST_BEMERKUNG']      = '';


        // Setze NAT_BETREIBEREBENE
        // HARDCODE
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['NAT_BETREIBEREBENE']      = 'N';


        // Setze Ext_Produkt_ID
        $curDataSet['CUSTOMER_ID'][$curCustomerID]['EXT_PRODUKT_ID']        = $this->setExtProdServiceID['setProductIDForInternet'];

        return true;

    }   // END private function preExcelVDSL(...)








































    private function getCurIndex()
    {
        $curNum = count($this->globalOut);

        $newIndex = $curNum + 1;

        $newIndex = $newIndex - 1;

        return $newIndex;

    }   // END private function getCurIndex()












}   // END class DimariExp

