<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Installer\Model;

use ACP3\Core\Installer\Exception\MissingInstallerException;
use ACP3\Core\Installer\Exception\ModuleNotInstallableException;
use ACP3\Core\Modules;
use ACP3\Core\Modules\SchemaUpdater;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class SchemaUpdateModel
{
    /**
     * @var \ACP3\Core\Modules
     */
    private $modules;
    /**
     * @var SchemaUpdater
     */
    private $schemaUpdater;
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $schemaLocator;
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $migrationLocator;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ContainerInterface $schemaLocator,
        ContainerInterface $migrationLocator,
        Modules $modules,
        SchemaUpdater $schemaUpdater
    ) {
        $this->modules = $modules;
        $this->schemaUpdater = $schemaUpdater;
        $this->schemaLocator = $schemaLocator;
        $this->migrationLocator = $migrationLocator;
        $this->logger = $logger;
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function updateModules(): array
    {
        $results = [];

        foreach ($this->modules->getAllModulesTopSorted() as $moduleInfo) {
            $moduleName = strtolower($moduleInfo['name']);

            if (!$this->modules->isInstallable($moduleName)) {
                continue;
            }

            try {
                $this->updateModule($moduleName);

                $results[$moduleName] = true;
            } catch (ModuleNotInstallableException $e) {
                // Intentionally omitted
            } catch (\Throwable $e) {
                $results[$moduleName] = false;

                $this->logger->error($e);
            }
        }

        return $results;
    }

    /**
     * Führt die Updateanweisungen eines Moduls aus.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     * @throws \ACP3\Core\Installer\Exception\MissingInstallerException
     * @throws \ACP3\Core\Modules\Exception\ModuleMigrationException
     */
    private function updateModule(string $moduleName): void
    {
        if (!$this->modules->isInstallable($moduleName)) {
            throw new ModuleNotInstallableException(sprintf('The module %s doesn\'t need to be installed, therefore it can\'t DB migrations.', $moduleName));
        }

        $serviceIdMigration = $moduleName . '.installer.migration';
        if (!$this->schemaLocator->has($moduleName) || !$this->migrationLocator->has($serviceIdMigration)) {
            throw new MissingInstallerException(sprintf('Could not find any schema or migration files for module "%s"', $moduleName));
        }

        $moduleSchema = $this->schemaLocator->get($moduleName);
        $moduleMigration = $this->migrationLocator->get($serviceIdMigration);
        if ($this->modules->isInstalled($moduleName) || \count($moduleMigration->renameModule()) > 0) {
            $this->schemaUpdater->updateSchema($moduleSchema, $moduleMigration);
        }
    }
}
