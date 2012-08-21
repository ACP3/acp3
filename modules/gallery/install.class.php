<?php

class ACP3_GalleryModuleInstaller extends ACP3_ModuleInstaller {

	public function createTables() {
		global $db;

		$queries = array(
			"CREATE TABLE `{pre}gallery` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`start` DATETIME NOT NULL,
				`end` DATETIME NOT NULL,
				`name` VARCHAR(120) NOT NULL,
				`user_id` INT UNSIGNED NOT NULL,
				PRIMARY KEY (`id`)
			) {engine};",
			"CREATE TABLE `{pre}gallery_pictures` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`pic` INT(10) UNSIGNED NOT NULL,
				`gallery_id` INT(10) UNSIGNED NOT NULL,
				`file` VARCHAR(120) NOT NULL,
				`description` TEXT NOT NULL,
				`comments` TINYINT(1) UNSIGNED NOT NULL,
				PRIMARY KEY (`id`), INDEX `foreign_gallery_id` (`gallery_id`)
			) {engine};"
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
			"DROP TABLE `{pre}gallery_pictures`;",
			"DROP TABLE `{pre}gallery`;",
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
			'width' => 640,
			'height' => 480,
			'thumbwidth' => 160,
			'thumbheight' => 480,
			'maxwidth' => 2048,
			'maxheight' => 1536,
			'filesize' => 20971520,
			'overlay' => 1,
			'dateformat' => 'long',
			'sidebar' => 5,
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
		$bool = $db->insert('modules', array('id' => '', 'name' => $db->escape('gallery'), 'active' => 1));
		$this->module_id = $db->link->lastInsertId();

		return (bool) $bool;
	}

	public function removeFromModulesTable() {
		global $db;

		return (bool) $db->delete('modules', 'id = ' . $this->module_id);
	}

}