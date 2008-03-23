<?php
/**
 * Validate
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
/**
 * Klasse zur Validierung von bestimmten Eintr�gen
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
class validate
{
	/**
	 * �berp�rft, ob der eingegebene Captcha mit dem generierten �bereinstimmt
	 *
	 * @param string $input
	 * @param string $hash
	 * @return boolean
	 */
	public function captcha($input, $hash)
	{
		return $input != base64_decode($hash) ? false : true;
	}
	/**
	 * �berpr�ft, ob alle Daten ein sinnvolles Datum ergeben
	 *
	 * @param array $values
	 *  Zu �berpr�fende Werte
	 * @param string $prefix_start
	 *  Pr�fix f�r das Ver�ffentlichungsdatum
	 * @param string $prefix_end
	 *  Pr�fix f�r das Enddatum
	 * @return boolean
	 */
	public function date($values, $prefix_start = 'start', $prefix_end = 'end')
	{
		if (!$this->is_number($values[$prefix_start . '_day']) ||
			!$this->is_number($values[$prefix_start . '_month']) ||
			!$this->is_number($values[$prefix_start . '_year']) ||
			!$this->is_number($values[$prefix_start . '_hour']) ||
			!$this->is_number($values[$prefix_start . '_min']) ||
			!$this->is_number($values[$prefix_end . '_day']) ||
			!$this->is_number($values[$prefix_end . '_month']) ||
			!$this->is_number($values[$prefix_end . '_year']) ||
			!$this->is_number($values[$prefix_end . '_hour']) ||
			!$this->is_number($values[$prefix_end . '_min']) ||
			mktime($values[$prefix_start . '_hour'], $values[$prefix_start . '_min'], 0, $values[$prefix_start . '_month'], $values[$prefix_start . '_day'], $values[$prefix_start . '_year']) >
			mktime($values[$prefix_end . '_hour'], $values[$prefix_end . '_min'], 0, $values[$prefix_end . '_month'], $values[$prefix_end . '_day'], $values[$prefix_end . '_year'])) {
			return false;
		}
		return true;
	}
	/**
	 * �berpr�ft, ob eine Standardkonforme E-Mail-Adresse �bergeben wurde
	 *
	 * @copyright HTML/QuickForm/Rule/Email.php
	 * 	Suchmuster von PEAR entnommen
	 * @param string $var
	 *  Zu �berpr�fende E-Mail-Adresse
	 * @return boolean
	 */
	public function email($var)
	{
		$pattern = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

		return preg_match($pattern, $var);
	}
	/**
	 * �berpr�ft eine Variable, ob diese nur aus Ziffern besteht
	 *
	 * @param mixed $var
	 * @return boolean
	 */
	public function is_number($var)
	{
		return preg_match('/^(\d+)$/', $var);
	}
	/**
	 * �berpr�fen, ob es ein unterst�tztes Bildformat ist
	 *
	 * @param string $var
	 *  Zu �berpr�fendes Bild
	 * @return boolean
	 */
	public function is_picture($var)
	{
		$info = getimagesize($var);

		return $info[2] == '1' || $info[2] == '2' || $info[2] == '3' ? true : false;
	}
}
?>