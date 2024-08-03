<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Installer;

use ACP3\Core\Database\Connection;
use ACP3\Core\Repository\ModuleAwareRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SchemaHelper
{
    use ContainerAwareTrait;

    public function __construct(private readonly Connection $db, private readonly ModuleAwareRepositoryInterface $moduleAwareRepository)
    {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getDb(): Connection
    {
        return $this->db;
    }

    public function getModuleAwareRepository(): ModuleAwareRepositoryInterface
    {
        return $this->moduleAwareRepository;
    }

    /**
     * Executes all given SQL queries.
     *
     * @param array<string|callable> $queries
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception\ModuleMigrationException
     * @throws \Doctrine\DBAL\Exception
     */
    public function executeSqlQueries(array $queries, string $moduleName = ''): void
    {
        if (\count($queries) === 0) {
            return;
        }

        $search = ['{pre}', '{engine}', '{charset}'];
        $replace = [$this->db->getPrefix(), 'ENGINE=InnoDB', 'CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`'];

        if ($this->moduleAwareRepository->coreTablesExist()) {
            $search[] = '{moduleId}';
            $replace[] = $this->getModuleId($moduleName);
        }

        try {
            foreach ($queries as $query) {
                if (\is_callable($query)) {
                    if ($query() === false) {
                        throw new Exception\ModuleMigrationException(\sprintf('An error occurred while executing a migration inside a closure for module "%s"', $moduleName));
                    }
                } elseif (!empty($query)) {
                    $this->db->executeStatement(str_ireplace($search, $replace, $query));
                }
            }
        } catch (\Exception $e) {
            throw new Exception\ModuleMigrationException(\sprintf('An error occurred while executing a migration for module "%s"', $moduleName), 0, $e);
        }
    }

    /**
     * Returns the module-ID.
     */
    public function getModuleId(string $moduleName): int
    {
        return $this->moduleAwareRepository->getModuleId($moduleName) ?: 0;
    }

    public function moduleIsInstalled(string $moduleName): bool
    {
        return $this->moduleAwareRepository->moduleExists($moduleName);
    }
}
