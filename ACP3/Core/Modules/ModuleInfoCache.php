<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Modules;

use ACP3\Core\Cache;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Filesystem;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Model\Repository\ModuleAwareRepositoryInterface;
use ACP3\Core\XML;

/**
 * Class ModuleInfoCache
 * @package ACP3\Core\Modules
 */
class ModuleInfoCache
{
    use ModuleDependenciesTrait;

    /**
     * @var \ACP3\Core\Cache
     */
    protected $cache;
    /**
     * @var \ACP3\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    protected $translator;
    /**
     * @var \ACP3\Core\Modules\Vendor
     */
    protected $vendors;
    /**
     * @var \ACP3\Core\XML
     */
    protected $xml;
    /**
     * @var ModuleAwareRepositoryInterface
     */
    protected $systemModuleRepository;

    /**
     * ModuleInfoCache constructor.
     * @param Cache $cache
     * @param ApplicationPath $appPath
     * @param Translator $translator
     * @param Vendor $vendors
     * @param XML $xml
     * @param ModuleAwareRepositoryInterface $systemModuleRepository
     */
    public function __construct(
        Cache $cache,
        ApplicationPath $appPath,
        Translator $translator,
        Vendor $vendors,
        XML $xml,
        ModuleAwareRepositoryInterface $systemModuleRepository
    ) {
        $this->cache = $cache;
        $this->appPath = $appPath;
        $this->translator = $translator;
        $this->vendors = $vendors;
        $this->xml = $xml;
        $this->systemModuleRepository = $systemModuleRepository;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return 'infos_' . $this->translator->getLocale();
    }

    /**
     * @return array
     */
    public function getModulesInfoCache()
    {
        if ($this->cache->contains($this->getCacheKey()) === false) {
            $this->saveModulesInfoCache();
        }

        return $this->cache->fetch($this->getCacheKey());
    }

    /**
     * Saves the modules info cache
     */
    public function saveModulesInfoCache()
    {
        $infos = [];

        // 1. fetch all core modules
        // 2. Fetch all 3rd party modules
        // 3. Fetch all local module customizations
        foreach ($this->vendors->getVendors() as $vendor) {
            $infos += $this->fetchVendorModules($vendor);
        }

        $this->cache->save($this->getCacheKey(), $infos);
    }

    /**
     * @param string $vendor
     *
     * @return array
     */
    protected function fetchVendorModules($vendor)
    {
        $infos = [];

        $modules = Filesystem::scandir($this->appPath->getModulesDir() . $vendor . '/');

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $moduleInfo = $this->fetchModuleInfo($module);

                if (!empty($moduleInfo)) {
                    $infos[strtolower($module)] = $moduleInfo;
                }
            }
        }

        return $infos;
    }

    /**
     * @param string $moduleDirectory
     *
     * @return array
     */
    protected function fetchModuleInfo($moduleDirectory)
    {
        $vendors = array_reverse($this->vendors->getVendors()); // Reverse the order of the array -> search module customizations first, then 3rd party modules, then core modules
        foreach ($vendors as $vendor) {
            $path = $this->appPath->getModulesDir() . $vendor . '/' . $moduleDirectory . '/Resources/config/module.xml';
            if (is_file($path) === true) {
                $moduleInfo = $this->xml->parseXmlFile($path, 'info');

                if (!empty($moduleInfo)) {
                    $moduleName = strtolower($moduleDirectory);
                    $moduleInfoDb = $this->systemModuleRepository->getInfoByModuleName($moduleName);

                    return [
                        'id' => !empty($moduleInfoDb) ? $moduleInfoDb['id'] : 0,
                        'dir' => $moduleDirectory,
                        'installed' => (!empty($moduleInfoDb)),
                        'active' => (!empty($moduleInfoDb) && $moduleInfoDb['active'] == 1),
                        'schema_version' => !empty($moduleInfoDb) ? (int)$moduleInfoDb['version'] : 0,
                        'description' => $this->getModuleDescription($moduleInfo, $moduleName),
                        'author' => $moduleInfo['author'],
                        'version' => $moduleInfo['version'],
                        'name' => $this->getModuleName($moduleInfo, $moduleName),
                        'categories' => isset($moduleInfo['categories']),
                        'protected' => isset($moduleInfo['protected']),
                        'dependencies' => $this->getModuleDependencies($path),
                    ];
                }
            }
        }

        return [];
    }

    /**
     * @param array  $moduleInfo
     * @param string $moduleName
     *
     * @return string
     */
    protected function getModuleDescription(array $moduleInfo, $moduleName)
    {
        if (isset($moduleInfo['description']['lang']) && $moduleInfo['description']['lang'] === 'true') {
            return $this->translator->t($moduleName, 'mod_description');
        }

        return $moduleInfo['description'];
    }

    /**
     * @param array  $moduleInfo
     * @param string $moduleName
     *
     * @return string
     */
    protected function getModuleName(array $moduleInfo, $moduleName)
    {
        if (isset($moduleInfo['name']['lang']) && $moduleInfo['name']['lang'] === 'true') {
            return $this->translator->t($moduleName, $moduleName);
        }

        return $moduleInfo['name'];
    }

    /**
     * @return XML
     */
    protected function getXml()
    {
        return $this->xml;
    }
}
