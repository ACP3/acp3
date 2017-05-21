<?php

namespace ACP3\Modules\ACP3\Filemanager\Installer;

/**
 * Class Schema
 * @package ACP3\Modules\ACP3\Filemanager\Installer
 */
class Schema implements \ACP3\Core\Installer\SchemaInterface
{
    const MODULE_NAME = 'filemanager';

    /**
     * @return array
     */
    public function specialResources()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return static::MODULE_NAME;
    }

    /**
     * @return int
     */
    public function getSchemaVersion()
    {
        return 1;
    }
    /**
     * @return array
     */
    public function createTables()
    {
        return [];
    }

    /**
     * @return array
     */
    public function removeTables()
    {
        return [];
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [];
    }
}
