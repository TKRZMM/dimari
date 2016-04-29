<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 29.04.2016
 * Time: 13:04
 */
class Customer
{

	public $CUSTOMER_ID;



	// Klassen - Konstruktor
	public function __construct()
	{

	}   // END public function __construct(...)


	public function setThis($var, $val)
	{
		$this->$var = $val;
	}

}   // END class Customer