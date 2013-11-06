<?php

namespace ACP3\Modules\Errors;

use ACP3\Core\Modules;

class Installer extends Modules\Installer
{

    const MODULE_NAME = 'errors';
    const SCHEMA_VERSION = 30;

    protected function removeResources()
    {
        return true;
    }

    protected function createTables()
    {
        return array();
    }

    protected function removeTables()
    {
        return array();
    }

    protected function settings()
    {
        return array();
    }

    protected function removeSettings()
    {
        return true;
    }

    protected function removeFromModulesTable()
    {
        return true;
    }

    protected function schemaUpdates()
    {
        return array();
    }

}
