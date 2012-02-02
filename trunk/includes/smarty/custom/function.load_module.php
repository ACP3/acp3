<?php
function smarty_function_load_module($params)
{
	$module = explode('|', $params['module']);

	if (modules::check($module[0], $module[1]) == 1) {
		global $auth, $date, $db, $lang, $tpl, $uri;

		include MODULES_DIR . $module[0] . '/' . $module[1] . '.php';
	}
}
/* vim: set expandtab: */
?>