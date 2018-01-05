<?php

namespace ACP3\Modules\ACP3\Gallery\Installer;

class Migration extends \ACP3\Core\Installer\AbstractMigration
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
                'ALTER TABLE `{pre}gallery` CHANGE `name` `title` VARCHAR(120) {CHARSET} NOT NULL;',
            ],
            32 => [
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = 'functions';",
            ],
            33 => [
                'UPDATE `{pre}seo` SET `uri`=REPLACE(`uri`, "gallery/", "gallery/index/") WHERE `uri` LIKE "gallery/%";',
            ],
            34 => [
                "UPDATE `{pre}acl_resources` SET `controller` = 'pictures' WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_picture';",
                "UPDATE `{pre}acl_resources` SET `page` = REPLACE(`page`, '_picture', '') WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_picture';",
                "UPDATE `{pre}acl_resources` SET `controller` = 'pictures' WHERE `module_id` = '{moduleId}' AND `page` = 'order';",
            ],
            35 => [
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "gallery/list/", "gallery/index/index/") WHERE `uri` LIKE "gallery/list/%";' : '',
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "gallery/pics/", "gallery/index/pics/") WHERE `uri` LIKE "gallery/pics/%";' : '',
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "gallery/details/", "gallery/index/details/") WHERE `uri` LIKE "gallery/details/%";' : '',
            ],
            36 => [
                'ALTER TABLE `{pre}gallery` ENGINE = InnoDB',
                'ALTER TABLE `{pre}gallery_pictures` ENGINE = InnoDB',
            ],
            37 => [
                'ALTER TABLE `{pre}gallery_pictures` ADD FOREIGN KEY (`gallery_id`) REFERENCES `{pre}gallery` (`id`) ON DELETE CASCADE',
            ],
            38 => [
                'ALTER TABLE `{pre}gallery` CHANGE `user_id` `user_id` INT(10) UNSIGNED',
                'ALTER TABLE `{pre}gallery` ADD INDEX (`user_id`)',
                'UPDATE `{pre}gallery` SET `user_id` = NULL WHERE `user_id` = 0',
                'ALTER TABLE `{pre}gallery` ADD FOREIGN KEY (`user_id`) REFERENCES `{pre}users` (`id`) ON DELETE SET NULL',
            ],
            39 => [
                "DELETE FROM `{pre}settings` WHERE `module_id` = {moduleId} AND name = 'filesize';",
                "DELETE FROM `{pre}settings` WHERE `module_id` = {moduleId} AND name = 'maxheight';",
                "DELETE FROM `{pre}settings` WHERE `module_id` = {moduleId} AND name = 'maxwidth';",
            ],
            40 => [
                "UPDATE `{pre}acl_resources` SET `area` = 'widget' WHERE `module_id` = '{moduleId}' AND `area` = 'sidebar';",
            ],
            41 => [
                'ALTER TABLE `{pre}gallery` ADD COLUMN `updated_at` DATETIME NOT NULL AFTER `end`;',
                'UPDATE `{pre}gallery` SET `updated_at` = `start`;',
            ],
            42 => [
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'admin', 'pictures', 'index', '', 3);",
            ],
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
