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

if (validate::isNumber($uri->id) && $db->countRows('*', 'menu_items', 'id = \'' . $uri->id . '\'') == '1') {
	require_once ACP3_ROOT . 'modules/menu_items/functions.php';

	if (isset($_POST['submit'])) {
		$form = $_POST['form'];

		if (!validate::date($form['start'], $form['end']))
			$errors[] = $lang->t('common', 'select_date');
		if (!validate::isNumber($form['mode']))
			$errors[] = $lang->t('menu_items', 'select_page_type');
		if (strlen($form['title']) < 3)
			$errors[] = $lang->t('menu_items', 'title_to_short');
		if (!validate::isNumber($form['block_id']))
			$errors[] = $lang->t('menu_items', 'select_block');
		if (!empty($form['parent']) && !validate::isNumber($form['parent']))
			$errors[] = $lang->t('menu_items', 'select_superior_page');
		if (!empty($form['parent']) && validate::isNumber($form['parent'])) {
			// Überprüfen, ob sich die ausgewählte übergeordnete Seite im selben Block befindet
			$parent_block = $db->select('block_id', 'menu_items', 'id = \'' . $form['parent'] . '\'');
			if (!empty($parent_block) && $parent_block[0]['block_id'] != $form['block_id'])
				$errors[] = $lang->t('menu_items', 'superior_page_not_allowed');
		}
		if ($form['display'] != '0' && $form['display'] != '1')
			$errors[] = $lang->t('menu_items', 'select_item_visibility');
		if (!validate::isNumber($form['target']) ||
			$form['mode'] == '1' && (!is_dir(ACP3_ROOT . 'modules/' . $form['module']) || preg_match('=/=', $form['module'])) ||
			$form['mode'] == '2' && !validate::internalURI($form['uri']) ||
			$form['mode'] == '3' && empty($form['uri']))
			$errors[] = $lang->t('menu_items', 'type_in_uri_and_target');

		if (isset($errors)) {
			$tpl->assign('error_msg', comboBox($errors));
		} else {
			// Vorgenommene Änderungen am Datensatz anwenden
			$update_values = array(
				'start' => $date->timestamp($form['start']),
				'end' => $date->timestamp($form['end']),
				'mode' => $form['mode'],
				'block_id' => $form['block_id'],
				'display' => $form['display'],
				'title' => $db->escape($form['title']),
				'uri' => $form['mode'] == '1' ? $form['module'] : $db->escape($form['uri'], 2),
				'target' => $form['target'],
			);

			// Die aktuelle Seite mit allen untergeordneten Seiten selektieren
			$pages = $db->query('SELECT c.id, c.root_id, c.left_id, c.right_id, c.block_id FROM ' . CONFIG_DB_PRE . 'menu_items AS p, ' . CONFIG_DB_PRE . 'menu_items AS c WHERE p.id = \'' . $uri->id . '\' AND c.left_id BETWEEN p.left_id AND p.right_id ORDER BY c.left_id ASC');
			$c_pages = count($pages);

			// Überprüfen, ob Seite ein Root-Element ist und ob dies auch so bleiben soll
			if (empty($form['parent']) && $form['block_id'] == $pages[0]['block_id'] && $db->countRows('*', 'menu_items', 'left_id < ' . $pages[0]['left_id'] . ' AND right_id > ' . $pages[0]['right_id']) == 0) {
				$bool = $db->update('menu_items', $update_values, 'id = \'' . $uri->id . '\'');
			} else {
				// Überprüfung, falls Seite kein Root-Element ist, aber keine Veränderung vorgenommen werden soll...
				$chk_parent = $db->query('SELECT p.id FROM ' . CONFIG_DB_PRE . 'menu_items p, ' . CONFIG_DB_PRE . 'menu_items c WHERE c.left_id BETWEEN p.left_id AND p.right_id AND c.id = ' . $uri->id . ' ORDER BY p.left_id DESC LIMIT 2');
				if (isset($chk_parent[1]) && $chk_parent[1]['id'] == $form['parent']) {
					$bool = $db->update('menu_items', $update_values, 'id = \'' . $uri->id . '\'');
				// ...ansonsten den Baum bearbeiten...
				} else {
					$bool = null;
					// Differenz zwischen linken und rechten Wert bilden
					$page_diff = $pages[0]['right_id'] - $pages[0]['left_id'] + 1;

					// Neues Elternelement
					$new_parent = $db->select('root_id, left_id, right_id', 'menu_items', 'id = \'' . $form['parent'] . '\'');

					// Rekursion verhindern
					if (!empty($new_parent) && $new_parent[0]['left_id'] < $pages[0]['left_id'] && $new_parent[0]['right_id'] > $pages[0]['right_id']) {
						$bool = null;
					} else {
						if (empty($new_parent)) {
							// Root-Element in anderen Block verschieben
							if ($pages[0]['block_id'] != $form['block_id']) {
								$new_block = $db->select('left_id', 'menu_items', 'block_id = \'' . $form['block_id'] . '\'', 'left_id ASC', 1);
								$root_id = $uri->id;

								if ($form['block_id'] > $pages[0]['block_id']) {
									$new_block[0]['left_id'] = $new_block[0]['left_id'] - $page_diff;
									$diff = $new_block[0]['left_id'] - $pages[0]['left_id'];
								} else {
									$diff = -1 * ($pages[0]['left_id'] - $new_block[0]['left_id']);
								}

								$db->link->beginTransaction();
								$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET right_id = right_id - ' . $page_diff . ' WHERE left_id < ' . $pages[0]['left_id'] . ' AND right_id > ' . $pages[0]['right_id'], 0);
								$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET left_id = left_id - ' . $page_diff . ', right_id = right_id - ' . $page_diff . ' WHERE left_id > ' . $pages[0]['right_id'], 0);
								$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET left_id = left_id + ' . $page_diff . ', right_id = right_id + ' . $page_diff . ' WHERE left_id >= ' . $new_block[0]['left_id'], 0);
							// Element zum neuen Elternknoten machen
							} else {
								$new_parent = $db->select('right_id', 'menu_items', 'block_id =  \'' . $pages[0]['block_id'] . '\'', 'right_id DESC', 1);
								$root_id = $uri->id;
								$diff = $new_parent[0]['right_id'] - $pages[0]['right_id'];

								$db->link->beginTransaction();
								$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET right_id = right_id - ' . $page_diff . ' WHERE left_id < ' . $pages[0]['left_id'] . ' AND right_id > ' . $pages[0]['right_id'], 0);
								$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET left_id = left_id - ' . $page_diff . ', right_id = right_id - ' . $page_diff . ' WHERE left_id > ' . $pages[0]['right_id'] . ' AND block_id = \'' . $pages[0]['block_id'] . '\'', 0);
							}
						} else {
							// Teilbaum nach unten...
							if ($new_parent[0]['left_id'] > $pages[0]['left_id']) {
								$new_parent[0]['left_id'] = $new_parent[0]['left_id'] - $page_diff;
								$new_parent[0]['right_id'] = $new_parent[0]['right_id'] - $page_diff;
								$diff = $new_parent[0]['left_id'] - $pages[0]['left_id'] + 1;
							// ...bzw. nach oben verschieben
							} else {
								$diff = -1 * ($pages[0]['left_id'] - $new_parent[0]['left_id'] - 1);
							}
							$root_id = $new_parent[0]['root_id'];

							$db->link->beginTransaction();
							$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET right_id = right_id - ' . $page_diff . ' WHERE left_id < ' . $pages[0]['left_id'] . ' AND right_id > ' . $pages[0]['right_id'], 0);
							$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET left_id = left_id - ' . $page_diff . ', right_id = right_id - ' . $page_diff . ' WHERE left_id > ' . $pages[0]['right_id'], 0);
							$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET right_id = right_id + ' . $page_diff . ' WHERE left_id <= ' . $new_parent[0]['left_id'] . ' AND right_id >= ' . $new_parent[0]['right_id'], 0);
							$db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET left_id = left_id + ' . $page_diff . ', right_id = right_id + ' . $page_diff . ' WHERE left_id > ' . $new_parent[0]['left_id'], 0);
						}

						// Einträge aktualisieren
						for ($i = 0; $i < $c_pages; ++$i) {
							$bool = $db->query('UPDATE ' . CONFIG_DB_PRE . 'menu_items SET block_id = \'' . $form['block_id'] . '\', root_id = \'' . $root_id . '\', left_id = ' . ($pages[$i]['left_id'] + $diff) . ', right_id = ' . ($pages[$i]['right_id'] + $diff) . ' WHERE id = \'' . $pages[$i]['id'] . '\'', 0);
							if ($bool == null)
								break;
						}
						$db->link->commit();
					}
				}
			}

			setMenuItemsCache();

			$content = comboBox($bool !== null ? $lang->t('common', 'edit_success') : $lang->t('common', 'edit_error'), uri('acp/menu_items'));
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		$page = $db->select('id, start, end, mode, block_id, left_id, right_id, display, title, uri, target', 'menu_items', 'id = \'' . $uri->id . '\'');
		$page[0]['uri'] = $db->escape($page[0]['uri'], 3);

		// Seitentyp
		$mode[0]['value'] = 1;
		$mode[0]['selected'] = selectEntry('mode', '1', $page[0]['mode']);
		$mode[0]['lang'] = $lang->t('menu_items', 'module');
		$mode[1]['value'] = 2;
		$mode[1]['selected'] = selectEntry('mode', '2', $page[0]['mode']);
		$mode[1]['lang'] = $lang->t('menu_items', 'dynamic_page');
		$mode[2]['value'] = 3;
		$mode[2]['selected'] = selectEntry('mode', '3', $page[0]['mode']);
		$mode[2]['lang'] = $lang->t('menu_items', 'hyperlink');

		// Block
		$blocks = $db->select('id, title', 'menu_items_blocks', 0, 'title ASC, id ASC');
		$c_blocks = count($blocks);
		for ($i = 0; $i < $c_blocks; ++$i) {
			$blocks[$i]['selected'] = selectEntry('block_id', $blocks[$i]['id'], $page[0]['block_id']);
		}

		// Module
		$modules = modules::modulesList();
		foreach ($modules as $row) {
			$modules[$row['name']]['selected'] = selectEntry('module', $row['dir'], $page[0]['mode'] == '1' ? $page[0]['uri'] : '');
		}
		if ($page[0]['mode'] == '1')
			$page[0]['uri'] = '';

		// Ziel des Hyperlinks
		$target[0]['value'] = 1;
		$target[0]['selected'] = selectEntry('target', '1', $page[0]['target']);
		$target[0]['lang'] = $lang->t('common', 'window_self');
		$target[1]['value'] = 2;
		$target[1]['selected'] = selectEntry('target', '2', $page[0]['target']);
		$target[1]['lang'] = $lang->t('common', 'window_blank');

		$display[0]['value'] = 1;
		$display[0]['selected'] = selectEntry('display', 1, $page[0]['display'], 'checked');
		$display[0]['lang'] = $lang->t('common', 'yes');
		$display[1]['value'] = 0;
		$display[1]['selected'] = selectEntry('display', '0', $page[0]['display'], 'checked');
		$display[1]['lang'] = $lang->t('common', 'no');

		// Daten an Smarty übergeben
		$tpl->assign('start_date', datepicker('start', $page[0]['start']));
		$tpl->assign('end_date', datepicker('end', $page[0]['end']));
		$tpl->assign('mode', $mode);
		$tpl->assign('blocks', $blocks);
		$tpl->assign('modules', $modules);
		$tpl->assign('target', $target);
		$tpl->assign('display', $display);
		$tpl->assign('form', isset($form) ? $form : $page[0]);

		// Übergeordnete Seite herausfinden
		$parent = $db->select('id', 'menu_items', 'left_id < ' . $page[0]['left_id'] . ' AND right_id > ' . $page[0]['right_id'], 'left_id DESC', 1);
		$tpl->assign('pages_list', pagesList(!empty($parent) ? $parent[0]['id'] : 0, $page[0]['left_id'], $page[0]['right_id']));

		$content = $tpl->fetch('menu_items/edit.html');
	}
} else {
	redirect('errors/404');
}
?>