<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Pagination\Exception\InvalidPageException;
use ACP3\Modules\ACP3\News;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly News\ViewProviders\NewsListViewProvider $newsListViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $cat = 0): Response
    {
        try {
            $response = $this->renderTemplate(null, ($this->newsListViewProvider)($cat));
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        } catch (InvalidPageException) {
            throw new ResultNotExistsException();
        }
    }
}
