<?php
/**
 * Categories
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (isset($_POST['submit'])) {
	$form = $_POST['form'];
	if (!empty($_FILES['picture']['name'])) {
		$file['tmp_name'] = $_FILES['picture']['tmp_name'];
		$file['name'] = $_FILES['picture']['name'];
		$file['size'] = $_FILES['picture']['size'];
	}

	if (strlen($form['name']) < 3)
		$errors[] = lang('categories', 'name_to_short');
	if (strlen($form['description']) < 3)
		$errors[] = lang('categories', 'description_to_short');
	if (!empty($file) && (empty($file['tmp_name']) || empty($file['size']) || !$validate->is_picture($file['tmp_name'])))
		$errors[] = lang('categories', 'please_select_an_image');
	if (empty($form['module']))
		$errors[] = lang('categories', 'select_module');
	if (strlen($form['name']) > 3 && !empty($form['module']) && $db->select('id', 'categories', 'name = \'' . $db->escape($form['name']) . '\' AND module = \'' . $db->escape($form['module'], 2) . '\'', 0, 0, 0, 1) > 0)
		$errors[] = lang('categories', 'category_already_exists');

	if (isset($errors)) {
		$tpl->assign('error_msg', combo_box($errors));
	} else {
		$file_sql = null;
		if (!empty($file)) {
			$result = move_file($file['tmp_name'], $file['name'], 'categories');
			$file_sql = array('picture' => $result['name']);
		}

		$insert_values = array(
			'id' => '',
			'name' => $db->escape($form['name']),
			'description' => $db->escape($form['description']),
			'module' => $db->escape($form['module'], 2),
		);
		if (is_array($file_sql)) {
			$insert_values = array_merge($insert_values, $file_sql);
		}

		$bool = $db->insert('categories', $insert_values);

		$cache->create('categories_' . $form['module'], $db->select('id, name, picture, description', 'categories', 'module = \'' . $form['module'] . '\'', 'name ASC'));

		$content = combo_box($bool ? lang('categories', 'create_success') : lang('categories', 'create_error'), uri('acp/categories'));
	}
}
if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
	$tpl->assign('form', isset($form) ? $form : '');

	$mod_list = $modules->modulesList();

	foreach ($mod_list as $name => $info) {
		if ($info['active'] && $info['categories']) {
			$mod_list[$name]['selected'] = select_entry('module', $info['dir']);
		} else {
			unset($mod_list[$name]);
		}
	}
	$tpl->assign('mod_list', $mod_list);

	$content = $tpl->fetch('categories/create.html');
}
?>