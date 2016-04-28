<?php

/**
 * Created by PhpStorm.
 * User: MMelching
 * Date: 28.04.2016
 * Time: 15:31
 */
abstract class Message
{

	// Message Handler
	public $myMessage = array('Info'    => array(),
							  'Runtime' => array()
	);










	// Klassen - Konstruktor
	public function __construct()
	{

	}   // END public function __construct(...)










	// Hänge ein Message an die ggf. schon bestehenden Messages
	public function addMessage($messageValue, $messageStatus = 'unset', $messageType = 'Runtime')
	{

		$this->myMessage[$messageType]['messageValue'][] = $messageValue;
		$this->myMessage[$messageType]['messageStatus'][] = $messageStatus;

		return true;

	}   // END private function addMessage($messageType, $messageValue)










	// Status ausgeben:
	public function showStatus()
	{

		$cntCategorie = 0;

		print ('<div style="position: static; overflow: auto; top: 50px; bottom: 20px; min-width: 600px; right: 5px; background-color: beige"><table>');

		// Durchlauf der Message - Kategorien
		foreach($this->myMessage as $messageType => $curMessageArray) {

			// Sind Einträge in der Kategorie vorhanden?
			if (count($curMessageArray) > 0) {

				$cntCategorie++;

				// Kategorienamen ausgeben
				if ($cntCategorie > 1)
					print ('<tr><th colspan="3" style="padding-top: 30px;">' . $messageType . '</th></td></tr>');
				else
					print ('<tr><th colspan="3">' . $messageType . '</th></td></tr>');


				// SpaltenTyp ausgeben:
				print ('<tr><td style="min-width: 40px;" class="bottomLine">Cnt</td><td style="min-width: 260px;" class="bottomLine">Event</td><td class="bottomLine">Status</td></tr>');

				foreach($curMessageArray['messageValue'] as $fieldIndex => $value) {

					print ('<tr><td class="bottomLine">');

					$infoCnt = '# ' . ($fieldIndex + 1);
					print ($infoCnt);


					print ('</td><td class="bottomLine">');
					print ($value);
					print ('</td>');


					// Wenn der Status der aktuelle Message gesetzt ist ... und er nicht 'unset' ist... dann ausgeben
					if ((isset($curMessageArray['messageStatus'][$fieldIndex])) && ($curMessageArray['messageStatus'][$fieldIndex] != 'unset'))
						print ('<td class="bottomLine">' . $curMessageArray['messageStatus'][$fieldIndex] . '</td>');
					else
						print ('<td class="bottomLine">&nbsp;</td>');

					print ('</tr>');

				}

			}

		}

		print ('</table></div>');

	}   // END private function showStatus()










	// Status hinzufügen und jetzt ausgeben
	public function outNow($messageValue, $messageStatus = 'unset', $messageType = 'Runtime')
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


}   // END class Message