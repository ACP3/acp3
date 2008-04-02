<?php
/**
 * Pages
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (isset($_POST['submit'])) {
	$form = $_POST['form'];

	if (!$validate->date($form['start']) || !$validate->date($form['end']))
		$errors[] = lang('common', 'select_date');
	if (!$validate->isNumber($form['mode']))
		$errors[] = lang('pages', 'select_static_hyperlink');
	if (!$validate->isNumber($form['blocks']))
		$errors[] = lang('pages', 'select_block');
	if (!empty($form['blocks']) && !$validate->isNumber($form['sort']))
		$errors[] = lang('pages', 'type_in_chronology');
	if (strlen($form['title']) < 3)
		$errors[] = lang('pages', 'title_to_short');
	if ($form['mode'] == '1' && !empty($form['parent']) && !$validate->isNumber($form['parent']))
		$errors[] = lang('pages', 'select_superior_page');
	if ($form['mode'] == '1' && strlen($form['text']) < 3)
		$errors[] = lang('pages', 'text_to_short');
	if (($form['mode'] == '2' || $form['mode'] == '3') && (empty($form['uri']) || !$validate->isNumber($form['target'])))
		$errors[] = lang('pages', 'type_in_uri_and_target');

	if (isset($errors)) {
		$tpl->assign('error_msg', comboBox($errors));
	} else {
		$start_date = strtotime($form['start'], dateAligned(2, time()));
		$end_date = strtotime($form['end'], dateAligned(2, time()));

		if ($form['mode'] == '1') {
			$form['uri'] = '';
			$form['target'] = '';
		} else {
			$form['parent'] = '';
			$form['text'] = '';
		}

		$insert_values = array(
			'id' => '',
			'start' => $start_date,
			'end' => $end_date,
			'mode' => $form['mode'],
			'parent' => $form['parent'],
			'block_id' => $form['blocks'],
			'sort' => $form['sort'],
			'title' => $db->escape($form['title']),
			'uri' => $db->escape($form['uri'], 2),
			'target' => $form['target'],
			'text' => $db->escape($form['text'], 2),
		);

		$bool = $db->insert('pages', $insert_values);

		$cache->create('pages', $db->select('p.id, p.start, p.end, p.mode, p.title, p.uri, p.target, b.index_name AS block_name', 'pages AS p, ' . CONFIG_DB_PRE . 'pages_blocks AS b', 'p.block_id != \'0\' AND p.block_id = b.id', 'p.sort ASC, p.title ASC'));

		$content = comboBox($bool ? lang('pages', 'create_success') : lang('pages', 'create_error'), uri('acp/pages'));
	}
}
if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
	//Funktionen einbinden
	include_once ACP3_ROOT . 'modules/pages/functions.php';

	// Datumsauswahl
	$tpl->assign('start_date', datepicker('start'));
	$tpl->assign('end_date', datepicker('end'));

	$mode[0]['value'] = 1;
	$mode[0]['selected'] = selectEntry('mode', '1');
	$mode[0]['lang'] = lang('pages', 'static_page');
	$mode[1]['value'] = 2;
	$mode[1]['selected'] = selectEntry('mode', '2');
	$mode[1]['lang'] = lang('pages', 'dynamic_page');
	$mode[2]['value'] = 3;
	$mode[2]['selected'] = selectEntry('mode', '3');
	$mode[2]['lang'] = lang('pages', 'hyperlink');
	$tpl->assign('mode', $mode);

	$blocks = $db->select('id, title', 'pages_blocks');
	$c_blocks = $validate->countArrayElements($blocks);

	for ($i = 0; $i < $c_blocks; $i++) {
		$blocks[$i]['selected'] = selectEntry('block', $blocks[$i]['id']);
	}
	$blocks[$c_blocks]['id'] = 0;
	$blocks[$c_blocks]['index_name'] = 'dot_display';
	$blocks[$c_blocks]['selected'] = selectEntry('blocks', '0');
	$blocks[$c_blocks]['title'] = lang('pages', 'do_not_display');
	$tpl->assign('blocks', $blocks);

	$target[0]['value'] = 1;
	$target[0]['selected'] = selectEntry('target', '1');
	$target[0]['lang'] = lang('common', 'window_self');
	$target[1]['value'] = 2;
	$target[1]['selected'] = selectEntry('target', '2');
	$target[1]['lang'] = lang('common', 'window_blank');
	$tpl->assign('target', $target);

	$tpl->assign('form', isset($form) ? $form : '');

	$tpl->assign('pages_list', pagesList());

	$content = $tpl->fetch('pages/create.html');
}
?>