<?php

namespace ACP3\Modules\Guestbook;

use ACP3\Core\Modules;
use ACP3\Modules\System;
use ACP3\Modules\Permissions;

/**
 * Class Installer
 * @package ACP3\Modules\Guestbook
 */
class Installer extends Modules\AbstractInstaller
{

    const MODULE_NAME = 'guestbook';
    const SCHEMA_VERSION = 32;

    /**
     * @inheritdoc
     */
    public function createTables()
    {
        return array(
            "CREATE TABLE `{pre}guestbook` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `date` DATETIME NOT NULL,
                `ip` VARCHAR(40) NOT NULL,
                `name` VARCHAR(20) NOT NULL,
                `user_id` INT(10) UNSIGNED NOT NULL,
                `message` TEXT NOT NULL,
                `website` VARCHAR(120) NOT NULL,
                `mail` VARCHAR(120) NOT NULL,
                `active` TINYINT(1) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`), INDEX `foreign_user_id` (`user_id`)
            ) {engine} {charset};"
        );
    }

    /**
     * @inheritdoc
     */
    public function removeTables()
    {
        return array("DROP TABLE `{pre}guestbook`;");
    }

    /**
     * @inheritdoc
     */
    public function settings()
    {
        return array(
            'dateformat' => 'long',
            'notify' => 0,
            'notify_email' => '',
            'emoticons' => 1,
            'newsletter_integration' => 0,
            'overlay' => 1
        );
    }

    /**
     * @inheritdoc
     */
    public function schemaUpdates()
    {
        return array(
            31 => array(
                'UPDATE `{pre}seo` SET uri=REPLACE(uri, "guestbook/", "guestbook/index/") WHERE uri LIKE "guestbook/%";',
            ),
            32 => array(
                $this->moduleIsInstalled('menus') || $this->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET uri=REPLACE(uri, "guestbook/list/", "guestbook/index/index/") WHERE uri LIKE "guestbook/list/%";' : '',
                $this->moduleIsInstalled('menus') || $this->moduleIsInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET uri=REPLACE(uri, "guestbook/create/", "guestbook/index/create/") WHERE uri LIKE "guestbook/create/%";' : '',
            )
        );
    }

}
