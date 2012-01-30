<?php
if (defined('IN_ACP3') === false)
	exit;

$users = $db->select('id, nickname, realname, mail, website', 'users', 0, 'nickname ASC, id ASC', POS, $session->get('entries'));
$c_users = count($users);
$all_users = $db->countRows('*', 'users');

if ($c_users > 0) {
	$tpl->assign('pagination', pagination($all_users));

	for ($i = 0; $i < $c_users; ++$i) {
		$pos = strrpos($users[$i]['realname'], ':');
		$users[$i]['realname_display'] = substr($users[$i]['realname'], $pos + 1);
		$users[$i]['realname'] = substr($db->escape($users[$i]['realname'], 3), 0, $pos);
		$pos = strrpos($users[$i]['mail'], ':');
		$users[$i]['mail_display'] = substr($users[$i]['mail'], $pos + 1);
		$users[$i]['mail'] = substr($users[$i]['mail'], 0, $pos);
		$pos = strrpos($users[$i]['website'], ':');
		$users[$i]['website_display'] = substr($users[$i]['website'], $pos + 1);
		$users[$i]['website'] = substr($db->escape($users[$i]['website'], 3), 0, $pos);
	}
	$tpl->assign('users', $users);
}
$tpl->assign('LANG_users_found', sprintf($lang->t('users', 'users_found'), $all_users));

view::setContent(view::fetchTemplate('users/list.tpl'));
