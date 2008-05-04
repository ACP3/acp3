<?php
/**
 * Diese Datei ist für die häufig verwendeten Funktionen im Installer zuständig
 *
 * @author Goratsch Webdesign
 * @package ACP3 Installer
 */
// Sprachdateien
function lang($module, $key)
{
	static $lang_data = array();

	$path = ACP3_ROOT . 'languages/' . LANG . '/' . $module . '.xml';

	if (!isset($lang_data[$module][$key]) && is_file($path)) {
		$xml = simplexml_load_file($path);
		foreach ($xml->item as $row) {
			$lang_data[$module][(string) $row->name] = (string) $row->message;
		}
	}

	return isset($lang_data[$module][$key]) ? $lang_data[$module][$key] : strtoupper('{' . $module . '_' . $key . '}');
}
// Variablen escapen
function mask($var, $mode = 1)
{
	switch ($mode) {
		case 1:
			return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
			break;
		case 2:
			return addslashes($var);
			break;
		case 3:
			return stripslashes($var);
			break;
	}
}
// Funktion zum Salzen von Passwörtern
function salt($str_length)
{
	$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$c_chars = strlen($chars) - 1;
	$key = '';
	for ($i = 0; $i < $str_length; ++$i) {
		$key.= $chars[mt_rand(0, $c_chars)];
	}
	return $key;
}
// Selektion eines Eintrages in einem Dropdown-Menü
function select_entry($name, $value, $field_value = '', $attr = 'selected') {
	$attr = ' ' . $attr . '="' . $attr . '"';

	if (!isset($_POST['form'][$name])) {
		if (!is_array($field_value) && $field_value == $value) {
			return $attr;
		} elseif (is_array($field_value)) {
			foreach ($field_value as $row) {
				if ($row == $value)
					return $attr;
			}
		}
	} elseif (isset($_POST['form'][$name]) && $_POST['form'][$name] != '') {
		if (!is_array($_POST['form'][$name]) && $_POST['form'][$name] == $value) {
			return $attr;
		} elseif (is_array($_POST['form'][$name])) {
			foreach ($_POST['form'][$name] as $row) {
				if ($row == $value)
					return $attr;
			}
		}
	}
}
// URIs
function uri($uri)
{
	return PHP_SELF . '/' . $uri . (!preg_match('/\/$/', $uri) ? '/' : '') . 'lang_' . LANG . '/';
}
?>