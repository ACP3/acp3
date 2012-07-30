<?php
/**
 * Newsletter
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (ACP3_Validate::isNumber($uri->id) === true && $db->countRows('*', 'newsletter_archive', 'id = \'' . $uri->id . '\'') == 1) {
	$settings = ACP3_Config::getModuleSettings('newsletter');
	$newsletter = $db->select('subject, text', 'newsletter_archive', 'id = \'' . $uri->id . '\'');
	$accounts = $db->select('mail', 'newsletter_accounts', 'hash = \'\'');
	$c_accounts = count($accounts);
	$bool = $bool2 = false;

	$subject = $db->escape($newsletter[0]['subject'], 3);
	$body = html_entity_decode($db->escape($newsletter[0]['text'], 3) . "\n-- \n" . $db->escape($settings['mailsig'], 3), ENT_QUOTES, 'UTF-8');

	for ($i = 0; $i < $c_accounts; ++$i) {
		$bool = generateEmail('', $accounts[$i]['mail'], $settings['mail'], $subject, $body);
		if ($bool === false)
			break;
	}
	if ($bool === true) {
		$bool2 = $db->update('newsletter_archive', array('status' => '1'), 'id = \'' . $uri->id . '\'');
	}

	setRedirectMessage($bool === true && $bool2 !== false ? $lang->t('newsletter', 'compose_success') : $lang->t('newsletter', 'compose_save_error'), 'acp/newsletter/adm_list_archive');
} else {
	$uri->redirect('errors/404');
}