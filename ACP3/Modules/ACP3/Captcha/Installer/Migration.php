<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Installer;

use ACP3\Core\Modules\Installer\MigrationInterface;

class Migration implements MigrationInterface
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
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = '{moduleId}' AND `page` = 'functions';",
            ],
            32 => [
                "INSERT INTO `{pre}settings` (`id`, `module_id`, `name`, `value`) VALUES ('', '{moduleId}', 'captcha', 'captcha.extension.native_captcha_extension');",
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
