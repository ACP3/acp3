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

if (ACP3_Validate::isNumber(ACP3_CMS::$uri->id) === true &&
	ACP3_CMS::$db2->fetchColumn('SELECT COUNT(*) FROM ' . DB_PRE . 'newsletters WHERE id = ?', array(ACP3_CMS::$uri->id)) == 1) {
	// Brotkrümelspur
	ACP3_CMS::$breadcrumb
	->append(ACP3_CMS::$lang->t('newsletter', 'newsletter'), ACP3_CMS::$uri->route('acp/newsletter'))
	->append(ACP3_CMS::$lang->t('newsletter', 'acp_edit'));

	if (isset($_POST['submit']) === true) {
		if (strlen($_POST['title']) < 3)
			$errors['title'] = ACP3_CMS::$lang->t('newsletter', 'subject_to_short');
		if (strlen($_POST['text']) < 3)
			$errors['text'] = ACP3_CMS::$lang->t('newsletter', 'text_to_short');

		if (isset($errors) === true) {
			ACP3_CMS::$view->assign('error_msg', errorBox($errors));
		} elseif (ACP3_Validate::formToken() === false) {
			ACP3_CMS::$view->setContent(errorBox(ACP3_CMS::$lang->t('system', 'form_already_submitted')));
		} else {
			$settings = ACP3_Config::getSettings('newsletter');

			// Newsletter archivieren
			$update_values = array(
				'date' => ACP3_CMS::$date->getCurrentDateTime(),
				'title' => str_encode($_POST['title']),
				'text' => str_encode($_POST['text'], true),
				'status' => $_POST['test'] == 1 ? '0' : (int) $_POST['action'],
				'user_id' => ACP3_CMS::$auth->getUserId(),
			);
			$bool = ACP3_CMS::$db2->update(DB_PRE . 'newsletters', $update_values, array('id' => ACP3_CMS::$uri->id));

			if ($_POST['action'] == 1 && $bool !== false) {
				$subject = str_encode($_POST['title'], true);
				$body = str_encode($_POST['text'], true) . "\n" . html_entity_decode($settings['mailsig'], ENT_QUOTES, 'UTF-8');

				// Testnewsletter
				if ($_POST['test'] == 1) {
					$bool2 = generateEmail('', $settings['mail'], $settings['mail'], $subject, $body);
				// An alle versenden
				} else {
					require_once MODULES_DIR . 'newsletter/functions.php';
					$bool2 = sendNewsletter($subject, $body, $settings['mail']);
				}
			}

			ACP3_CMS::$session->unsetFormToken();

			if ($_POST['action'] == 0 && $bool !== false) {
				setRedirectMessage(true, ACP3_CMS::$lang->t('newsletter', 'save_success'), 'acp/newsletter');
			} elseif ($_POST['action'] == 1 && $bool !== false && $bool2 === true) {
				setRedirectMessage($bool && $bool2, ACP3_CMS::$lang->t('newsletter', 'create_success'), 'acp/newsletter');
			} else {
				setRedirectMessage(false, ACP3_CMS::$lang->t('newsletter', 'create_save_error'), 'acp/newsletter');
			}
		}
	}
	if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
		$newsletter = ACP3_CMS::$db2->fetchAssoc('SELECT title, text FROM ' . DB_PRE . 'newsletters WHERE id = ?', array(ACP3_CMS::$uri->id));

		ACP3_CMS::$view->assign('form', isset($_POST['submit']) ? $_POST : $newsletter);

		$lang_test = array(ACP3_CMS::$lang->t('system', 'yes'), ACP3_CMS::$lang->t('system', 'no'));
		ACP3_CMS::$view->assign('test', selectGenerator('test', array(1, 0), $lang_test, 0, 'checked'));

		$lang_action = array(ACP3_CMS::$lang->t('newsletter', 'send_and_save'), ACP3_CMS::$lang->t('newsletter', 'only_save'));
		ACP3_CMS::$view->assign('action', selectGenerator('action', array(1, 0), $lang_action, 1, 'checked'));

		ACP3_CMS::$session->generateFormToken();
	}
} else {
	ACP3_CMS::$uri->redirect('errors/404');
}