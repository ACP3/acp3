<?php

namespace ACP3\Modules\ACP3\Search\Installer;

use ACP3\Core\Modules;

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
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `page`, `params`, `privilege_id`) VALUES ('', '{moduleId}', 'sidebar', '', 1);",
            ],
            32 => [
                'UPDATE `{pre}seo` SET `uri`=REPLACE(`uri`, "search/", "search/index/") WHERE `uri` LIKE "search/%";',
            ],
            33 => [
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "search/list/", "search/index/index/") WHERE `uri` LIKE "search/list/%";' : '',
            ],
            34 => [
                "UPDATE `{pre}acl_resources` SET `area` = 'widget' WHERE `module_id` = '{moduleId}' AND `area` = 'sidebar';"
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
