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
	public function __construct()
	{

	}   // END public function __construct(...)



	// Methode ist die Haupt - Methode für das Daten - Einlesen und für das Ausgeben
	function initialOutDataFullHandling()
	{

		// Starte Daten einlesen:
		$this->initialCollectData();
		$this->showStatus();

	}	// END function initialOutDataFullHandling()


}   // END class OutData