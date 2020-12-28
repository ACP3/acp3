<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articlesseo\Event\Listener;

use ACP3\Core\Model\Event\ModelSaveEvent;
use ACP3\Core\Modules;
use ACP3\Modules\ACP3\Articles\Helpers;
use ACP3\Modules\ACP3\Seo\Helper\UriAliasManager;
use ACP3\Modules\ACP3\Seo\Installer\Schema as SeoSchema;

class OnArticlesModelDeleteAfterListener
{
    /**
     * @var UriAliasManager
     */
    private $uriAliasManager;
    /**
     * @var \ACP3\Core\Modules
     */
    private $modules;

    public function __construct(Modules $modules, UriAliasManager $uriAliasManager)
    {
        $this->uriAliasManager = $uriAliasManager;
        $this->modules = $modules;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __invoke(ModelSaveEvent $event)
    {
        if (!$this->modules->isInstalled(SeoSchema::MODULE_NAME)) {
            return;
        }

        if (!$event->isDeleteStatement()) {
            return;
        }

        foreach ($event->getEntryId() as $articleId) {
            $uri = \sprintf(Helpers::URL_KEY_PATTERN, $articleId);
            $this->uriAliasManager->deleteUriAlias($uri);
        }
    }
}