<?php
namespace ACP3\Modules\Contact;
use ACP3\Core\ModuleInstaller;

class ContactInstaller extends ModuleInstaller {
	private $module_name = 'contact';
	private $schema_version = 32;

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
			'address' => '',
			'disclaimer' => '',
			'fax' => '',
			'mail' => '',
			'telephone' => '',
		);
	}

	protected function schemaUpdates() {
		return array(
			31 => array(
				"UPDATE `{pre}acl_resources` SET privilege_id = 7 WHERE page = 'acp_list' AND module_id = " . $this->getModuleId() . ";"
			),
			32 => array(
				"INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `page`, `params`, `privilege_id`) VALUES('', " . $this->getModuleId() . ", 'sidebar', '', 1);",
			)
		);
	}
}