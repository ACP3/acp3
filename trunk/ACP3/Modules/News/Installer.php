<?php

namespace ACP3\Modules\News;

use ACP3\Core\Modules;
use ACP3\Modules\System;
use ACP3\Modules\Permissions;

class Installer extends Modules\AbstractInstaller
{

    const MODULE_NAME = 'news';
    const SCHEMA_VERSION = 34;

    /**
     * @var \ACP3\Core\Modules
     */
    protected $modules;

    public function __construct(
        \Doctrine\DBAL\Connection $db,
        System\Model $systemModel,
        Permissions\Model $permissionsModel,
        Modules $modules
    )
    {
        parent::__construct($db, $systemModel, $permissionsModel);

        $this->modules = $modules;
    }

    public function createTables()
    {
        return array(
            "CREATE TABLE `{pre}news` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `start` DATETIME NOT NULL,
                `end` DATETIME NOT NULL,
                `title` VARCHAR(120) NOT NULL,
                `text` TEXT NOT NULL,
                `readmore` TINYINT(1) UNSIGNED NOT NULL,
                `comments` TINYINT(1) UNSIGNED NOT NULL,
                `category_id` INT(10) UNSIGNED NOT NULL,
                `uri` VARCHAR(120) NOT NULL,
                `target` TINYINT(1) UNSIGNED NOT NULL,
                `link_title` VARCHAR(120) NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`), FULLTEXT KEY `index` (`title`,`text`), INDEX `foreign_category_id` (`category_id`)
            ) {engine} {charset};"
        );
    }

    public function removeTables()
    {
        return array(
            "DROP TABLE `{pre}news`;",
            "DELETE FROM `{pre}categories` WHERE module_id = " . $this->getModuleId() . ";"
        );
    }

    public function settings()
    {
        return array(
            'comments' => 1,
            'dateformat' => 'long',
            'readmore' => 1,
            'readmore_chars' => 350,
            'sidebar' => 5,
            'category_in_breadcrumb' => 1
        );
    }

    public function schemaUpdates()
    {
        return array(
            31 => array(
                "ALTER TABLE `{pre}news` CHANGE `headline` `title` VARCHAR(120) {charset} NOT NULL",
            ),
            32 => array(
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = " . $this->getModuleId() . " AND page = \"extensions/search\";",
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = " . $this->getModuleId() . " AND page = \"extensions/feeds\";",
                "DELETE FROM `{pre}acl_resources` WHERE `module_id` = " . $this->getModuleId() . " AND page = \"functions\";",
            ),
            33 => array(
                'UPDATE `{pre}seo` SET uri=REPLACE(uri, "news/", "news/index/") WHERE uri LIKE "news/%";',
            ),
            34 => array(
                $this->modules->isInstalled('menus') || $this->modules->isInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET uri=REPLACE(uri, "news/list/", "news/index/index/") WHERE uri LIKE "news/list/%";' : '',
                $this->modules->isInstalled('menus') || $this->modules->isInstalled('menu_items') ? 'UPDATE `{pre}menu_items` SET uri=REPLACE(uri, "news/details/", "news/index/details/") WHERE uri LIKE "news/details/%";' : '',
            )
        );
    }

}