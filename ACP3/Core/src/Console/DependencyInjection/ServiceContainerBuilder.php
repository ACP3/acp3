<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Console\DependencyInjection;

use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Installer\DependencyInjection\RegisterInstallersCompilerPass;
use ACP3\Core\Migration\DependencyInjection\RegisterMigrationsCompilerPass;
use ACP3\Core\Model\DataProcessor\DependencyInjection\RegisterColumnTypesCompilerPass;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

final class ServiceContainerBuilder extends ContainerBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ApplicationPath $applicationPath,
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
        $this->set('core.logger.system_logger', $this->logger);
        $this->set('core.environment.application_path', $this->applicationPath);

        $this
            ->addCompilerPass(new RegisterListenersPass())
            ->addCompilerPass(new RegisterInstallersCompilerPass())
            ->addCompilerPass(new RegisterMigrationsCompilerPass())
            ->addCompilerPass(new RegisterCommandsCompilerPass())
            ->addCompilerPass(new RegisterColumnTypesCompilerPass());

        $loader = new YamlFileLoader($this, new FileLocator(__DIR__));
        $loader->import($this->applicationPath->getAppDir() . 'config.yml');
        $loader->import(ComponentRegistry::getPathByName('core') . '/Resources/config/console.yml');

        foreach (ComponentRegistry::allTopSorted() as $module) {
            $path = $module->getPath() . '/Resources/config/services.yml';

            if (is_file($path)) {
                $loader->import($path);
            }
        }

        $this->compile();
    }

    /**
     * @throws \Exception
     */
    public static function create(
        LoggerInterface $logger, ApplicationPath $applicationPath,
    ): ServiceContainerBuilder {
        return new ServiceContainerBuilder($logger, $applicationPath);
    }
}
