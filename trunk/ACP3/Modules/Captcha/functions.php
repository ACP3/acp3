<?php
/**
 * Captcha
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

/**
 * Erzeugt das Captchafeld für das Template
 *
 * @param integer $captcha_length
 *  Anzahl der Zeichen, welche das Captcha haben soll
 * @return string
 */
function captcha($captcha_length = 5)
{
	// Wenn man als User angemeldet ist, Captcha nicht anzeigen
	if (ACP3\CMS::$injector['Auth']->isUser() === false) {
		$_SESSION['captcha'] = ACP3\Core\Functions::salt($captcha_length);

		$captcha = array();
		$captcha['width'] = $captcha_length * 25;
		$captcha['height'] = 30;
		ACP3\CMS::$injector['View']->assign('captcha', $captcha);
		return ACP3\CMS::$injector['View']->fetchTemplate('captcha/captcha.tpl');
	}
	return '';
}