<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Details extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Core\Date $date,
        private readonly News\Repository\NewsRepository $newsRepository,
        private readonly News\ViewProviders\NewsDetailsViewProvider $newsDetailsViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): Response
    {
        if ($this->newsRepository->resultExists($id, $this->date->getCurrentDateTime()) == 1) {
            $response = $this->renderTemplate(null, ($this->newsDetailsViewProvider)($id));
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
