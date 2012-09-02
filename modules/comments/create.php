<?php
/**
 * Comments
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */
/**
 * Gibt das Formular zum Erzeugen eines Kommentares aus
 *
 * @param string $module
 * 	Das jeweilige Modul
 * @param integer $entry_id
 * 	Die ID des jeweiligen Eintrages
 * @return string
 */
function commentsCreate($module, $entry_id)
{
	global $auth, $date, $db, $lang, $session, $uri, $tpl;

	// Formular für das Eintragen von Kommentaren
	if (isset($_POST['submit']) === true) {
		$ip = $_SERVER['REMOTE_ADDR'];

		// Flood Sperre
		$flood = ACP3_CMS::$db->select('date', 'comments', 'ip = \'' . $ip . '\'', 'id DESC', '1');
		if (count($flood) === 1) {
			$flood_time = ACP3_CMS::$date->timestamp($flood[0]['date']) + CONFIG_FLOOD;
		}
		$time = ACP3_CMS::$date->timestamp();

		if (isset($flood_time) && $flood_time > $time)
			$errors[] = sprintf(ACP3_CMS::$lang->t('common', 'flood_no_entry_possible'), $flood_time - $time);
		if (empty($_POST['name']))
			$errors['name'] = ACP3_CMS::$lang->t('common', 'name_to_short');
		if (strlen($_POST['message']) < 3)
			$errors['message'] = ACP3_CMS::$lang->t('common', 'message_to_short');
		if (ACP3_Modules::check(ACP3_CMS::$db->escape($_POST['module']), 'list') === false || ACP3_Validate::isNumber($_POST['entry_id']) === false)
			$errors[] = ACP3_CMS::$lang->t('comments', 'module_doesnt_exist');
		if (ACP3_CMS::$auth->isUser() === false && ACP3_Validate::captcha($_POST['captcha']) === false)
			$errors['captcha'] = ACP3_CMS::$lang->t('captcha', 'invalid_captcha_entered');

		if (isset($errors) === true) {
			ACP3_CMS::$view->assign('error_msg', errorBox($errors));
		} elseif (ACP3_Validate::formToken() === false) {
			return errorBox(ACP3_CMS::$lang->t('common', 'form_already_submitted'));
		} else {
			$mod_id = ACP3_CMS::$db->select('id', 'modules', 'name = \'' . ACP3_CMS::$db->escape($_POST['module']) . '\'');
			$insert_values = array(
				'id' => '',
				'date' => ACP3_CMS::$date->timestampToDateTime($time),
				'ip' => $ip,
				'name' => ACP3_CMS::$auth->isUser() === true && ACP3_Validate::isNumber(ACP3_CMS::$auth->getUserId() === true) ? '' : ACP3_CMS::$db->escape($_POST['name']),
				'user_id' => ACP3_CMS::$auth->isUser() === true && ACP3_Validate::isNumber(ACP3_CMS::$auth->getUserId() === true) ? ACP3_CMS::$auth->getUserId() : '',
				'message' => ACP3_CMS::$db->escape($_POST['message']),
				'module_id' => $mod_id[0]['id'],
				'entry_id' => $_POST['entry_id'],
			);

			$bool = ACP3_CMS::$db->insert('comments', $insert_values);

			ACP3_CMS::$session->unsetFormToken();

			return confirmBox(ACP3_CMS::$lang->t('common', $bool !== false ? 'create_success' : 'create_error'), ACP3_CMS::$uri->route(ACP3_CMS::$uri->query));
		}
	}
	if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
		$settings = ACP3_Config::getSettings('comments');

		// Emoticons einbinden, falls diese aktiv sind
		if (ACP3_Modules::check('emoticons', 'functions') === true && $settings['emoticons'] == 1) {
			require_once MODULES_DIR . 'emoticons/functions.php';

			// Emoticons im Formular anzeigen
			ACP3_CMS::$view->assign('emoticons', emoticonsList());
		}

		// Name des Moduls und Datensatznummer ins Formular einbinden
		$defaults = array(
			'module' => $module,
			'entry_id' => $entry_id
		);

		// Falls Benutzer eingeloggt ist, Formular schon teilweise ausfüllen
		if (ACP3_CMS::$auth->isUser() === true) {
			$user = ACP3_CMS::$auth->getUserInfo();
			$disabled = ' readonly="readonly" class="readonly"';

			if (isset($_POST['submit'])) {
				$_POST['name'] = $user['nickname'];
				$_POST['name_disabled'] = $disabled;
			} else {
				$defaults['name'] = $user['nickname'];
				$defaults['name_disabled'] = $disabled;
				$defaults['message'] = '';
			}
		} else {
			$defaults['name'] = '';
			$defaults['name_disabled'] = '';
			$defaults['message'] = '';
		}
		ACP3_CMS::$view->assign('form', isset($_POST['submit']) ? array_merge($defaults, $_POST) : $defaults);

		require_once MODULES_DIR . 'captcha/functions.php';
		ACP3_CMS::$view->assign('captcha', captcha());

		ACP3_CMS::$session->generateFormToken();

		return ACP3_CMS::$view->fetchTemplate('comments/create.tpl');
	}
}