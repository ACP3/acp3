<?php
/**
 * System
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

ACP3_View::setContent(ACP3_View::fetchTemplate('system/maintenance.tpl'));
