<?php
namespace ACP3\Modules\ACP3\System;

use ACP3\Core;

/**
 * Class Model
 * @package ACP3\Modules\ACP3\System
 */
class Model extends Core\Model
{
    const TABLE_NAME = 'modules';
    const TABLE_NAME_SETTINGS = 'settings';

    /**
     * @return array
     */
    public function getSchemaTables()
    {
        return $this->db->fetchAll('SELECT `TABLE_NAME` FROM information_schema.TABLES WHERE `TABLE_TYPE` = ? AND `TABLE_SCHEMA` = ?', ['BASE TABLE', $this->db->getDatabase()]);
    }

    /**
     * @param $moduleName
     *
     * @return mixed
     */
    public function getModuleId($moduleName)
    {
        return $this->db->fetchColumn('SELECT `id` FROM ' . $this->getTableName() . ' WHERE `name` = ?', [$moduleName]);
    }

    /**
     * @return array
     */
    public function getAllModuleSettings()
    {
        return $this->db->fetchAll('SELECT m.name AS module_name, s.name, s.value FROM ' . $this->getTableName(static::TABLE_NAME_SETTINGS) . ' AS s JOIN ' . $this->getTableName() . ' AS m ON(m.id = s.module_id) ORDER BY s.module_id');
    }

    /**
     * @param $moduleName
     *
     * @return array
     */
    public function getSettingsByModuleName($moduleName)
    {
        return $this->db->fetchAll('SELECT s.name, s.value FROM ' . $this->getTableName(static::TABLE_NAME_SETTINGS) . ' AS s JOIN ' . $this->getTableName() . ' AS m ON(m.id = s.module_id) WHERE m.name = ?', [$moduleName]);
    }

    /**
     * @param $moduleName
     *
     * @return mixed
     */
    public function getModuleSchemaVersion($moduleName)
    {
        return $this->db->fetchColumn('SELECT `version` FROM ' . $this->getTableName() . ' WHERE `name` = ?', [$moduleName]);
    }

    /**
     * @param $moduleName
     *
     * @return bool
     */
    public function moduleExists($moduleName)
    {
        return $this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE `name` = ?', [$moduleName]) > 0;
    }

    /**
     * @param $moduleName
     *
     * @return array
     */
    public function getInfoByModuleName($moduleName)
    {
        return $this->db->fetchAssoc('SELECT `id`, `version`, `active` FROM ' . $this->getTableName() . ' WHERE `name` = ?', [$moduleName]);
    }

    /**
     * @param $moduleId
     *
     * @return mixed
     */
    public function getModuleNameById($moduleId)
    {
        return $this->db->fetchColumn('SELECT `name` FROM ' . $this->getTableName() . ' WHERE `id` = ?', [$moduleId]);
    }
}