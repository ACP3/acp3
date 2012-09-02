<?php
/**
 * Gallery
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (isset($_POST['submit']) === true) {
	if (ACP3_Validate::date($_POST['start'], $_POST['end']) === false)
		$errors[] = ACP3_CMS::$lang->t('common', 'select_date');
	if (strlen($_POST['name']) < 3)
		$errors['name'] = ACP3_CMS::$lang->t('gallery', 'type_in_gallery_name');
	if ((bool) CONFIG_SEO_ALIASES === true && !empty($_POST['alias']) &&
		(ACP3_Validate::isUriSafe($_POST['alias']) === false || ACP3_Validate::uriAliasExists($_POST['alias']) === true))
		$errors['alias'] = ACP3_CMS::$lang->t('common', 'uri_alias_unallowed_characters_or_exists');

	if (isset($errors) === true) {
		ACP3_CMS::$view->assign('error_msg', errorBox($errors));
	} elseif (ACP3_Validate::formToken() === false) {
		ACP3_CMS::setContent(errorBox(ACP3_CMS::$lang->t('common', 'form_already_submitted')));
	} else {
		$insert_values = array(
			'id' => '',
			'start' => $_POST['start'],
			'end' => $_POST['end'],
			'name' => ACP3_CMS::$db->escape($_POST['name']),
			'user_id' => ACP3_CMS::$auth->getUserId(),
		);

		$bool = ACP3_CMS::$db->insert('gallery', $insert_values);
		if ((bool) CONFIG_SEO_ALIASES === true && !empty($_POST['alias']))
			ACP3_SEO::insertUriAlias('gallery/pics/id_' . ACP3_CMS::$db->link->lastInsertID(), $_POST['alias'], ACP3_CMS::$db->escape($_POST['seo_keywords']), ACP3_CMS::$db->escape($_POST['seo_description']), (int) $_POST['seo_robots']);

		ACP3_CMS::$session->unsetFormToken();

		setRedirectMessage($bool, ACP3_CMS::$lang->t('common', $bool !== false ? 'create_success' : 'create_error'), 'acp/gallery');
	}
}
if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
	// Datumsauswahl
	ACP3_CMS::$view->assign('publication_period', ACP3_CMS::$date->datepicker(array('start', 'end')));

	ACP3_CMS::$view->assign('SEO_FORM_FIELDS', ACP3_SEO::formFields());

	ACP3_CMS::$view->assign('form', isset($_POST['submit']) ? $_POST : array('name' => '', 'alias' => '', 'seo_keywords' => '', 'seo_description' => ''));

	ACP3_CMS::$session->generateFormToken();

	ACP3_CMS::setContent(ACP3_CMS::$view->fetchTemplate('gallery/acp_create.tpl'));
}
