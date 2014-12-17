<?php
namespace ACP3\Modules\System;

use ACP3\Core;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class Helpers
 * @package ACP3\Modules\System
 */
class Helpers
{
    /**
     * @var Core\DB
     */
    protected $db;
    /**
     * @var Core\Modules
     */
    protected $modules;

    /**
     * @param Core\DB $db
     * @param Core\Modules $modules
     */
    public function __construct(
        Core\DB $db,
        Core\Modules $modules
    ) {
        $this->db = $db;
        $this->modules = $modules;
    }

    /**
     * Überprüft die Modulabhängigkeiten beim Installieren eines Moduls
     *
     * @param Core\Modules\AbstractInstaller $moduleInstaller
     * @return array
     */
    public function checkInstallDependencies(Core\Modules\AbstractInstaller $moduleInstaller)
    {
        $deps = $moduleInstaller->getDependencies();
        $modulesToEnable = [];
        if (!empty($deps)) {
            foreach ($deps as $dep) {
                if ($this->modules->isActive($dep) === false) {
                    $modulesToEnable[] = ucfirst($dep);
                }
            }
        }
        return $modulesToEnable;
    }

    /**
     * @param                                                  $moduleToBeUninstalled
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     * @return array
     */
    public function checkUninstallDependencies($moduleToBeUninstalled, Container $container)
    {
        $modules = $this->modules->getActiveModules();
        $moduleDependencies = [];
        foreach ($modules as $localizedModuleName => $values) {
            $moduleName = strtolower($values['dir']);
            if ($moduleName !== $moduleToBeUninstalled) {
                $deps = $container->get($moduleName . '.installer')->getDependencies();
                if (!empty($deps) && in_array($moduleToBeUninstalled, $deps) === true) {
                    $moduleDependencies[] = $localizedModuleName;
                }
            }
        }
        return $moduleDependencies;
    }

    /**
     * @param array $tables
     * @param $exportType
     * @param $withDropTables
     * @return string
     */
    public function exportDatabase(array $tables, $exportType, $withDropTables)
    {
        $structure = $data = '';
        foreach ($tables as $table) {
            // Struktur ausgeben
            if ($exportType === 'complete' || $exportType === 'structure') {
                $result = $this->db->getConnection()->fetchAssoc('SHOW CREATE TABLE ' . $table);
                if (!empty($result)) {
                    $structure .= $withDropTables == 1 ? 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n\n" : '';
                    $structure .= $result['Create Table'] . ';' . "\n\n";
                }
            }

            // Datensätze ausgeben
            if ($exportType === 'complete' || $exportType === 'data') {
                $resultSets = $this->db->getConnection()->fetchAll('SELECT * FROM ' . $this->db->getPrefix() . substr($table, strlen($this->db->getPrefix())));
                if (count($resultSets) > 0) {
                    $fields = '';
                    // Felder der jeweiligen Tabelle auslesen
                    foreach (array_keys($resultSets[0]) as $field) {
                        $fields .= '`' . $field . '`, ';
                    }

                    // Datensätze auslesen
                    foreach ($resultSets as $row) {
                        $values = '';
                        foreach ($row as $value) {
                            $values .= '\'' . $value . '\', ';
                        }
                        $data .= 'INSERT INTO `' . $table . '` (' . substr($fields, 0, -2) . ') VALUES (' . substr($values, 0, -2) . ');' . "\n";
                    }
                }
            }
        }

        return $structure . $data;
    }
}
