<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Controller\Admin\Extensions;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\RedirectMessages;
use ACP3\Modules\ACP3\System\Event\RenewCacheEvent;
use ACP3\Modules\ACP3\System\Exception\ModuleInstallerException;
use ACP3\Modules\ACP3\System\Helper\Installer;
use ACP3\Modules\ACP3\System\Services\CacheClearService;
use ACP3\Modules\ACP3\System\ViewProviders\AdminModulesViewProvider;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;

class Modules extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly EventDispatcher $eventDispatcher,
        private readonly RedirectMessages $redirectMessages,
        private readonly Core\Modules $modules,
        private readonly Installer $installerHelper,
        private readonly ContainerInterface $schemaLocator,
        private readonly Core\Installer\SchemaInstaller $schemaInstaller,
        private readonly Core\Installer\AclInstaller $aclInstaller,
        private readonly AdminModulesViewProvider $adminModulesViewProvider,
        private readonly CacheClearService $cacheClearService
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     *
     * @throws DBALException
     */
    public function __invoke(?string $action = null, ?string $dir = null): array|Response
    {
        return match ($action) {
            'install' => $this->installModule($dir),
            'uninstall' => $this->uninstallModule($dir),
            default => $this->outputPage(),
        };
    }

    /**
     * @throws ModuleInstallerException
     */
    private function moduleInstallerExists(string $serviceId): void
    {
        if ($this->schemaLocator->has($serviceId) === false) {
            throw new ModuleInstallerException($this->translator->t('system', 'module_installer_not_found'));
        }
    }

    /**
     * @param string[] $dependencies
     *
     * @throws ModuleInstallerException
     */
    private function checkForFailedModuleDependencies(array $dependencies, string $phrase): void
    {
        if (!empty($dependencies)) {
            throw new ModuleInstallerException($this->translator->t('system', $phrase, ['%modules%' => implode(', ', $dependencies)]));
        }
    }

    private function purgeCaches(): void
    {
        Core\Cache\Purge::doPurge([
            $this->applicationPath->getCacheDir() . 'container.php',
            $this->applicationPath->getCacheDir() . 'container.php.meta',
        ]);

        $this->cacheClearService->clearCacheByType('general');
        $this->cacheClearService->clearCacheByType('page');
        $this->cacheClearService->clearCacheByType('templates');
    }

    /**
     * @throws DBALException
     */
    private function renewCaches(): void
    {
        $this->eventDispatcher->dispatch(new RenewCacheEvent());
    }

    /**
     * @throws DBALException
     */
    private function installModule(string $moduleDirectory): Response
    {
        $result = false;

        try {
            if ($this->modules->isInstalled($moduleDirectory) === true) {
                throw new ModuleInstallerException($this->translator->t('system', 'module_already_installed'));
            }

            $this->moduleInstallerExists($moduleDirectory);

            $moduleSchema = $this->schemaLocator->get($moduleDirectory);

            $dependencies = $this->installerHelper->checkInstallDependencies($moduleSchema);
            $this->checkForFailedModuleDependencies($dependencies, 'enable_following_modules_first');

            $result = $this->schemaInstaller->install($moduleSchema);
            $resultAcl = $this->aclInstaller->install($moduleSchema);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_installation_' . ($result !== false && $resultAcl !== false ? 'success' : 'error')
            );
        } catch (ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages->setMessage($result, $text, $this->request->getFullPath());
    }

    private function uninstallModule(string $moduleDirectory): Response
    {
        $result = false;

        try {
            if ($this->modules->isInstalled($moduleDirectory) === false) {
                throw new ModuleInstallerException(\sprintf($this->translator->t('system', 'module_not_installed'), $moduleDirectory));
            }

            $this->moduleInstallerExists($moduleDirectory);

            $moduleSchema = $this->schemaLocator->get($moduleDirectory);

            $dependencies = $this->installerHelper->checkUninstallDependencies($moduleSchema);
            $this->checkForFailedModuleDependencies($dependencies, 'uninstall_following_modules_first');

            $result = $this->schemaInstaller->uninstall($moduleSchema);
            $resultAcl = $this->aclInstaller->uninstall($moduleSchema);

            $this->purgeCaches();

            $text = $this->translator->t(
                'system',
                'mod_uninstallation_' . ($result !== false && $resultAcl !== false ? 'success' : 'error')
            );
        } catch (ModuleInstallerException $e) {
            $text = $e->getMessage();
        }

        return $this->redirectMessages->setMessage($result, $text, $this->request->getFullPath());
    }

    /**
     * @return array<string, mixed>
     *
     * @throws DBALException
     */
    private function outputPage(): array
    {
        $this->renewCaches();

        return ($this->adminModulesViewProvider)();
    }
}
