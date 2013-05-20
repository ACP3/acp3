<?php
namespace ACP3\Modules\Captcha;
use ACP3\Core\ModuleInstaller;

class CaptchaInstaller extends ModuleInstaller {
	private $module_name = 'captcha';
	private $schema_version = 31;

	protected function getName() {
		return $this->module_name;
	}

	protected function getSchemaVersion() {
		return $this->schema_version;
	}

	protected function createTables() {
		return array();
	}

	protected function removeTables() {
		return array();
	}

	protected function settings() {
		return array();
	}

	protected function schemaUpdates() {
		return array(
			31 => array(
				"DELETE FROM `{pre}acl_resources` WHERE `module_id` = " . $this->getModuleId() . " AND page = \"functions\";",
			) 
		);
	}
}