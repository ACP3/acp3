<?php
/**
 * Users
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */
/**
 * Überprüft, ob der übergebene Username schon existiert
 *
 * @param string $nickname
 *  Der zu überprüfende Nickname
 * @return boolean
 */
function userNameExists($nickname, $id = 0)
{
	global $db;
	$nickname = $db->escape($nickname);
	$id = ACP3_Validate::isNumber($id) === true ? ' AND id != \'' . $id . '\'' : '';
	return !empty($nickname) && $db->countRows('*', 'users', 'nickname = \'' . $nickname . '\'' . $id) == 1 ? true : false;
}
/**
 * Überprüft, ob die übergebene E-Mail-Adresse schon existiert
 *
 * @param string $mail
 *  Die zu überprüfende E-Mail-Adresse
 * @return boolean
 */
function userEmailExists($mail, $id = 0)
{
	global $db;
	$id = ACP3_Validate::isNumber($id) === true ? ' AND id != \'' . $id . '\'' : '';
	return ACP3_Validate::email($mail) === true && $db->countRows('*', 'users', 'mail IN(\'' . $mail . ':1\', \'' . $mail . ':0\')' . $id) > 0 ? true : false;
}