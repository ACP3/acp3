<?php
if (!defined('IN_ACP3'))
	exit;

if ($auth->isUser()) {
	redirect(0, ROOT_DIR);
} else {
	breadcrumb::assign(lang('users', 'users'), uri('users'));
	breadcrumb::assign(lang('users', 'forgot_pwd'));

	if (isset($_POST['submit'])) {
		$form = $_POST['form'];

		if (empty($form['nickname']) && empty($form['mail']))
			$errors[] = lang('users', 'type_in_nickname_or_email');
		if (!empty($form['nickname']) && $db->select('id', 'users', 'nickname = \'' . $db->escape($form['nickname']) . '\'', 0, 0, 0, 1) == '0')
			$errors[] = lang('users', 'user_not_exists');
		if (!empty($form['mail']) && !validate::email($form['mail']))
			$errors[] = lang('common', 'wrong_email_format');
		if (validate::email($form['mail']) && $db->select('id', 'users', 'mail = \'' . $form['mail'] . '\'', 0, 0, 0, 1) == '0')
			$errors[] = lang('users', 'user_not_exists');
		if (!validate::captcha($form['captcha'], $form['hash']))
			$errors[] = lang('captcha', 'invalid_captcha_entered');

		if (isset($errors)) {
			$tpl->assign('error_msg', comboBox($errors));
		} else {
			// Neues Passwort und neuen Zufallsschlüssel erstellen
			$new_password = salt(8);
			$host = htmlentities($_SERVER['HTTP_HOST']);

			// Je nachdem welches Feld ausgefüllt wurde, dieses auswählen
			$where = !empty($form['mail']) ? 'mail = \'' . $form['mail'] . '\'' : 'nickname = \'' . $db->escape($form['nickname']) . '\'';
			$user = $db->select('id, nickname, mail', 'users', $where);

			// E-Mail mit dem neuen Passwort versenden
			$subject = str_replace(array('{title}', '{host}'), array(CONFIG_TITLE, $host), lang('users', 'forgot_pwd_mail_subject'));
			$message = str_replace(array('{name}', '{mail}', '{password}', '{title}', '{host}'), array($user[0]['nickname'], $user[0]['mail'], $new_password, CONFIG_TITLE, $host), lang('users', 'forgot_pwd_mail_message'));
			$header = 'Content-type: text/plain; charset=UTF-8';
			$mail_sent = mail($user[0]['mail'], $subject, $message, $header);

			// Das Passwort des Benutzers nur abändern, wenn die E-Mail erfolgreich versendet werden konnte
			if ($mail_sent) {
				$salt = salt(12);
				$bool = $db->update('users', array('pwd' => sha1($salt . sha1($new_password)) . ':' . $salt), 'id = \'' . $user[0]['id'] . '\'');
			}
			$content = comboBox($mail_sent && isset($bool) && $bool ? lang('users', 'forgot_pwd_success') : lang('users', 'forgot_pwd_error'), ROOT_DIR);
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		$defaults = array(
			'nickname' => '',
			'mail' => '',
		);

		$tpl->assign('form', isset($form) ? $form : $defaults);

		$tpl->assign('captcha', captcha());

		$content = $tpl->fetch('users/forgot_pwd.html');
	}
}
?>