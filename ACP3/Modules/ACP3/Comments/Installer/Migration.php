<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Installer;

class Migration implements \ACP3\Core\Installer\MigrationInterface
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
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `page`, `params`, `privilege_id`) VALUES ('', '{moduleId}', 'acp_list_comments', '', 3);",
                "UPDATE `{pre}acl_resources` SET `page` = 'acp_delete' WHERE `module_id` = '{moduleId}' AND `page` = 'acp_delete_comments_per_module';",
            ],
            32 => [
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = \"functions\";",
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'list', '', 1);",
            ],
            33 => [
                "UPDATE `{pre}acl_resources` SET `controller` = 'details' WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_comments';",
                "UPDATE `{pre}acl_resources` SET `page` = REPLACE(`page`, '_comments', '') WHERE `module_id` = '{moduleId}' AND `page` LIKE '%_comments';",
                "UPDATE `{pre}acl_resources` SET `controller` = 'details' WHERE `module_id` = '{moduleId}' AND `page` = 'edit';",
            ],
            34 => [
                'ALTER TABLE `{pre}comments` ENGINE = InnoDB',
            ],
            35 => [
                'DELETE FROM `{pre}comments` WHERE `module_id` NOT IN (SELECT `id` FROM `{pre}modules`);',
                'ALTER TABLE `{pre}comments` ADD INDEX (`module_id`)',
                'ALTER TABLE `{pre}comments` ADD FOREIGN KEY (`module_id`) REFERENCES `{pre}modules` (`id`) ON DELETE CASCADE',
            ],
            36 => [
                'ALTER TABLE `{pre}comments` CHANGE `user_id` `user_id` INT(10) UNSIGNED',
                'ALTER TABLE `{pre}comments` ADD INDEX (`user_id`)',
                'UPDATE `{pre}comments` SET `user_id` = NULL WHERE `user_id` = 0',
                'ALTER TABLE `{pre}comments` ADD FOREIGN KEY (`user_id`) REFERENCES `{pre}users` (`id`) ON DELETE SET NULL',
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
