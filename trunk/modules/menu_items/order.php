<?php
/**
 * Pages
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (validate::isNumber($uri->id) === true && $db->countRows('*', 'menu_items', 'id = \'' . $uri->id . '\'') == 1) {
	require_once MODULES_DIR . 'menu_items/functions.php';

	$pages = $db->query('SELECT c.id, c.block_id, c.left_id, c.right_id FROM {pre}menu_items AS p, {pre}menu_items AS c WHERE p.id = \'' . $uri->id . '\' AND c.left_id BETWEEN p.left_id AND p.right_id ORDER BY c.left_id ASC');

	if ($uri->action === 'up' && $db->countRows('*', 'menu_items', 'right_id = ' . ($pages[0]['left_id'] - 1) . ' AND block_id = \'' . $pages[0]['block_id'] . '\'') > 0) {
		// Vorherigen Menüpunkt mit allen Kindern selektieren
		$elem = $db->query('SELECT c.id, c.left_id, c.right_id FROM {pre}menu_items AS p, {pre}menu_items AS c WHERE p.right_id = ' . ($pages[0]['left_id'] - 1) . ' AND c.left_id BETWEEN p.left_id AND p.right_id ORDER BY c.left_id ASC');
		$diff_left = $pages[0]['left_id'] - $elem[0]['left_id'];
		$diff_right = $pages[0]['right_id'] - $elem[0]['right_id'];
	} elseif ($uri->action === 'down' && $db->countRows('*', 'menu_items', 'left_id = ' . ($pages[0]['right_id'] + 1) . ' AND block_id = \'' . $pages[0]['block_id'] . '\'') > 0) {
		// Nachfolgenden Menüpunkt mit allen Kindern selektieren
		$elem = $db->query('SELECT c.id, c.left_id, c.right_id FROM {pre}menu_items AS p, {pre}menu_items AS c WHERE p.left_id = ' . ($pages[0]['right_id'] + 1) . ' AND c.left_id BETWEEN p.left_id AND p.right_id ORDER BY c.left_id ASC');
		$diff_left = $elem[0]['left_id'] - $pages[0]['left_id'];
		$diff_right = $elem[0]['right_id'] - $pages[0]['right_id'];
	} else {
		$uri->redirect('errors/404');
	}

	$c_elem = count($elem);
	$c_pages = count($pages);
	$elem_ids = $pages_ids = '';

	for ($i = 0; $i < $c_elem; ++$i) {
		$elem_ids.= 'id = \'' . $elem[$i]['id'] . '\' OR ';
	}
	for ($i = 0; $i < $c_pages; ++$i) {
		$pages_ids.= 'id = \'' . $pages[$i]['id'] . '\' OR ';
	}

	$db->link->beginTransaction();

	if ($uri->action === 'up') {
		$bool = $db->query('UPDATE {pre}menu_items SET left_id = left_id + ' . $diff_right . ', right_id = right_id + ' . $diff_right . ' WHERE ' . substr($elem_ids, 0, -4), 0);
		$bool2 = $db->query('UPDATE {pre}menu_items SET left_id = left_id - ' . $diff_left . ', right_id = right_id - ' . $diff_left . ' WHERE ' . substr($pages_ids, 0, -4), 0);
	} elseif ($uri->action === 'down') {
		$bool = $db->query('UPDATE {pre}menu_items SET left_id = left_id - ' . $diff_left . ', right_id = right_id - ' . $diff_left . ' WHERE ' . substr($elem_ids, 0, -4), 0);
		$bool2 = $db->query('UPDATE {pre}menu_items SET left_id = left_id + ' . $diff_right . ', right_id = right_id + ' . $diff_right . ' WHERE ' . substr($pages_ids, 0, -4), 0);
	}

	$db->link->commit();

	setMenuItemsCache();
	$uri->redirect('acp/menu_items');
} else {
	$uri->redirect('errors/404');
}
