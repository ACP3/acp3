<?php
/**
 * Gallery
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (validate::isNumber($uri->id) && $db->select('id', 'gallery_pictures', 'id = \'' . $uri->id . '\'', 0, 0, 0, 1) == '1') {
	$picture = $db->select('p.gallery_id, p.file, p.description, g.name AS gallery_name', 'gallery_pictures AS p, ' . CONFIG_DB_PRE . 'gallery AS g', 'p.id = \'' . $uri->id . '\' AND p.gallery_id = g.id');

	breadcrumb::assign($lang->t('common', 'acp'), uri('acp'));
	breadcrumb::assign($lang->t('gallery', 'gallery'), uri('acp/gallery'));
	breadcrumb::assign($picture[0]['gallery_name'], uri('acp/gallery/edit_gallery/id_' . $picture[0]['gallery_id']));
	breadcrumb::assign($lang->t('gallery', 'edit_picture'));

	if (isset($_POST['submit'])) {
		if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['size'] > '0') {
			$file['tmp_name'] = $_FILES['file']['tmp_name'];
			$file['name'] = $_FILES['file']['name'];
			$file['size'] = $_FILES['file']['size'];
		}
		$form = $_POST['form'];
		$settings = config::output('gallery');

		if (isset($file) && is_array($file) && !validate::isPicture($file['tmp_name'], $settings['maxwidth'], $settings['maxheight'], $settings['filesize']))
			$errors[] = $lang->t('gallery', 'invalid_image_selected');

		if (isset($errors)) {
			$tpl->assign('error_msg', comboBox($errors));
		} else {
			$new_file_sql = null;
			if (isset($file) && is_array($file)) {
				$result = moveFile($file['tmp_name'], $file['name'], 'gallery');
				$new_file_sql['file'] = $result['name'];
			}

			$update_values = array(
				'description' => $db->escape($form['description'], 2),
			);
			if (is_array($new_file_sql)) {
				$old_file = $db->select('file', 'gallery_pictures', 'id = \'' . $uri->id . '\'');
				removeFile('gallery', $old_file[0]['file']);

				$update_values = array_merge($update_values, $new_file_sql);
			}

			$bool = $db->update('gallery_pictures', $update_values, 'id = \'' . $uri->id . '\'');
			setGalleryCache($picture[0]['gallery_id']);

			$content = comboBox($bool ? $lang->t('gallery', 'edit_picture_success') : $lang->t('gallery', 'edit_picture_error'), uri('acp/gallery/edit_gallery/id_' . $picture[0]['gallery_id']));
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		$picture[0]['description'] = $db->escape($picture[0]['description'], 3);
		$tpl->assign('form', isset($form) ? $form : $picture[0]);

		$content = $tpl->fetch('gallery/edit_picture.html');
	}
} else {
	redirect('errors/404');
}
?>