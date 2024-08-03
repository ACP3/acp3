<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Model;

use ACP3\Core\Database\Connection;
use ACP3\Core\Helpers\Secure;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Installer\Exception\ModuleMigrationException;
use ACP3\Core\Installer\SampleDataInterface;
use ACP3\Core\Installer\SchemaHelper;
use ACP3\Core\Installer\SchemaRegistrar;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Installer\Core\DependencyInjection\ServiceContainerBuilder;
use ACP3\Modules\ACP3\Installer\Core\Environment\ApplicationPath;
use ACP3\Modules\ACP3\Installer\Helpers\Install;
use ACP3\Modules\ACP3\Installer\Helpers\ModuleInstaller;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users\Model\UserModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstallModel
{
    public function __construct(protected ContainerInterface $container, protected ApplicationPath $appPath, private readonly Secure $secure, protected Translator $translator, protected Install $installHelper, protected ModuleInstaller $moduleInstaller)
    {
    }

    /**
     * @param array<string, mixed> $formData
     */
    public function writeConfigFile(string $configFilePath, array $formData): void
    {
        $configParams = [
            'parameters' => [
                'db_host' => $formData['db_host'],
                'db_name' => $formData['db_name'],
                'db_table_prefix' => $formData['db_pre'],
                'db_password' => $formData['db_password'],
                'db_user' => $formData['db_user'],
                'db_driver' => 'pdo_mysql',
                'db_charset' => 'utf8mb4',
            ],
        ];

        $this->installHelper->writeConfigFile($configFilePath, $configParams);
    }

    /**
     * @throws \Exception
     */
    public function updateContainer(): void
    {
        $this->container = ServiceContainerBuilder::create(
            $this->appPath,
            true
        );
    }

    /**
     * @throws \Exception
     */
    public function installModules(): void
    {
        $this->moduleInstaller->installModules($this->container);
    }

    /**
     * @throws ModuleMigrationException
     */
    public function installAclResources(): void
    {
        /** @var SchemaRegistrar $schemaRegistrar */
        $schemaRegistrar = $this->container->get(SchemaRegistrar::class);

        foreach ($schemaRegistrar->all() as $schema) {
            if ($this->installHelper->installResources($schema, $this->container) === false) {
                throw new ModuleMigrationException(\sprintf('Error while installing ACL resources for the module %s.', $schema->getModuleName()));
            }
        }
    }

    /**
     * Set the module settings.
     *
     * @param array<string, mixed> $formData
     */
    public function configureModules(array $formData): void
    {
        $settings = [
            Schema::MODULE_NAME => [
                'date_format_long' => $this->secure->strEncode($formData['date_format_long']),
                'date_format_short' => $this->secure->strEncode($formData['date_format_short']),
                'date_time_zone' => $formData['date_time_zone'],
                'maintenance_message' => $this->translator->t('installer', 'offline_message'),
                'lang' => $this->translator->getLocale(),
                'design' => $formData['design'],
                'site_title' => !empty($formData['title']) ? $formData['title'] : 'ACP3',
            ],
            \ACP3\Modules\ACP3\Users\Installer\Schema::MODULE_NAME => [
                'mail' => $formData['mail'],
            ],
        ];

        /** @var SettingsInterface $config */
        $config = $this->container->get(SettingsInterface::class);

        foreach ($settings as $module => $data) {
            $config->saveSettings($data, $module);
        }
    }

    /**
     * @param array<string, mixed> $formData
     *
     * @throws \Exception
     */
    public function createSuperUser(array $formData): void
    {
        /** @var Connection $db */
        $db = $this->container->get(Connection::class);

        $salt = $this->secure->salt(UserModel::SALT_LENGTH);
        $currentDate = gmdate('Y-m-d H:i:s');

        $queries = [
            "INSERT INTO
                `{pre}users`
            VALUES
                (1, 1, {$db->getConnection()->quote($formData['user_name'])}, '{$this->secure->generateSaltedPassword($salt, $formData['user_pwd'], 'sha512')}', '{$salt}', '', 0, '', 1, '', 0, '{$formData['mail']}', 0, '', '', '', '', '', '', '', 0, 0, 0, '{$currentDate}');",
            'INSERT INTO `{pre}acl_user_roles` (`user_id`, `role_id`) VALUES (1, 4);',
        ];

        /** @var SchemaHelper $schemaHelper */
        $schemaHelper = $this->container->get(SchemaHelper::class);

        $schemaHelper->executeSqlQueries($queries);
    }

    /**
     * @throws ModuleMigrationException
     */
    public function installSampleData(): void
    {
        /** @var \Symfony\Component\DependencyInjection\ServiceLocator<SampleDataInterface> $sampleDataRegistrar */
        $sampleDataRegistrar = $this->container->get('core.installer.sample_data_registrar');
        /** @var SchemaHelper $schemaHelper */
        $schemaHelper = $this->container->get(SchemaHelper::class);

        foreach ($sampleDataRegistrar->getProvidedServices() as $serviceId => $class) {
            try {
                $this->installHelper->installSampleData(
                    $sampleDataRegistrar->get($serviceId),
                    $schemaHelper
                );
            } catch (\Throwable $e) {
                throw new ModuleMigrationException(\sprintf('Error while installing module sample data of serviceId "%s".', $serviceId), 0, $e);
            }
        }
    }
}
