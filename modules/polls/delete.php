<?php
/**
 * Polls
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (isset($_POST['entries']) && is_array($_POST['entries']) === true)
	$entries = $_POST['entries'];
elseif (validate::deleteEntries($uri->entries) === true)
	$entries = $uri->entries;

if (!isset($entries)) {
	view::setContent(errorBox($lang->t('common', 'no_entries_selected')));
} elseif (is_array($entries) === true) {
	$marked_entries = implode('|', $entries);
	view::setContent(confirmBox($lang->t('common', 'confirm_delete'), $uri->route('acp/polls/delete/entries_' . $marked_entries . '/action_confirmed/'), $uri->route('acp/polls')));
} elseif (validate::deleteEntries($entries) === true && $uri->action === 'confirmed') {
	$marked_entries = explode('|', $entries);
	$bool = $bool2 = $bool3 = false;
	foreach ($marked_entries as $entry) {
		if (!empty($entry) && validate::isNumber($entry) && $db->countRows('*', 'polls', 'id = \'' . $entry . '\'') == '1') {
			$bool = $db->delete('polls', 'id = \'' . $entry . '\'');
			$bool2 = $db->delete('poll_answers', 'poll_id = \'' . $entry . '\'');
			$bool3 = $db->delete('poll_votes', 'poll_id = \'' . $entry . '\'');
		}
	}
	setRedirectMessage($bool !== false && $bool2 !== false && $bool3 !== false ? $lang->t('common', 'delete_success') : $lang->t('common', 'delete_error'), 'acp/polls');
} else {
	$uri->redirect('acp/errors/404');
}
