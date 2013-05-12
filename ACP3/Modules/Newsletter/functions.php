<?php
/**
 * Newsletter
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

/**
 * Versendet einen Newsletter
 *
 * @param string $subject
 * @param string $body
 * @param string $from_address
 * @return boolean
 */
function sendNewsletter($subject, $body, $from_address)
{
	$accounts = ACP3\CMS::$injector['Db']->fetchAll('SELECT mail FROM ' . DB_PRE . 'newsletter_accounts WHERE hash = \'\'');
	$c_accounts = count($accounts);

	for ($i = 0; $i < $c_accounts; ++$i) {
		$bool2 = generateEmail('', $accounts[$i]['mail'], $from_address, $subject, $body);
		if ($bool2 === false)
			return false;
	}
	return true;
}
/**
 * Meldet eine E-Mail-Adresse beim Newsletter an
 *
 * @param string $emailAddress
 *	Die anzumeldente E-Mail-Adresse
 * @return boolean
 */
function subscribeToNewsletter($emailAddress)
{
	$hash = md5(mt_rand(0, microtime(true)));
	$host = htmlentities($_SERVER['HTTP_HOST']);
	$settings = ACP3\Core\Config::getSettings('newsletter');

	$subject = sprintf(ACP3\CMS::$injector['Lang']->t('newsletter', 'subscribe_mail_subject'), CONFIG_SEO_TITLE);
	$body = str_replace('{host}', $host, ACP3\CMS::$injector['Lang']->t('newsletter', 'subscribe_mail_body')) . "\n\n";
	$body.= 'http://' . $host . ACP3\CMS::$injector['URI']->route('newsletter/activate/hash_' . $hash . '/mail_' . $emailAddress);
	$mail_sent = generateEmail('', $emailAddress, $settings['mail'], $subject, $body);
	$bool = false;

	// Newsletter-Konto nur erstellen, wenn die E-Mail erfolgreich versendet werden konnte
	if ($mail_sent === true) {
		$insert_values = array(
			'id' => '',
			'mail' => $emailAddress,
			'hash' => $hash
		);
		$bool = ACP3\CMS::$injector['Db']->insert(DB_PRE . 'newsletter_accounts', $insert_values);
	}

	return $mail_sent === true && $bool !== false;
}