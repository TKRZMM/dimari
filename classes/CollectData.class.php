<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 15:08
 */
abstract class CollectData extends Message
{

	// Klassen - Konstruktor
	public function __construct()
	{

	}   // END public function __construct(...)





	// Initial und Steuer-Methode für das Daten-Einlesen
	function initialCollectData()
	{

		$this->addMessage('Starte Methode:<br> ' . __METHOD__, 'START', 'Runtime');


	}	// END function initialCollectData()




}   // END class CollectData