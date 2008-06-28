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

// Funktionen einbinden
include_once ACP3_ROOT . 'modules/pages/functions.php';

if (isset($_POST['submit'])) {
	$form = $_POST['form'];

	if (!validate::date($form['start'], $form['end']))
		$errors[] = $lang->t('common', 'select_date');
	if (!validate::isNumber($form['mode']))
		$errors[] = $lang->t('pages', 'select_static_hyperlink');
	if (!validate::isNumber($form['blocks']))
		$errors[] = $lang->t('pages', 'select_block');
	if (!empty($form['blocks']) && !validate::isNumber($form['sort']))
		$errors[] = $lang->t('pages', 'type_in_chronology');
	if (strlen($form['title']) < 3)
		$errors[] = $lang->t('pages', 'title_to_short');
	if (!empty($form['parent']) && !validate::isNumber($form['parent']))
		$errors[] = $lang->t('pages', 'select_superior_page');
	if ($form['mode'] == '1' && strlen($form['text']) < 3)
		$errors[] = $lang->t('pages', 'text_to_short');
	if (($form['mode'] == '2' || $form['mode'] == '3') && (empty($form['uri']) || !validate::isNumber($form['target'])))
		$errors[] = $lang->t('pages', 'type_in_uri_and_target');

	if (isset($errors)) {
		$tpl->assign('error_msg', comboBox($errors));
	} else {
		if ($form['mode'] == '1') {
			$form['uri'] = '';
			$form['target'] = '';
		} else {
			$form['text'] = '';
		}

		$insert_values = array(
			'id' => '',
			'start' => $date->timestamp($form['start']),
			'end' => $date->timestamp($form['end']),
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

		generatePagesCache();

		$content = comboBox($bool ? $lang->t('pages', 'create_success') : $lang->t('pages', 'create_error'), uri('acp/pages'));
	}
}
if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
	// Datumsauswahl
	$tpl->assign('start_date', datepicker('start'));
	$tpl->assign('end_date', datepicker('end'));

	$mode[0]['value'] = 1;
	$mode[0]['selected'] = selectEntry('mode', '1');
	$mode[0]['lang'] = $lang->t('pages', 'static_page');
	$mode[1]['value'] = 2;
	$mode[1]['selected'] = selectEntry('mode', '2');
	$mode[1]['lang'] = $lang->t('pages', 'dynamic_page');
	$mode[2]['value'] = 3;
	$mode[2]['selected'] = selectEntry('mode', '3');
	$mode[2]['lang'] = $lang->t('pages', 'hyperlink');
	$tpl->assign('mode', $mode);

	$blocks = $db->select('id, title', 'pages_blocks');
	$c_blocks = count($blocks);

	for ($i = 0; $i < $c_blocks; ++$i) {
		$blocks[$i]['selected'] = selectEntry('block', $blocks[$i]['id']);
	}
	$blocks[$c_blocks]['id'] = 0;
	$blocks[$c_blocks]['index_name'] = 'dot_display';
	$blocks[$c_blocks]['selected'] = selectEntry('blocks', '0');
	$blocks[$c_blocks]['title'] = $lang->t('pages', 'do_not_display');
	$tpl->assign('blocks', $blocks);

	$target[0]['value'] = 1;
	$target[0]['selected'] = selectEntry('target', '1');
	$target[0]['lang'] = $lang->t('common', 'window_self');
	$target[1]['value'] = 2;
	$target[1]['selected'] = selectEntry('target', '2');
	$target[1]['lang'] = $lang->t('common', 'window_blank');
	$tpl->assign('target', $target);

	$defaults = array(
		'title' => '',
		'sort' => '',
		'text' => '',
		'uri' => ''
	);

	$tpl->assign('form', isset($form) ? $form : $defaults);
	$tpl->assign('pages_list', pagesList(2));

	$content = $tpl->fetch('pages/create.html');
}
?>