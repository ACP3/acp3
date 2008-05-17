<?php
if (!defined('IN_ACP3'))
	exit;

breadcrumb::assign(lang('users', 'users'), uri('users'));
breadcrumb::assign(lang('users', 'view_profile'));

if (validate::isNumber($uri->id) && $db->select('id', 'users', 'id = \'' . $uri->id . '\'', 0, 0, 0, 1) == '1') {
	$user = $auth->getUserInfo($uri->id);

	$user['website'] = $db->escape($user['website'], 3);

	$tpl->assign('user', $user);
}
$content = $tpl->fetch('users/view_profile.html');
?>