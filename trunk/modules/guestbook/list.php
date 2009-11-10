<?php
/**
 * Guestbook
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ACP3'))
	exit;

$guestbook = $db->query('SELECT u.nickname AS user_name, u.website AS user_website, u.mail AS user_mail, g.date, g.name, g.user_id, g.message, g.website, g.mail FROM ' . $db->prefix . 'guestbook AS g LEFT JOIN ' . $db->prefix . 'users AS u ON(u.id = g.user_id) ORDER BY date DESC LIMIT ' . POS . ', ' . $auth->entries);
$c_guestbook = count($guestbook);

if ($c_guestbook > 0) {
	$tpl->assign('pagination', pagination($db->countRows('*', 'guestbook')));
	$emoticons = false;

	// Emoticons einbinden
	if (modules::check('emoticons', 'functions') == 1) {
		require_once ACP3_ROOT . 'modules/emoticons/functions.php';
		$emoticons = true;
	}

	$settings = config::output('guestbook');

	for ($i = 0; $i < $c_guestbook; ++$i) {
		if (empty($guestbook[$i]['user_name']) && empty($guestbook[$i]['name'])) {
			$guestbook[$i]['name'] = $lang->t('users', 'deleted_user');
			$guestbook[$i]['user_id'] = 0;
		}
		$guestbook[$i]['name'] = $db->escape(!empty($guestbook[$i]['user_name']) ? $guestbook[$i]['user_name'] : $guestbook[$i]['name'], 3);
		$guestbook[$i]['date'] = $date->format($guestbook[$i]['date'], $settings['dateformat']);
		$guestbook[$i]['message'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $guestbook[$i]['message']);
		if ($emoticons) {
			$guestbook[$i]['message'] = emoticonsReplace($guestbook[$i]['message']);
		}
		$guestbook[$i]['website'] = $db->escape(strlen($guestbook[$i]['user_website']) > 2 ? substr($guestbook[$i]['user_website'], 0, -2) : $guestbook[$i]['website'], 3);
		if (!empty($guestbook[$i]['website']) && strpos($guestbook[$i]['website'], 'http://') === false)
			$guestbook[$i]['website'] = 'http://' . $guestbook[$i]['website'];

		$guestbook[$i]['mail'] = !empty($guestbook[$i]['user_mail']) ? substr($guestbook[$i]['user_mail'], 0, -2) : $guestbook[$i]['mail'];
	}
	$tpl->assign('guestbook', $guestbook);
}
$content = $tpl->fetch('guestbook/list.html');
