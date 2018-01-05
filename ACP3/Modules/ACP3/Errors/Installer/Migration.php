<?php

namespace ACP3\Modules\ACP3\Errors\Installer;

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
                'UPDATE `{pre}seo` SET `uri`=REPLACE(`uri`, "errors/", "errors/index/") WHERE `uri` LIKE "errors/%";',
            ],
            32 => [
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "errors/403/", "errors/index/403/") WHERE `uri` LIKE "errors/403/%";' : '',
                $this->schemaHelper->moduleIsInstalled('menus') || $this->schemaHelper->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET `uri`=REPLACE(`uri`, "errors/404/", "errors/index/404/") WHERE `uri` LIKE "errors/404/%";' : '',
            ],
            33 => [
                "INSERT INTO `{pre}acl_resources` (`id`, `module_id`, `area`, `controller`, `page`, `params`, `privilege_id`) VALUES('', '{moduleId}', 'frontend', 'index', '500', '', 1);",
                "UPDATE `{pre}acl_resources` SET `page` = '401' WHERE `module_id` = '{moduleId}' AND `area` = 'frontend' AND `controller` = 'index' AND `page` = '403';",
            ],
            34 => [
                "UPDATE `{pre}acl_resources` SET `page` = '403' WHERE `module_id` = '{moduleId}' AND `area` = 'frontend' AND `controller` = 'index' AND `page` = '401';",
            ],
            35 => [
                "UPDATE `{pre}acl_resources` SET `page` = 'access_forbidden' WHERE `module_id` = '{moduleId}' AND `area` = 'frontend' AND `controller` = 'index' AND `page` = '403';",
                "UPDATE `{pre}acl_resources` SET `page` = 'not_found' WHERE `module_id` = '{moduleId}' AND `area` = 'frontend' AND `controller` = 'index' AND `page` = '404';",
                "UPDATE `{pre}acl_resources` SET `page` = 'server_error' WHERE `module_id` = '{moduleId}' AND `area` = 'frontend' AND `controller` = 'index' AND `page` = '500';",
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
