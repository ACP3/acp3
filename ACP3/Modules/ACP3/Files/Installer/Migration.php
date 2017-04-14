<?php

namespace ACP3\Modules\ACP3\Files\Installer;

use ACP3\Core\Modules;

/**
 * Class Migration
 * @package ACP3\Modules\ACP3\Files\Installer
 */
class Migration extends Modules\Installer\AbstractMigration
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public function schemaUpdates()
    {
        return [
            31 => [
                "ALTER TABLE `{pre}files` CHANGE `link_title` `title` VARCHAR(120) {CHARSET} NOT NULL;",
            ],
            32 => [
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = \"extensions/search\";",
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = \"extensions/feeds\";",
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = \"functions\";",
            ],
            33 => [
                'UPDATE `{pre}seo` SET `uri`=REPLACE(`uri`, "files/", "files/index/") WHERE `uri` LIKE "files/%";',
            ],
            34 => [
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "files/list/", "files/index/index/") WHERE `uri` LIKE "files/list/%";' : '',
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "files/files/", "files/index/files/") WHERE `uri` LIKE "files/files/%";' : '',
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "files/details/", "files/index/details/") WHERE `uri` LIKE "files/details/%";' : '',
            ],
            35 => [
                "ALTER TABLE `{pre}files` CHANGE `title` `title` VARCHAR(255) NOT NULL;",
            ],
            36 => [
                "ALTER TABLE `{pre}files` ENGINE = InnoDB",
            ],
            37 => [
                "ALTER TABLE `{pre}files` ADD FOREIGN KEY (`category_id`) REFERENCES `{pre}categories` (`id`) ON DELETE CASCADE"
            ],
            38 => [
                "ALTER TABLE `{pre}files` CHANGE `user_id` `user_id` INT(10) UNSIGNED",
                "ALTER TABLE `{pre}files` ADD INDEX (`user_id`)",
                "UPDATE `{pre}files` SET `user_id` = NULL WHERE `user_id` = 0",
                "ALTER TABLE `{pre}files` ADD FOREIGN KEY (`user_id`) REFERENCES `{pre}users` (`id`) ON DELETE SET NULL"
            ],
            39 => [
                "ALTER TABLE `{pre}files` DROP INDEX `index`;",
                "ALTER TABLE `{pre}files` ADD FULLTEXT `fulltext_index` (`title`, `file`, `text`)"
            ],
            40 => [
                "UPDATE `{pre}acl_resources` SET `area` = 'widget' WHERE `module_id` = '{moduleId}' AND `area` = 'sidebar';"
            ],
            41 => [
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'frontend', 'index', 'download', '', 1);",
            ],
            42 => [
                "ALTER TABLE `{pre}files` ADD COLUMN `updated_at` DATETIME NOT NULL AFTER `end`;",
                "UPDATE `{pre}files` SET `updated_at` = `start`;"
            ],
            43 => [
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'admin', 'index', 'duplicate', '', 4);",
            ]
        ];
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function renameModule()
    {
        return [];
    }
}
