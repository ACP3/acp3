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

if (validate::isNumber($uri->id) && $db->select('COUNT(id)', 'pages', 'id = \'' . $uri->id . '\'', 0, 0, 0, 1) == '1') {
	if (isset($_POST['submit'])) {
		$form = $_POST['form'];

		if (!validate::date($form['start'], $form['end']))
			$errors[] = $lang->t('common', 'select_date');
		if (!validate::isNumber($form['mode']))
			$errors[] = $lang->t('pages', 'select_static_hyperlink');
		if (!validate::isNumber($form['blocks']))
			$errors[] = $lang->t('pages', 'select_block');
		if (strlen($form['title']) < 3)
			$errors[] = $lang->t('pages', 'title_to_short');
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

			// Vorgenommene Änderungen am Datensatz anwenden
			$update_values = array(
				'start' => $date->timestamp($form['start']),
				'end' => $date->timestamp($form['end']),
				'mode' => $form['mode'],
				'block_id' => $form['blocks'],
				'title' => $db->escape($form['title']),
				'uri' => $db->escape($form['uri'], 2),
				'target' => $form['target'],
				'text' => $db->escape($form['text'], 2),
			);

			$page = $db->query('SELECT c.id, c.root_id, c.left_id, c.right_id FROM ' . CONFIG_DB_PRE . 'pages AS p, ' . CONFIG_DB_PRE . 'pages AS c WHERE p.id = \'' . $uri->id . '\' AND c.left_id BETWEEN p.left_id AND p.right_id ORDER BY c.left_id ASC');
			// Überprüfen, ob Seite ein Root-Element ist und ob dies auch sop bleiben soll
			if (empty($form['parent']) && $db->select('id', 'pages', 'left_id < ' . $page[0]['left_id'] . ' AND right_id > ' . $page[0]['right_id'], 0, 0, 0, 1) == 0) {
				$bool = $db->update('pages', $update_values, 'id = \'' . $uri->id . '\'');
			} else {
				// Überprüfung, falls Seite kein Root-Element ist, aber keine Veränderung vorgenommen werden soll...
				$chk_parent = $db->query('SELECT p.id FROM ' . CONFIG_DB_PRE . 'pages p, ' . CONFIG_DB_PRE . 'pages c WHERE c.left_id BETWEEN p.left_id AND p.right_id AND c.id = ' . $uri->id . ' ORDER BY p.left_id LIMIT 1');
				if ($chk_parent[0]['id'] == $form['parent']) {
					$bool = $db->update('pages', $update_values, 'id = \'' . $uri->id . '\'');
				// ...ansonsten den Baum bearbeiten...
				} else {
					$page_diff = $page[0]['right_id'] - $page[0]['left_id'] + 1;

					// Elternknoten der aktuellen Seite anpassen
					$db->query('UPDATE ' . CONFIG_DB_PRE . 'pages SET right_id = right_id - ' . $page_diff . ' WHERE root_id = ' . $page[0]['root_id'] . ' AND left_id < ' . $page[0]['left_id'], 0);
					// Alle nachfolgenden Seiten anpassen
					$db->query('UPDATE ' . CONFIG_DB_PRE . 'pages SET left_id = left_id - ' . $page_diff . ', right_id = right_id - ' . $page_diff . ' WHERE left_id > ' . $page[0]['right_id'], 0);

					// Neues Elternelement
					$new_parent = $db->select('root_id, left_id', 'pages', 'id = \'' . $form['parent'] . '\'');
					if (empty($new_parent)) {
						echo 'huhu';
						$new_parent = $db->select('right_id', 'pages', 0, 'right_id DESC', 1);
						$root_id = $uri->id;
						$left_id = $new_parent[0]['right_id'] + 1;
					} else {
						$db->query('UPDATE ' . CONFIG_DB_PRE . 'pages SET right_id = right_id + ' . $page_diff . ' WHERE root_id = \'' . $new_parent[0]['root_id'] . '\' AND left_id <= ' . $new_parent[0]['left_id'], 0);
						$db->query('UPDATE ' . CONFIG_DB_PRE . 'pages SET left_id = left_id + ' . $page_diff . ', right_id = right_id + ' . $page_diff . ' WHERE left_id > ' . $new_parent[0]['left_id'], 0);

						$root_id = $new_parent[0]['root_id'];
						$left_id = $new_parent[0]['left_id'] + 1;
					}

					$bool = false;
					$c_page = count($page);
					for ($i = 0; $i < $c_page; ++$i) {
						$position = array(
							'root_id' => $root_id,
							'left_id' => $left_id + $i,
							'right_id' => $left_id + $i + ($page[$i]['right_id'] - $page[$i]['left_id']),
						);
						$bool = $db->update('pages', $i == 0 ? array_merge($update_values, $position) : $parent, 'id = \'' . $page[$i]['id'] . '\'');
					}
				}
			}

			setNavbarCache();

			$content = comboBox($bool ? $lang->t('pages', 'edit_success') : $lang->t('pages', 'edit_error'), uri('acp/pages'));
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		$page = $db->select('id, start, end, mode, block_id, left_id, right_id, title, uri, target, text', 'pages', 'id = \'' . $uri->id . '\'');
		$page[0]['text'] = $db->escape($page[0]['text'], 3);
		$page[0]['uri'] = $db->escape($page[0]['uri'], 3);

		// Datumsauswahl
		$tpl->assign('start_date', datepicker('start', $page[0]['start']));
		$tpl->assign('end_date', datepicker('end', $page[0]['end']));

		// Seitentyp
		$mode[0]['value'] = 1;
		$mode[0]['selected'] = selectEntry('mode', '1', $page[0]['mode']);
		$mode[0]['lang'] = $lang->t('pages', 'static_page');
		$mode[1]['value'] = 2;
		$mode[1]['selected'] = selectEntry('mode', '2', $page[0]['mode']);
		$mode[1]['lang'] = $lang->t('pages', 'dynamic_page');
		$mode[2]['value'] = 3;
		$mode[2]['selected'] = selectEntry('mode', '3', $page[0]['mode']);
		$mode[2]['lang'] = $lang->t('pages', 'hyperlink');
		$tpl->assign('mode', $mode);

		// Block
		$blocks = $db->select('id, title', 'pages_blocks', 0, 'title ASC, id ASC');
		$c_blocks = count($blocks);
		for ($i = 0; $i < $c_blocks; ++$i) {
			$blocks[$i]['selected'] = selectEntry('blocks', $blocks[$i]['id'], $page[0]['block_id']);
		}
		$blocks[$c_blocks]['id'] = '0';
		$blocks[$c_blocks]['index_name'] = 'dot_display';
		$blocks[$c_blocks]['selected'] = selectEntry('block', '0', $page[0]['block_id']);
		$blocks[$c_blocks]['title'] = $lang->t('pages', 'do_not_display');
		$tpl->assign('blocks', $blocks);

		// Ziel des Hyperlinks
		$target[0]['value'] = 1;
		$target[0]['selected'] = selectEntry('target', '1', $page[0]['target']);
		$target[0]['lang'] = $lang->t('common', 'window_self');
		$target[1]['value'] = 2;
		$target[1]['selected'] = selectEntry('target', '2', $page[0]['target']);
		$target[1]['lang'] = $lang->t('common', 'window_blank');
		$tpl->assign('target', $target);

		$tpl->assign('form', isset($form) ? $form : $page[0]);

		// Übergeordnete Seite herausfinden
		$parent = $db->select('id', 'pages', 'left_id < ' . $page[0]['left_id'] . ' AND right_id > ' . $page[0]['right_id'], 'left_id DESC', 1);
		$tpl->assign('pages_list', pagesList(!empty($parent) ? $parent[0]['id'] : 0, $page[0]['left_id'], $page[0]['right_id']));

		$content = $tpl->fetch('pages/edit.html');
	}
} else {
	redirect('errors/404');
}
?>