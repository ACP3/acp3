<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\System\Controller\Admin\Extensions;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions;
use ACP3\Modules\ACP3\System;

/**
 * Class Modules
 * @package ACP3\Modules\ACP3\System\Controller\Admin\Extensions
 */
class Modules extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Core\Modules\ModuleInfoCache
     */
    protected $moduleInfoCache;
    /**
     * @var \ACP3\Modules\ACP3\System\Model\Repository\ModulesRepository
     */
    protected $systemModuleRepository;
    /**
     * @var \ACP3\Modules\ACP3\System\Helper\Installer
     */
    protected $installerHelper;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Cache
     */
    protected $permissionsCache;
    /**
     * @var Core\I18n\DictionaryCache
     */
    private $dictionaryCache;
    /**
     * @var Core\Installer\SchemaRegistrar
     */
    private $schemaRegistrar;

    /**
     * Modules constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param Core\I18n\DictionaryCache $dictionaryCache
     * @param \ACP3\Core\Modules\ModuleInfoCache $moduleInfoCache
     * @param \ACP3\Modules\ACP3\System\Model\Repository\ModulesRepository $systemModuleRepository
     * @param \ACP3\Modules\ACP3\System\Helper\Installer $installerHelper
     * @param \ACP3\Modules\ACP3\Permissions\Cache $permissionsCache
     * @param Core\Installer\SchemaRegistrar $schemaRegistrar
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\I18n\DictionaryCache $dictionaryCache,
        Core\Modules\ModuleInfoCache $moduleInfoCache,
        System\Model\Repository\ModulesRepository $systemModuleRepository,
        System\Helper\Installer $installerHelper,
        Permissions\Cache $permissionsCache,
        Core\Installer\SchemaRegistrar $schemaRegistrar
    ) {
        parent::__construct($context);

        $this->moduleInfoCache = $moduleInfoCache;
        $this->systemModuleRepository = $systemModuleRepository;
        $this->installerHelper = $installerHelper;
        $this->permissionsCache = $permissionsCache;
        $this->dictionaryCache = $dictionaryCache;
        $this->schemaRegistrar = $schemaRegistrar;
    }

    /**
     * @param string $action
     * @param string $dir
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($action = '', $dir = '')
    {
        switch ($action) {
            case 'activate':
                return $this->enableModule($dir);
            case 'deactivate':
                return $this->disableModule($dir);
            case 'install':
                return $this->installModule($dir);
            case 'uninstall':
                return $this->uninstallModule($dir);
            default:
                return $this->outputPage();
        }
    }

    /**
     * @param string $moduleDirectory
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function enableModule($moduleDirectory)
    {
        $bool = false;

        try {
            $info = $this->modules->getModuleInfo($moduleDirectory);
            if (empty($info) || $info['protected'] === true) {
                throw new System\Exception\ModuleInstallerException(
                    $this->translator->t('system', 'could_not_complete_request')
                );
            }

            $serviceId = strtolower($moduleDirectory . '.installer.schema');
            $this->moduleInstallerExists($serviceId);

            $moduleSchema = $this->schemaRegistrar->get($serviceId);

            $dependencies = $this->installerHelper->checkInstallDependencies($moduleSchema);
            $this->checkForFailedModuleDependencies($dependencies, 'enable_following_modules_first');

            $bool = $this->saveModuleState($moduleDirectory, 1);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_activate_' . ($bool !== false ? 'success' : 'error')
            );
        } catch (System\Exception\ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages()->setMessage($bool, $text, $this->request->getFullPath());
    }

    /**
     * @param string $serviceId
     *
     * @throws System\Exception\ModuleInstallerException
     */
    protected function moduleInstallerExists($serviceId)
    {
        if ($this->schemaRegistrar->has($serviceId) === false) {
            throw new System\Exception\ModuleInstallerException(
                $this->translator->t('system', 'module_installer_not_found')
            );
        }
    }

    /**
     * @param array  $dependencies
     * @param string $phrase
     *
     * @throws \ACP3\Modules\ACP3\System\Exception\ModuleInstallerException
     */
    protected function checkForFailedModuleDependencies(array $dependencies, $phrase)
    {
        if (!empty($dependencies)) {
            throw new System\Exception\ModuleInstallerException(
                $this->translator->t(
                    'system',
                    $phrase,
                    ['%modules%' => implode(', ', $dependencies)]
                )
            );
        }
    }

    /**
     * @param string $moduleDirectory
     * @param int    $active
     *
     * @return bool|int
     */
    protected function saveModuleState($moduleDirectory, $active)
    {
        return $this->systemModuleRepository->update(['active' => $active], ['name' => $moduleDirectory]);
    }

    protected function purgeCaches()
    {
        Core\Cache\Purge::doPurge([
            $this->appPath->getCacheDir() . 'http',
            $this->appPath->getCacheDir() . 'sql',
            $this->appPath->getCacheDir() . 'tpl_compiled',
            $this->appPath->getCacheDir() . 'tpl_cached',
            $this->appPath->getCacheDir() . 'container.php',
            $this->appPath->getCacheDir() . 'container.php.meta',
        ]);
    }

    protected function renewCaches()
    {
        $this->dictionaryCache->saveLanguageCache($this->translator->getLocale());
        $this->moduleInfoCache->saveModulesInfoCache();
        $this->permissionsCache->saveResourcesCache();
    }

    /**
     * @param string $moduleDirectory
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    protected function disableModule($moduleDirectory)
    {
        $bool = false;

        try {
            $info = $this->modules->getModuleInfo($moduleDirectory);
            if (empty($info) || $info['protected'] === true) {
                throw new System\Exception\ModuleInstallerException(
                    $text = $this->translator->t('system', 'could_not_complete_request')
                );
            }

            $serviceId = strtolower($moduleDirectory . '.installer.schema');
            $this->moduleInstallerExists($serviceId);

            $moduleSchema = $this->schemaRegistrar->get($serviceId);

            $dependencies = $this->installerHelper->checkUninstallDependencies(
                $moduleSchema->getModuleName(),
                $this->container
            );
            $this->checkForFailedModuleDependencies($dependencies, 'module_disable_not_possible');

            $bool = $this->saveModuleState($moduleDirectory, 0);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_deactivate_' . ($bool !== false ? 'success' : 'error')
            );
        } catch (System\Exception\ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages()->setMessage($bool, $text, $this->request->getFullPath());
    }

    /**
     * @param string $moduleDirectory
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function installModule($moduleDirectory)
    {
        $bool = false;

        try {
            if ($this->modules->isInstalled($moduleDirectory) === true) {
                throw new System\Exception\ModuleInstallerException(
                    $this->translator->t('system', 'module_already_installed')
                );
            }

            $serviceId = strtolower($moduleDirectory . '.installer.schema');
            $this->moduleInstallerExists($serviceId);

            $moduleSchema = $this->schemaRegistrar->get($serviceId);

            $dependencies = $this->installerHelper->checkInstallDependencies($moduleSchema);
            $this->checkForFailedModuleDependencies($dependencies, 'enable_following_modules_first');

            $bool = $this->container->get('core.modules.schemaInstaller')->install($moduleSchema);
            $bool2 = $this->container->get('core.modules.aclInstaller')->install($moduleSchema);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_installation_' . ($bool !== false && $bool2 !== false ? 'success' : 'error')
            );
        } catch (System\Exception\ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages()->setMessage($bool, $text, $this->request->getFullPath());
    }

    /**
     * @param string $moduleDirectory
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function uninstallModule($moduleDirectory)
    {
        $bool = false;

        try {
            $info = $this->modules->getModuleInfo($moduleDirectory);
            if ($this->modules->isInstalled($moduleDirectory) === false || $info['protected'] === true) {
                throw new System\Exception\ModuleInstallerException(
                    $this->translator->t('system', 'protected_module_description')
                );
            }

            $serviceId = strtolower($moduleDirectory . '.installer.schema');
            $this->moduleInstallerExists($serviceId);

            $moduleSchema = $this->schemaRegistrar->get($serviceId);

            $dependencies = $this->installerHelper->checkUninstallDependencies(
                $moduleSchema->getModuleName(),
                $this->container
            );
            $this->checkForFailedModuleDependencies($dependencies, 'uninstall_following_modules_first');

            $bool = $this->container->get('core.modules.schemaInstaller')->uninstall($moduleSchema);
            $bool2 = $this->container->get('core.modules.aclInstaller')->uninstall($moduleSchema);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_uninstallation_' . ($bool !== false && $bool2 !== false ? 'success' : 'error')
            );
        } catch (System\Exception\ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages()->setMessage($bool, $text, $this->request->getFullPath());
    }

    /**
     * @return array
     */
    protected function outputPage()
    {
        $this->renewCaches();

        $modules = $this->modules->getAllModules();
        $installedModules = $newModules = [];

        foreach ($modules as $key => $values) {
            $values['dir'] = strtolower($values['dir']);
            if ($this->modules->isInstalled($values['dir']) === true) {
                $installedModules[$key] = $values;
            } else {
                $newModules[$key] = $values;
            }
        }

        return [
            'installed_modules' => $installedModules,
            'new_modules' => $newModules
        ];
    }
}
