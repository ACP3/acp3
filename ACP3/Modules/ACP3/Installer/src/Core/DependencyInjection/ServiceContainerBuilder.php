<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Core\DependencyInjection;

use ACP3\Core\Application\BootstrapCache;
use ACP3\Core\Assets\DependencyInjection\RegisterAssetLibraryPass;
use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\Component\ComponentTypeEnum;
use ACP3\Core\Controller\DependencyInjection\RegisterControllerActionsPass;
use ACP3\Core\Environment\ApplicationMode;
use ACP3\Core\Installer\DependencyInjection\RegisterInstallersCompilerPass;
use ACP3\Core\Migration\DependencyInjection\RegisterMigrationsCompilerPass;
use ACP3\Core\Validation\DependencyInjection\RegisterValidationRulesPass;
use ACP3\Core\View\Renderer\Smarty\DependencyInjection\RegisterSmartyPluginsPass;
use ACP3\Modules\ACP3\Installer\Core\Environment\ApplicationPath;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\DependencyInjection\FragmentRendererPass;

final class ServiceContainerBuilder extends ContainerBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly ApplicationPath $applicationPath,
        private readonly bool $installationIsInProgress = false,
    ) {
        parent::__construct();

        $this->setUpContainer();
    }

    /**
     * @throws \Exception
     */
    private function setUpContainer(): void
    {
        $this->set('core.environment', $this->applicationPath->getApplicationMode());
        $this->set('core.environment.application_path', $this->applicationPath);

        $this
            ->addCompilerPass(new RegisterListenersPass())
            ->addCompilerPass(new RegisterSmartyPluginsPass())
            ->addCompilerPass(new RegisterControllerActionsPass())
            ->addCompilerPass(new RegisterValidationRulesPass())
            ->addCompilerPass(new RegisterInstallersCompilerPass())
            ->addCompilerPass(new RegisterMigrationsCompilerPass())
            ->addCompilerPass(new RegisterAssetLibraryPass())
            ->addCompilerPass(new FragmentRendererPass());

        $loader = new YamlFileLoader($this, new FileLocator(__DIR__));

        $this->setParameter('installationIsInProgress', $this->installationIsInProgress);

        $this->includeConfig($loader);

        // Themes currently don't define or override services, so we have to filter them out.
        // Otherwise we would get errors...
        $filteredComponents = ComponentRegistry::excludeByType(
            ComponentRegistry::allTopSorted(),
            [ComponentTypeEnum::THEME]
        );

        foreach ($filteredComponents as $component) {
            $loader->import($component->getPath() . '/Resources/config/services.yml');
        }

        $loader->import(ComponentRegistry::getPathByName('installer') . '/Resources/config/services_' . $this->applicationPath->getApplicationMode()->value . '.yml');

        $this->removeDefinition(BootstrapCache::class);

        $this->compile();
    }

    /**
     * @throws \Exception
     */
    private function includeConfig(YamlFileLoader $loader): void
    {
        if ($this->installationIsInProgress === false && $this->applicationPath->getApplicationMode() === ApplicationMode::INSTALLER) {
            $this->setParameter('db_host', '');
            $this->setParameter('db_name', '');
            $this->setParameter('db_table_prefix', '');
            $this->setParameter('db_password', '');
            $this->setParameter('db_user', '');
            $this->setParameter('db_driver', 'pdo_mysql');
            $this->setParameter('db_charset', 'utf8mb4');

            return;
        }

        $loader->import($this->applicationPath->getAppDir() . 'config.yml');
    }

    /**
     * @throws \Exception
     */
    public static function create(
        ApplicationPath $applicationPath,
        bool $installationIsInProgress = false,
    ): ServiceContainerBuilder {
        return new ServiceContainerBuilder($applicationPath, $installationIsInProgress);
    }
}
