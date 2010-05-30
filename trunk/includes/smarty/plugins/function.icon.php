<?php
function smarty_function_icon($params)
{
	$path = 'images/crystal/' . $params['path'] . '.png';
	$width = $height = '';

	if (validate::isNumber($params['width']) && validate::isNumber($params['height'])) {
		$width = ' width="' . $params['width'] . '"';
		$height = ' height="' . $params['height'] . '"';
	} elseif (is_file(ACP3_ROOT . $path)) {
		$picInfos = getimagesize(ACP_ROOT . $path);
		$width = ' width="' . $picInfos[0] . '"';
		$height = ' height="' . $picInfos[1] . '"';
	}

	$alt = !empty($params['alt']) ? ' alt="' . $params['alt'] . '"' : '';
	$title = !empty($params['title']) ? ' title="' . $params['title'] . '"' : '';

	return '<img src="' . ROOT_DIR . $path . '"' . $width . $height . $alt . $title . ' />';
}
/* vim: set expandtab: */
?>