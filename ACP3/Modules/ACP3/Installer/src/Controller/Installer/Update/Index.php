<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Controller\Installer\Update;

use ACP3\Core\Cache\Purge;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Migration\Migrator;
use ACP3\Modules\ACP3\Installer\Core\Environment\ApplicationPath;

class Index extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly Migrator $migrator,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function __invoke(?string $action = null): ?array
    {
        if ($action === 'confirmed') {
            return $this->doUpdate();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     * @throws \Exception
     */
    private function doUpdate(): array
    {
        $results = $this->migrator->updateModules();

        $this->setTemplate('Installer/Installer/update.index.result.tpl');
        $this->clearCaches();

        return [
            'results' => $results,
            'hasErrors' => $this->checkExecutedMigrationsForErrors($results),
        ];
    }

    private function clearCaches(): void
    {
        Purge::doPurge([
            ACP3_ROOT_DIR . '/cache/',
            $this->applicationPath->getUploadsDir() . 'assets',
        ]);
    }

    /**
     * @param array<string, \Throwable[]|null> $executedMigrations
     */
    private function checkExecutedMigrationsForErrors(array $executedMigrations): bool
    {
        foreach ($executedMigrations as $result) {
            if ($result !== null) {
                return true;
            }
        }

        return false;
    }
}
