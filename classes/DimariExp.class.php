<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 10.03.2016
 * Time: 15:32
 */
class DimariExp
{
    public $globalTarget = array();     // Ziel Ausgabe

    public $globalOut = array();        // Vor Auslieferung
    public $globalOut_APPEND = array();
    public $globalOutEND = array();

    public $CNTSubs;


    public function __construct($getGlobalTarget)
    {
        $this->globalTarget = $getGlobalTarget;
    }



    // Initial Methode!!
    public function initialDimariExport()
    {

        $this->mainToExcel();

        $this->writeToExcel();

        return true;

    }   // END public function initialDimariExport()






    private function mainToExcel()
    {

        // Durchlauf Customer
        foreach ($this->globalTarget['CUSTOMER_ID'] as $curCustomerID=>$customerArray){

            // echo "CUSTOMER_ID: $curCustomerID<br>";

            // Durchlauf Contracts -> CO_ID
            foreach ($customerArray['CO_ID'] as $curCO_ID=>$baseDataArray){
                // echo "CO_ID: $curCO_ID <br>";
                // echo " DIENST_ART: " .$baseDataArray['DIENST_ART']."<br>";

                if (isset($baseDataArray['PRODUCT_ID'])){

                    // Durchlauf PRODUCT_ID
                    foreach ($baseDataArray['PRODUCT_ID'] as $curProductID=>$productArray){

                        // VOIP Dienste
                        if (isset($productArray['COV_ID'])){

                            // Durchlauf COV_OD
                            foreach ($productArray['COV_ID'] as $curCOV_ID=>$covArray){

                                // echo "COV_ID: $curCOV_ID<br>";
                                $numIndex = $this->getCurIndex();



                                $this->globalOut[$numIndex]['KUNDEN_NR']           = $curCustomerID;
                                $this->globalOut[$numIndex]['DIENST_ART']          = $baseDataArray['DIENST_ART'];
                                $this->globalOut[$numIndex]['DIENST_BEZEICHNUNG']  = $productArray['DIENST_BEZEICHNUNG'];
                                $this->globalOut[$numIndex]['DIENST_BEMERKUNG']    = '';
                                $this->globalOut[$numIndex]['DATEN_USERNAME']      = 'unset';
                                $this->globalOut[$numIndex]['DATEN_USERPASSWORT']  = 'unset';
                                $this->globalOut[$numIndex]['NAT_BETREIBEREBENE']  = $baseDataArray['NAT_BETREIBEREBENE'];
                                $this->globalOut[$numIndex]['CLIENT_ID']           = 'unset';
                                $this->globalOut[$numIndex]['USERINFO_ID']         = 'unset';
                                $this->globalOut[$numIndex]['ROUTER_MODELL']    = '';
                                $this->globalOut[$numIndex]['ROUTER_SERIEN_NR']    = '';
                                $this->globalOut[$numIndex]['ACS_ID']    = '';
                                $this->globalOut[$numIndex]['EXT_PRODUKT_ID']    = 'unset';
                                $this->globalOut[$numIndex]['OPTION_1']    = 'unset';
                                $this->globalOut[$numIndex]['OPTION_2']    = 'unset';
                                $this->globalOut[$numIndex]['OPTION_3']    = 'unset';
                                $this->globalOut[$numIndex]['GUELTIG_VON']    = $baseDataArray['GUELTIG_VON'];
                                $this->globalOut[$numIndex]['GUELTIG_BIS']    = $baseDataArray['GUELTIG_BIS'];
                                $this->globalOut[$numIndex]['ERFASST_AM']    = $baseDataArray['ERFASST_AM'];
                                $this->globalOut[$numIndex]['UNTERZEICHNET_AM']    = $baseDataArray['UNTERZEICHNET_AM'];
                                $this->globalOut[$numIndex]['WIDERRUFEN_AM']    = '';
                                $this->globalOut[$numIndex]['GEKUENDIGT_AM']    = '';
                                $this->globalOut[$numIndex]['STANDORT']    = 'unset';
                                $this->globalOut[$numIndex]['INSTALLATIONSTERMIN']    = 'unset';
                                $this->globalOut[$numIndex]['HAUPTVERTEILER']    = 'unset';
                                $this->globalOut[$numIndex]['KABELVERZEIGER']    = 'unset';
                                $this->globalOut[$numIndex]['DOPPELADER_1']    = 'unset';
                                $this->globalOut[$numIndex]['DOPPELADER_2']    = 'unset';
                                $this->globalOut[$numIndex]['VOIP_DIENST_BEZEICHNUNG']    = '';
                                $this->globalOut[$numIndex]['VOIP_EXT_PRODUKT_ID']    = '';
                                $this->globalOut[$numIndex]['SPERRE_0900']    = $productArray['SPERRE_0900'];
                                $this->globalOut[$numIndex]['UEBERMITTLUNG_RUFNR']    = 'J';
                                $this->globalOut[$numIndex]['PURTEL_KUNDENUMMER']    = '';
                                $this->globalOut[$numIndex]['PURTEL_HAUPTANSCHLUSS']    = '';
                                $this->globalOut[$numIndex]['VOIP_SPERRE_AKTIV']    = 'N';
                                $this->globalOut[$numIndex]['VOIP_PORTIERUNG']    = 'unset';

                                if (isset($subArray['VOIP_PORT_TERMIN']))
                                    $this->globalOut[$numIndex]['VOIP_PORT_TERMIN']    = $subArray['VOIP_PORT_TERMIN'];
                                else
                                    $this->globalOut[$numIndex]['VOIP_PORT_TERMIN']    = '';


                                if (isset($subArray['CARRIER_CODE']))
                                    $this->globalOut[$numIndex]['VOIP_PORT_ABG_CARRIER']    = $subArray['CARRIER_CODE'];
                                else
                                    $this->globalOut[$numIndex]['VOIP_PORT_ABG_CARRIER'] = '';


                                $this->globalOut[$numIndex]['VOIP_PORT_REST_MSN_KUENDIGEN']    = 'unset';



                                // Durchlauf SUBS_ID
                                if (isset($covArray['SUBS_ID'])){
                                    $cntNumbers = 0;
                                    foreach ($covArray['SUBS_ID'] as $curSubID=>$subArray){
                                        // echo "Sub_ID_: $curSubID<br>";

                                        $cntNumbers++;

                                        // echo $subArray['SUBSCRIBER_ID'] . "<br><br>";

                                        //$numIndex = $this->getCurIndex();

                                        // echo "...... $numIndex<br>";

                                        // Einträge zählen ... wir müssen später auf 10 kommen...
                                        $this->CNTSubs++;


                                        // Wenn mehr als 3 VOIP Nummern, müssen wir die hinten anhängen
                                        if ($cntNumbers > 3){

                                            $globalOut = 'globalOutEND';
                                        }
                                        else {
                                            $globalOut = 'globalOut';
                                        }

                                        $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $cntNumbers;
                                        $this->$globalOut[$numIndex][$curVOIP_ACCOUNT]    = $subArray[$curVOIP_ACCOUNT];




                                        $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $cntNumbers;
                                        $this->$globalOut[$numIndex][$curVOIP_ACCOUNT_PASSWORT]    = $subArray[$curVOIP_ACCOUNT_PASSWORT];

                                        $curVOIP_NATIONAL_VORWAHLT = 'VOIP_NATIONAL_VORWAHLT_' . $cntNumbers;
                                        $this->$globalOut[$numIndex][$curVOIP_NATIONAL_VORWAHLT]    = $subArray[$curVOIP_NATIONAL_VORWAHLT];

                                        $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $cntNumbers;
                                        $this->$globalOut[$numIndex][$curVOIP_KOPFNUMMER]    = $subArray[$curVOIP_KOPFNUMMER];

                                        $curVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $cntNumbers;
                                        $this->$globalOut[$numIndex][$curVOIP_TRANSACTION_NO]    = 'unset';






                                        if (isset($productArray['EGN_VERFREMDUNG'])) {
                                            $this->globalOut_APPEND[$numIndex]['EGN_VERFREMDUNG']    = $productArray['EGN_VERFREMDUNG'];
                                        }
                                        else{
                                            if (!isset($this->globalOut_APPEND[$numIndex]['EGN_VERFREMDUNG']))
                                                $this->globalOut_APPEND[$numIndex]['EGN_VERFREMDUNG']    = '';
                                        }


                                        if (isset($subArray['TELEFONBUCHEINTRAG']))
                                            if ( (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCHEINTRAG'])) || ($this->globalOut_APPEND[$numIndex]['TELEFONBUCHEINTRAG'] == 'N') )
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCHEINTRAG'] = $subArray['TELEFONBUCHEINTRAG'];
                                            else
                                                if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCHEINTRAG']))
                                                    $this->globalOut_APPEND[$numIndex]['TELEFONBUCHEINTRAG']    = '';



                                        if (isset($subArray['TELEFONBUCH_NACHNAME']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_NACHNAME']    = $subArray['TELEFONBUCH_NACHNAME'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_NACHNAME']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_NACHNAME'] = '';


                                        if (isset($subArray['TELEFONBUCH_VORNAME']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_VORNAME']      = $subArray['TELEFONBUCH_VORNAME'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_VORNAME']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_VORNAME'] = '';


                                        if (isset($subArray['TELEFONBUCH_STRASSE']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_STRASSE']      = $subArray['TELEFONBUCH_STRASSE'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_STRASSE']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_STRASSE'] = '';


                                        if (isset($subArray['TELEFONBUCH_PLZ']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_PLZ']          = $subArray['TELEFONBUCH_PLZ'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_PLZ']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_PLZ'] = '';


                                        if (isset($subArray['TELEFONBUCH_ORT']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_ORT']          = $subArray['TELEFONBUCH_ORT'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_ORT']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_ORT'] = '';


                                        if (isset($subArray['TELEFONBUCH_FAX']))
                                            $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_FAX']          = $subArray['TELEFONBUCH_FAX'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEFONBUCH_FAX']))
                                                $this->globalOut_APPEND[$numIndex]['TELEFONBUCH_FAX'] = '';


                                        if (isset($subArray['TELEFONBUCH_SPERRE_INVERS']))
                                            $this->globalOut_APPEND[$numIndex]['TELEBUCH_SPERRE_INVERS']    = $subArray['TELEFONBUCH_SPERRE_INVERS'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEBUCH_SPERRE_INVERS']))
                                                $this->globalOut_APPEND[$numIndex]['TELEBUCH_SPERRE_INVERS'] = '';


                                        if (isset($subArray['TELEFONBUCH_EINTRAG_ELEKT']))
                                            $this->globalOut_APPEND[$numIndex]['TELEBUCH_EINTRAG_ELEKT']    = $subArray['TELEFONBUCH_EINTRAG_ELEKT'];
                                        else
                                            if (!isset($this->globalOut_APPEND[$numIndex]['TELEBUCH_EINTRAG_ELEKT']))
                                                $this->globalOut_APPEND[$numIndex]['TELEBUCH_EINTRAG_ELEKT'] = '';

                                    }
                                }


                                // Auf 3 füllen?
                                $x = $this->CNTSubs + 1;
                                for ($i = $x; $i <= 3; $i++){
                                    $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_ACCOUNT]    = '';

                                    $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_ACCOUNT_PASSWORT]    = '';

                                    $curVOIP_NATIONAL_VORWAHLT = 'VOIP_NATIONAL_VORWAHLT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_NATIONAL_VORWAHLT]    = '';

                                    $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_KOPFNUMMER]    = '';

                                    $curVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_TRANSACTION_NO]    = 'unset';
                                }



                                // Ende anhängen?
                                if (isset($this->globalOutEND[$numIndex])){
                                    foreach ($this->globalOutEND[$numIndex] as $keyname=>$value){
                                        $this->globalOut[$numIndex][$keyname] = $value;
                                    }
                                }


                                // Global Out anhängen?
                                if (isset($this->globalOut_APPEND)){
                                    foreach ($this->globalOut_APPEND[$numIndex] as $keyname=>$value){
                                        $this->globalOut[$numIndex][$keyname] = $value;
                                    }
                                }


                                // Einträge auf 10 auffüllen
                                $x = $this->CNTSubs + 1;
                                for ($i=$x; $i <= 10; $i++){
                                    $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_ACCOUNT]    = '';

                                    $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_ACCOUNT_PASSWORT]    = '';

                                    $curVOIP_NATIONAL_VORWAHLT = 'VOIP_NATIONAL_VORWAHLT_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_NATIONAL_VORWAHLT]    = '';

                                    $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_KOPFNUMMER]    = '';

                                    $curVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $i;
                                    $this->globalOut[$numIndex][$curVOIP_TRANSACTION_NO]    = 'unset';
                                }


                                $this->globalOut[$numIndex]['VOIP_ABG_PORT_TERMIN']     = 'unset';
                                $this->globalOut[$numIndex]['VOIP_ABG_PORT_AUF_CARRIER'] = 'unset';

                            }

                        }
                        // Hier alle Dienste die nicht VOIP sind!!!
                        else {
                            $numIndex = $this->getCurIndex();

                            $this->globalOut[$numIndex]['KUNDEN_NR']           = $curCustomerID;
                            $this->globalOut[$numIndex]['DIENST_ART']          = $baseDataArray['DIENST_ART'];
                            $this->globalOut[$numIndex]['DIENST_BEZEICHNUNG']  = $productArray['DIENST_BEZEICHNUNG'];

                            $this->globalOut[$numIndex]['DIENST_BEMERKUNG']    = '';
                            $this->globalOut[$numIndex]['DATEN_USERNAME']      = 'unset';
                            $this->globalOut[$numIndex]['DATEN_USERPASSWORT']  = 'unset';
                            $this->globalOut[$numIndex]['NAT_BETREIBEREBENE']  = 'N';
                            $this->globalOut[$numIndex]['CLIENT_ID']           = 'unset';
                            $this->globalOut[$numIndex]['USERINFO_ID']         = 'unset';
                            $this->globalOut[$numIndex]['ROUTER_MODELL']    = '';
                            $this->globalOut[$numIndex]['ROUTER_SERIEN_NR']    = '';
                            $this->globalOut[$numIndex]['ACS_ID']    = '';
                            $this->globalOut[$numIndex]['EXT_PRODUKT_ID']    = 'unset';
                            $this->globalOut[$numIndex]['OPTION_1']    = 'unset';
                            $this->globalOut[$numIndex]['OPTION_2']    = 'unset';
                            $this->globalOut[$numIndex]['OPTION_3']    = 'unset';
                            $this->globalOut[$numIndex]['GUELTIG_VON']    = $baseDataArray['GUELTIG_VON'];
                            $this->globalOut[$numIndex]['GUELTIG_BIS']    = $baseDataArray['GUELTIG_BIS'];
                            $this->globalOut[$numIndex]['ERFASST_AM']    = $baseDataArray['ERFASST_AM'];
                            $this->globalOut[$numIndex]['UNTERZEICHNET_AM']    = $baseDataArray['UNTERZEICHNET_AM'];
                            $this->globalOut[$numIndex]['WIDERRUFEN_AM']    = '';
                            $this->globalOut[$numIndex]['GEKUENDIGT_AM']    = '';
                            $this->globalOut[$numIndex]['STANDORT']    = 'unset';
                            $this->globalOut[$numIndex]['INSTALLATIONSTERMIN']    = 'unset';
                            $this->globalOut[$numIndex]['HAUPTVERTEILER']    = 'unset';
                            $this->globalOut[$numIndex]['KABELVERZEIGER']    = 'unset';
                            $this->globalOut[$numIndex]['DOPPELADER_1']    = 'unset';
                            $this->globalOut[$numIndex]['DOPPELADER_2']    = 'unset';
                            $this->globalOut[$numIndex]['VOIP_DIENST_BEZEICHNUNG']    = '';
                            $this->globalOut[$numIndex]['VOIP_EXT_PRODUKT_ID']    = '';
                            $this->globalOut[$numIndex]['SPERRE_0900']    = 'J';
                            $this->globalOut[$numIndex]['UEBERMITTLUNG_RUFNR']    = 'J';
                            $this->globalOut[$numIndex]['PURTEL_KUNDENUMMER']    = '';
                            $this->globalOut[$numIndex]['PURTEL_HAUPTANSCHLUSS']    = '';
                            $this->globalOut[$numIndex]['VOIP_SPERRE_AKTIV']    = 'N';
                            $this->globalOut[$numIndex]['VOIP_PORTIERUNG']    = 'unset';

                            $this->globalOut[$numIndex]['VOIP_PORT_TERMIN']    = '';
                            $this->globalOut[$numIndex]['VOIP_PORT_ABG_CARRIER'] = '';
                            $this->globalOut[$numIndex]['VOIP_PORT_REST_MSN_KUENDIGEN']    = 'unset';



                            for ($i = 1; $i <= 3; $i++){
                                $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_ACCOUNT]    = '';

                                $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_ACCOUNT_PASSWORT]    = '';

                                $curVOIP_NATIONAL_VORWAHLT = 'VOIP_NATIONAL_VORWAHLT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_NATIONAL_VORWAHLT]    = '';

                                $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_KOPFNUMMER]    = '';

                                $curVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_TRANSACTION_NO]    = 'unset';
                            }


                            $this->globalOut[$numIndex]['EGN_VERFREMDUNG']    = '';
                            $this->globalOut[$numIndex]['TELEFONBUCHEINTRAG']    = 'N';
                            $this->globalOut[$numIndex]['TELEFONBUCH_NACHNAME'] = '';
                            $this->globalOut[$numIndex]['TELEFONBUCH_VORNAME'] = '';
                            $this->globalOut[$numIndex]['TELEFONBUCH_STRASSE'] = '';
                            $this->globalOut[$numIndex]['TELEFONBUCH_PLZ'] = '';
                            $this->globalOut[$numIndex]['TELEFONBUCH_ORT'] = '';
                            $this->globalOut[$numIndex]['TELEFONBUCH_FAX'] = '';
                            $this->globalOut[$numIndex]['TELEBUCH_SPERRE_INVERS'] = '';
                            $this->globalOut[$numIndex]['TELEBUCH_EINTRAG_ELEKT'] = '';


                            for ($i = 4; $i <= 10; $i++){
                                $curVOIP_ACCOUNT = 'VOIP_ACCOUNT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_ACCOUNT]    = '';

                                $curVOIP_ACCOUNT_PASSWORT = 'VOIP_ACCOUNT_PASSWORT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_ACCOUNT_PASSWORT]    = '';

                                $curVOIP_NATIONAL_VORWAHLT = 'VOIP_NATIONAL_VORWAHLT_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_NATIONAL_VORWAHLT]    = '';

                                $curVOIP_KOPFNUMMER = 'VOIP_KOPFNUMMER_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_KOPFNUMMER]    = '';

                                $curVOIP_TRANSACTION_NO = 'VOIP_TRANSACTION_NO_' . $i;
                                $this->globalOut[$numIndex][$curVOIP_TRANSACTION_NO]    = 'unset';
                            }

                            $this->globalOut[$numIndex]['VOIP_ABG_PORT_TERMIN']     = 'unset';
                            $this->globalOut[$numIndex]['VOIP_ABG_PORT_AUF_CARRIER'] = 'unset';

                        }

                    }

                }

            }
            // echo "<br>"; // BR Bei Customer

        }


//        echo "<pre>";
//        print_r($this->globalOut);
//        echo "</pre><br>";

    }   // END     private function mainToExcel()




    private function getCurIndex()
    {
        $curNum = count($this->globalOut);

        $newIndex = $curNum + 1;

        $newIndex = $newIndex - 1;

        return $newIndex;
    }





    private function writeToExcel()
    {
        $excel = $this->writeToExcelHeadline();

        echo "<pre>";
        print_r($this->globalOut);
        echo "</pre><br>";

        // Durchlauf 0 ... Headline schreiben
        foreach ($this->globalOut as $cntRow=>$dataArray){

            // echo "Zeile: " . $cntRow . "<br>";

            $leadingPipe = false;

            foreach ($dataArray as $fieldname=>$value){

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
        $downloadLink = 'DimariDiensteExpFTTC_20160311';

        // '/var/www/html/www/uploads/';
        $storeFile = 'uploads/' . $downloadLink . '.csv';

        $fp = fopen($storeFile, 'w');
        fwrite($fp, $excel);
        fclose($fp);

        return true;
    }



    private function writeToExcelHeadline()
    {
        $excel = '';

        // Durchlauf 0 ... Headline schreiben
        foreach ($this->globalOut as $cntRow=>$dataArray){

            // echo "Zeile: " . $cntRow . "<br>";

            $leadingPipe = false;

            foreach ($dataArray as $fieldname=>$value){

                //echo "Feldname: " . $fieldname . " => Value: " . $value . "<br>";

                // Trennzeichen setzen?
                if ($leadingPipe)
                    $excel .= ';';

                $excel .= '"' . utf8_encode(trim($fieldname)) . '"';

                // Ab jetzt Trennzeichen setzen!
                $leadingPipe = true;

            }

            $excel .= "\r\n";

            break;
        }

        return $excel;
    }








}

