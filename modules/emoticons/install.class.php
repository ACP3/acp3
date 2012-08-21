<?php

class ACP3_EmoticonsModuleInstaller extends ACP3_ModuleInstaller {

	public function createTables() {
		global $db;

		$queries = array(
			"CREATE TABLE `{pre}emoticons` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`code` VARCHAR(10) NOT NULL,
				`description` VARCHAR(15) NOT NULL,
				`img` VARCHAR(40) NOT NULL,
				PRIMARY KEY (`id`)
			) {engine};",
			// Default Smilies
			"INSERT INTO `{pre}emoticons` VALUES ('', ':D', 'Very Happy', '1.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':)', 'Smile', '2.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':(', 'Sad', '3.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':o', 'Surprised', '4.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':shocked:', 'Shocked', '5.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':?', 'Confused', '6.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':8)', 'Cool', '7.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':lol:', 'Laughing', '8.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':x', 'Mad', '9.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':P', 'Razz', '10.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':oops:', 'Embarassed', '11.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':cry:', 'Crying', '12.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':evil:', 'Evil', '13.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':twisted:', 'Twisted Evil', '14.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':roll:', 'Rolling Eyes', '15.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':wink:', 'Wink', '16.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':!:', 'Exclamation', '17.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':?:', 'Question', '18.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':idea:', 'Idea', '19.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':arrow:', 'Arrow', '20.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':|', 'Neutral', '21.gif');",
			"INSERT INTO `{pre}emoticons` VALUES ('', ':mrgreen:', 'Mr. Green', '22.gif');",
		);

		$engine = 'ENGINE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`';
		$bool = false;
		foreach ($queries as $query) {
			$bool = $db->query(str_replace('{engine}', $engine, $query), 0);
		}

		return (bool) $bool;
	}

	public function removeTables() {
		global $db;

		$queries = array(
			"DROP TABLE `{pre}emoticons`;",
		);

		$bool = false;
		foreach ($queries as $query) {
			$bool = $db->query($query, 0);
		}
		return (bool) $bool;
	}

	public function addSettings() {
		global $db;

		$queries = array(
			'width' => 32,
			'height' => 32,
			'filesize' => 10240,
		);

		$bool = false;
		foreach ($queries as $key => $value) {
			$bool = $db->insert('settings', array('id' => '', 'module_id' => $this->module_id, 'name' => $key, 'value' => $value));
		}
		return (bool) $bool;
	}

	public function removeSettings() {
		global $db;

		return (bool) $db->delete('settings', 'module_id = ' . $this->module_id);
	}

	public function addToModulesTable() {
		global $db;

		// Modul in die Modules-SQL-Tabelle eintragen
		$bool = $db->insert('modules', array('id' => '', 'name' => $db->escape('emoticons'), 'active' => 1));
		$this->module_id = $db->link->lastInsertId();

		return (bool) $bool;
	}

	public function removeFromModulesTable() {
		global $db;

		return (bool) $db->delete('modules', 'id = ' . $this->module_id);
	}

}