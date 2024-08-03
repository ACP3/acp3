<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Helpers;

use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\Component\ComponentTypeEnum;
use ACP3\Core\Database\Connection;
use ACP3\Core\Installer\SchemaRegistrar;
use ACP3\Core\Migration\MigrationServiceLocator;
use ACP3\Core\Migration\Repository\MigrationRepositoryInterface;
use ACP3\Modules\ACP3\System\Exception\ModuleInstallerException;
use Psr\Container\ContainerInterface;

class ModuleInstaller
{
    public function __construct(private readonly Install $installHelper, private readonly MigrationServiceLocator $migrationServiceLocator)
    {
    }

    /**
     * @return array<string, bool>
     *
     * @throws ModuleInstallerException
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function installModules(ContainerInterface $container): array
    {
        $installedModules = $this->doInstallModules($container);

        $this->doMarkAllMigrationsAsExecuted($container);

        return $installedModules;
    }

    /**
     * @return array<string, bool>
     *
     * @throws ModuleInstallerException
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    private function doInstallModules(ContainerInterface $container): array
    {
        /** @var ContainerInterface $schemaRegistrar */
        $schemaRegistrar = $container->get(SchemaRegistrar::class);

        $installableModules = array_filter(
            ComponentRegistry::excludeByType(ComponentRegistry::allTopSorted(), [ComponentTypeEnum::THEME]),
            static fn ($module) => $schemaRegistrar->has($module->getName())
        );

        $installedModules = [];
        foreach ($installableModules as $module) {
            if ($this->installHelper->installModule($schemaRegistrar->get($module->getName()), $container) === false) {
                throw new ModuleInstallerException(\sprintf('Error while installing module "%s"', $module->getName()));
            }

            $installedModules[$module->getName()] = true;
        }

        return $installedModules;
    }

    /**
     * Installing a module already ensures, that it is using the most current DB schema.
     * Therefore, we have to ensure that all the available migrations are getting marked as executed.
     * Otherwise, these migrations would get executed the next time the DB schema updater gets executed which could result in errors.
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    private function doMarkAllMigrationsAsExecuted(ContainerInterface $container): void
    {
        /** @var Connection $db */
        $db = $container->get(Connection::class);
        /** @var MigrationRepositoryInterface $migrationRepository */
        $migrationRepository = $container->get(MigrationRepositoryInterface::class);

        foreach ($this->migrationServiceLocator->getMigrations() as $fqcn => $migration) {
            $db->getConnection()->insert($migrationRepository->getTableName(), ['name' => $fqcn]);
        }
    }
}
