<?php
/**
 * Newsletter
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (isset($_POST['entries']) && is_array($_POST['entries']))
	$entries = $_POST['entries'];
elseif (preg_match('/^([\d|]+)$/', $modules->entries))
	$entries = $modules->entries;

if (!isset($entries)) {
	$content = comboBox(array(lang('common', 'no_entries_selected')));
} elseif (is_array($entries)) {
	$marked_entries = implode('|', $entries);
	$content = comboBox(lang('newsletter', 'confirm_archive_delete'), uri('acp/newsletter/delete_archive/entries_' . $marked_entries), uri('acp/newsletter/adm_list_archive'));
} elseif (preg_match('/^([\d|]+)$/', $entries) && $modules->confirmed) {
	$marked_entries = explode('|', $entries);
	$bool = 0;
	foreach ($marked_entries as $entry) {
		if (!empty($entry) && validate::isNumber($entry) && $db->select('id', 'newsletter_archive', 'id = \'' . $entry . '\'', 0, 0, 0, 1) == '1') {
			$bool = $db->delete('newsletter_archive', 'id = \'' . $entry . '\'');
		}
	}
	$content = comboBox($bool ? lang('newsletter', 'delete_archive_success') : lang('newsletter', 'delete_archive_error'), uri('acp/newsletter/adm_list_archive'));
}
?>