<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Helpers;

use ACP3\Core\Installer\AclInstaller;
use ACP3\Core\Installer\SampleDataInterface;
use ACP3\Core\Installer\SchemaHelper;
use ACP3\Core\Installer\SchemaInstaller;
use ACP3\Core\Installer\SchemaInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Yaml\Dumper;

class Install
{
    /**
     * Writes the system config file.
     *
     * @param array<string, mixed> $data
     */
    public function writeConfigFile(string $configFilePath, array $data): bool
    {
        if (is_writable($configFilePath) === true) {
            ksort($data);

            $dumper = new Dumper();
            $yaml = $dumper->dump($data);

            return file_put_contents($configFilePath, $yaml, LOCK_EX) !== false;
        }

        return false;
    }

    public function installModule(SchemaInterface $schema, ContainerInterface $container): bool
    {
        return $this->install($schema, $container, SchemaInstaller::class);
    }

    public function installResources(SchemaInterface $schema, ContainerInterface $container): bool
    {
        return $this->install($schema, $container, AclInstaller::class);
    }

    /**
     * @param class-string $installerServiceId
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function install(
        SchemaInterface $schema,
        ContainerInterface $container,
        string $installerServiceId,
    ): bool {
        /** @var \ACP3\Core\Modules\InstallerInterface $installer */
        $installer = $container->get($installerServiceId);

        return $installer->install($schema);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function installSampleData(
        SampleDataInterface $sampleData,
        SchemaHelper $schemaHelper,
    ): void {
        $schemaHelper->executeSqlQueries($sampleData->sampleData());
    }
}
