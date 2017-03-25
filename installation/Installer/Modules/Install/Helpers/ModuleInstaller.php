<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Installer\Modules\Install\Helpers;

use ACP3\Core\Filesystem;
use ACP3\Core\Modules\ModuleDependenciesTrait;
use ACP3\Core\Modules\Vendor;
use ACP3\Core\XML;
use ACP3\Installer\Core\Environment\ApplicationPath;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModuleInstaller
{
    use ModuleDependenciesTrait;

    /**
     * @var ApplicationPath
     */
    protected $applicationPath;
    /**
     * @var Vendor
     */
    protected $vendor;
    /**
     * @var XML
     */
    protected $xml;
    /**
     * @var Install
     */
    protected $installHelper;
    /**
     * @var array
     */
    protected $alreadyInstalledModules = [];

    /**
     * ModuleInstaller constructor.
     * @param ApplicationPath $applicationPath
     * @param Vendor $vendor
     * @param XML $xml
     * @param Install $installHelper
     */
    public function __construct(
        ApplicationPath $applicationPath,
        Vendor $vendor,
        XML $xml,
        Install $installHelper
    ) {
        $this->applicationPath = $applicationPath;
        $this->vendor = $vendor;
        $this->xml = $xml;
        $this->installHelper = $installHelper;
    }

    /**
     * @param ContainerInterface $container
     * @param array $modules
     * @return array
     * @throws \Exception
     */
    public function installModules(ContainerInterface $container, array $modules = [])
    {
        foreach ($this->vendor->getVendors() as $vendor) {
            $vendorPath = $this->applicationPath->getModulesDir() . $vendor . '/';
            $vendorModules = $this->fetchModuleByVendor($modules, $vendorPath);

            foreach ($vendorModules as $module) {
                $module = strtolower($module);

                if (isset($this->alreadyInstalledModules[$module])) {
                    continue;
                }

                $modulePath = $vendorPath . ucfirst($module) . '/';
                $moduleConfigPath = $modulePath . 'Resources/config/module.xml';

                if ($this->isValidModule($modulePath, $moduleConfigPath)) {
                    $dependencies = $this->getModuleDependencies($moduleConfigPath);

                    if (count($dependencies) > 0) {
                        $this->installModules($container, $dependencies);
                    }

                    if ($this->installHelper->installModule($module, $container) === false) {
                        throw new \Exception("Error while installing module {$module}.");
                    }

                    $this->alreadyInstalledModules[$module] = true;
                }
            }
        }

        return $this->alreadyInstalledModules;
    }

    /**
     * @param array $modules
     * @param string $vendorPath
     * @return array
     */
    private function fetchModuleByVendor(array $modules, $vendorPath)
    {
        return count($modules) > 0 ? $modules : Filesystem::scandir($vendorPath);
    }

    /**
     * @param string $modulePath
     * @param string $moduleConfigPath
     * @return bool
     */
    private function isValidModule($modulePath, $moduleConfigPath)
    {
        $config = $this->xml->parseXmlFile($moduleConfigPath, '/module/info');

        return is_dir($modulePath) && is_file($moduleConfigPath) && !isset($config['no_install']);
    }

    /**
     * @return XML
     */
    protected function getXml()
    {
        return $this->xml;
    }
}
