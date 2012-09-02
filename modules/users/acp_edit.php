<?php
/**
 * Users
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (ACP3_Validate::isNumber(ACP3_CMS::$uri->id) === true && ACP3_CMS::$db->countRows('*', 'users', 'id = \'' . ACP3_CMS::$uri->id . '\'') == 1) {
	$user = ACP3_CMS::$auth->getUserInfo(ACP3_CMS::$uri->id);

	if (isset($_POST['submit']) === true) {
		require_once MODULES_DIR . 'users/functions.php';

		if (empty($_POST['nickname']))
			$errors['nickname'] = ACP3_CMS::$lang->t('common', 'name_to_short');
		if (userNameExists($_POST['nickname'], ACP3_CMS::$uri->id))
			$errors['nickname'] = ACP3_CMS::$lang->t('users', 'user_name_already_exists');
		if (ACP3_Validate::email($_POST['mail']) === false)
			$errors['mail'] = ACP3_CMS::$lang->t('common', 'wrong_email_format');
		if (userEmailExists($_POST['mail'], ACP3_CMS::$uri->id))
			$errors['mail'] = ACP3_CMS::$lang->t('users', 'user_email_already_exists');
		if (empty($_POST['roles']) || is_array($_POST['roles']) === false || ACP3_Validate::aclRolesExist($_POST['roles']) === false)
			$errors['roles'] = ACP3_CMS::$lang->t('users', 'select_access_level');
		if (!isset($_POST['super_user']) || ($_POST['super_user'] != 1 && $_POST['super_user'] != 0))
			$errors['super-user'] = ACP3_CMS::$lang->t('users', 'select_super_user');
		if (ACP3_CMS::$lang->languagePackExists($_POST['language']) === false)
			$errors['language'] = ACP3_CMS::$lang->t('users', 'select_language');
		if (ACP3_Validate::isNumber($_POST['entries']) === false)
			$errors['entries'] = ACP3_CMS::$lang->t('common', 'select_records_per_page');
		if (empty($_POST['date_format_long']) || empty($_POST['date_format_short']))
			$errors[] = ACP3_CMS::$lang->t('system', 'type_in_date_format');
		if (ACP3_Validate::timeZone($_POST['date_time_zone']) === false)
			$errors['time-zone'] = ACP3_CMS::$lang->t('common', 'select_time_zone');
		if (!empty($_POST['new_pwd']) && !empty($_POST['new_pwd_repeat']) && $_POST['new_pwd'] != $_POST['new_pwd_repeat'])
			$errors[] = ACP3_CMS::$lang->t('users', 'type_in_pwd');

		if (isset($errors) === true) {
			ACP3_CMS::$view->assign('error_msg', errorBox($errors));
		} elseif (ACP3_Validate::formToken() === false) {
			ACP3_CMS::setContent(errorBox(ACP3_CMS::$lang->t('common', 'form_already_submitted')));
		} else {
			$update_values = array(
				'super_user' => (int) $_POST['super_user'],
				'nickname' => ACP3_CMS::$db->escape($_POST['nickname']),
				'realname' => ACP3_CMS::$db->escape($_POST['realname']) . ':' . $user['realname_display'],
				'mail' => $_POST['mail'] . ':' . $user['mail_display'],
				'website' => ACP3_CMS::$db->escape($_POST['website'], 2) . ':' . $user['website_display'],
				'date_format_long' => ACP3_CMS::$db->escape($_POST['date_format_long']),
				'date_format_short' => ACP3_CMS::$db->escape($_POST['date_format_short']),
				'time_zone' => $_POST['date_time_zone'],
				'language' => ACP3_CMS::$db->escape($_POST['language'], 2),
				'entries' => (int) $_POST['entries'],
			);

			// Rollen aktualisieren
			ACP3_CMS::$db->link->beginTransaction();
			ACP3_CMS::$db->delete('acl_user_roles', 'user_id = \'' . ACP3_CMS::$uri->id . '\'');
			foreach ($_POST['roles'] as $row) {
				ACP3_CMS::$db->insert('acl_user_roles', array('user_id' => ACP3_CMS::$uri->id, 'role_id' => $row));
			}
			ACP3_CMS::$db->link->commit();

			// Neues Passwort
			if (!empty($_POST['new_pwd']) && !empty($_POST['new_pwd_repeat'])) {
				$salt = salt(12);
				$new_pwd = generateSaltedPassword($salt, $_POST['new_pwd']);
				$update_values['pwd'] = $new_pwd . ':' . $salt;
			}

			$bool = ACP3_CMS::$db->update('users', $update_values, 'id = \'' . ACP3_CMS::$uri->id . '\'');

			// Falls sich der User selbst bearbeitet hat, Cookie aktualisieren
			if (ACP3_CMS::$uri->id == ACP3_CMS::$auth->getUserId()) {
				$cookie_arr = explode('|', base64_decode($_COOKIE['ACP3_AUTH']));
				ACP3_CMS::$auth->setCookie($_POST['nickname'], isset($new_pwd) ? $new_pwd : $cookie_arr[1], 3600);
			}

			ACP3_CMS::$session->unsetFormToken();

			setRedirectMessage($bool, ACP3_CMS::$lang->t('common', $bool !== false ? 'edit_success' : 'edit_error'), 'acp/users');
		}
	}
	if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
		$user['nickname'] = ACP3_CMS::$db->escape($user['nickname'], 3);
		$user['date_format_long'] = ACP3_CMS::$db->escape($user['date_format_long'], 3);
		$user['date_format_short'] = ACP3_CMS::$db->escape($user['date_format_short'], 3);

		// Zugriffslevel holen
		$roles = ACP3_ACL::getAllRoles();
		$c_roles = count($roles);
		$user_roles = ACP3_ACL::getUserRoles(ACP3_CMS::$uri->id);
		for ($i = 0; $i < $c_roles; ++$i) {
			$roles[$i]['name'] = str_repeat('&nbsp;&nbsp;', $roles[$i]['level']) . $roles[$i]['name'];
			$roles[$i]['selected'] = selectEntry('roles', $roles[$i]['id'], in_array($roles[$i]['id'], $user_roles) ? $roles[$i]['id'] : '');
		}
		ACP3_CMS::$view->assign('roles', $roles);

		// Super User
		$super_user = array();
		$super_user[0]['value'] = '1';
		$super_user[0]['checked'] = selectEntry('super_user', '1', $user['super_user'], 'checked');
		$super_user[0]['lang'] = ACP3_CMS::$lang->t('common', 'yes');
		$super_user[1]['value'] = '0';
		$super_user[1]['checked'] = selectEntry('super_user', '0', $user['super_user'], 'checked');
		$super_user[1]['lang'] = ACP3_CMS::$lang->t('common', 'no');
		ACP3_CMS::$view->assign('super_user', $super_user);

		// Sprache
		$user['language'] = ACP3_CMS::$db->escape($user['language'], 3);
		$languages = array();
		$lang_dir = scandir(ACP3_ROOT . 'languages');
		$c_lang_dir = count($lang_dir);
		for ($i = 0; $i < $c_lang_dir; ++$i) {
			$lang_info = ACP3_XML::parseXmlFile(ACP3_ROOT . 'languages/' . $lang_dir[$i] . '/info.xml', '/language');
			if (!empty($lang_info)) {
				$name = $lang_info['name'];
				$languages[$name]['dir'] = $lang_dir[$i];
				$languages[$name]['selected'] = selectEntry('language', $lang_dir[$i], $user['language']);
				$languages[$name]['name'] = $lang_info['name'];
			}
		}
		ksort($languages);
		ACP3_CMS::$view->assign('languages', $languages);

		// Einträge pro Seite
		ACP3_CMS::$view->assign('entries', recordsPerPage((int) $user['entries']));

		// Zeitzonen
		ACP3_CMS::$view->assign('time_zones', ACP3_CMS::$date->getTimeZones($user['time_zone']));

		ACP3_CMS::$view->assign('form', isset($_POST['submit']) ? $_POST : $user);

		ACP3_CMS::$session->generateFormToken();

		ACP3_CMS::setContent(ACP3_CMS::$view->fetchTemplate('users/acp_edit.tpl'));
	}
} else {
	ACP3_CMS::$uri->redirect('errors/404');
}