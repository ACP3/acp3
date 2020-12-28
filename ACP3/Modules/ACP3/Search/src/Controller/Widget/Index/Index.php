<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Search\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Search;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Search\ViewProviders\SearchWidgetViewProvider
     */
    private $searchWidgetViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Search\ViewProviders\SearchWidgetViewProvider $searchWidgetViewProvider
    ) {
        parent::__construct($context);

        $this->searchWidgetViewProvider = $searchWidgetViewProvider;
    }

    public function execute(): array
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        return ($this->searchWidgetViewProvider)();
    }
}