<?php

namespace ACP3\Modules\ACP3\Menus\Installer;

class Migration implements \ACP3\Core\Installer\MigrationInterface
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public function renameModule()
    {
        return [
            31 => "UPDATE `{pre}modules` SET `name` = 'menus' WHERE `name` = 'menu_items';",
        ];
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function schemaUpdates()
    {
        return [
            31 => [
                "UPDATE `{pre}acl_resources` SET `page` = 'acp_create_item' WHERE `module_id` = '{moduleId}' AND `page` = 'acp_create_block';",
                "UPDATE `{pre}acl_resources` SET `page` = 'acp_delete_item' WHERE `module_id` = '{moduleId}' AND `page` = 'acp_delete_blocks';",
                "UPDATE `{pre}acl_resources` SET `page` = 'acp_edit_item' WHERE `module_id` = '{moduleId}' AND `page` = 'acp_edit_block';",
                "DELETE  FROM `{pre}acl_resources` WHERE `page` = 'acp_list_blocks' AND `module_id` = '{moduleId}';",
                'RENAME TABLE `{pre}menu_items_blocks` TO `{pre}menus`;',
            ],
            32 => [
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = 'functions';",
            ],
            33 => [
                "UPDATE `{pre}acl_resources` SET `controller` = 'items' WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_item';",
                "UPDATE `{pre}acl_resources` SET `page` = REPLACE(`page`, '_item', '') WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_item';",
                "UPDATE `{pre}acl_resources` SET `controller` = 'items' WHERE `module_id` = '{moduleId}' AND `page` = 'order';",
            ],
            34 => [
                'ALTER TABLE `{pre}menu_items` ENGINE = InnoDB',
                'ALTER TABLE `{pre}menus` ENGINE = InnoDB',
            ],
            35 => [
                'ALTER TABLE `{pre}menu_items` ADD FOREIGN KEY (`block_id`) REFERENCES `{pre}menus` (`id`) ON DELETE CASCADE',
            ],
            36 => [
                'ALTER TABLE `{pre}menu_items` ADD INDEX `left_id` (`left_id`);',
            ],
            37 => [
                'ALTER TABLE `{pre}menus` ADD UNIQUE KEY `index_name` (`index_name`);',
            ],
            38 => [
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'admin', 'index', 'manage', '', 8);",
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'admin', 'items', 'manage', '', 8);",
            ],
        ];
    }
}
