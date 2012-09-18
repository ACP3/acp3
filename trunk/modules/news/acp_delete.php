<?php
/**
 * News
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (isset($_POST['entries']) && is_array($_POST['entries']) === true)
	$entries = $_POST['entries'];
elseif (ACP3_Validate::deleteEntries(ACP3_CMS::$uri->entries) === true)
	$entries = ACP3_CMS::$uri->entries;

if (!isset($entries)) {
	ACP3_CMS::setContent(errorBox(ACP3_CMS::$lang->t('system', 'no_entries_selected')));
} elseif (is_array($entries) === true) {
	$marked_entries = implode('|', $entries);
	ACP3_CMS::setContent(confirmBox(ACP3_CMS::$lang->t('system', 'confirm_delete'), ACP3_CMS::$uri->route('acp/news/delete/entries_' . $marked_entries . '/action_confirmed/'), ACP3_CMS::$uri->route('acp/news')));
} elseif (ACP3_CMS::$uri->action === 'confirmed') {
	$marked_entries = explode('|', $entries);
	$bool = false;
	$commentsInstalled = ACP3_Modules::isInstalled('comments');
	foreach ($marked_entries as $entry) {
		$bool = ACP3_CMS::$db2->delete(DB_PRE . 'news', array('id' => $entry));
		if ($commentsInstalled === true)
			ACP3_CMS::$db2->delete(DB_PRE . 'comments', array('module_id' => 'news', 'entry_id' => $entry));
		// News Cache löschen
		ACP3_Cache::delete('details_id_' . $entry, 'news');
		ACP3_SEO::deleteUriAlias('news/details/id_' . $entry);
	}
	setRedirectMessage($bool, ACP3_CMS::$lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/news');
} else {
	ACP3_CMS::$uri->redirect('errors/404');
}