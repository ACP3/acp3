<?php
namespace ACP3\Modules\Feeds;
use ACP3\Core\ModuleInstaller;

class FeedsInstaller extends ModuleInstaller {
	private $module_name = 'feeds';
	private $schema_version = 31;

	public function __construct(\ACP3\Core\Pimple $injector) {
		parent::__construct($injector);

		$this->special_resources = array(
			'acp_list' => 7
		);
	}

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
		return array(
			'feed_image' => '',
			'feed_type' => 'RSS 2.0'
		);
	}

	protected function removeSettings() {
		return true;
	}

	protected function schemaUpdates() {
		return array(
			31 => array(
				"INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `page`, `params`, `privilege_id`) VALUES('', " . $this->getModuleId() . ", 'acp_list', '', 7);",
				"INSERT INTO `{pre}settings` (`id`, `module_id`, `name`, `value`) VALUES ('', " . $this->getModuleId() . ", 'feed_image', '');",
				"INSERT INTO `{pre}settings` (`id`, `module_id`, `name`, `value`) VALUES ('', " . $this->getModuleId() . ", 'feed_type', 'RSS 2.0');",
			)
		);
	}
}