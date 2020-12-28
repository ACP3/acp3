<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Files;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Files\ViewProviders\FilesWidgetViewProvider
     */
    private $filesWidgetViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Files\ViewProviders\FilesWidgetViewProvider $filesWidgetViewProvider
    ) {
        parent::__construct($context);

        $this->filesWidgetViewProvider = $filesWidgetViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(?int $limit = null, ?int $categoryId = null, string $template = ''): array
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        $this->setTemplate(\urldecode($template));

        return ($this->filesWidgetViewProvider)($categoryId, $limit);
    }
}