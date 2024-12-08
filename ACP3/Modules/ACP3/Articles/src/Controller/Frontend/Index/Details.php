<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Frontend\Index;

use ACP3\Core\Cache\CacheResponseTrait;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Date;
use ACP3\Modules\ACP3\Articles\Repository\ArticleRepository;
use ACP3\Modules\ACP3\Articles\ViewProviders\ArticlePaginatedViewProvider;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Details extends AbstractWidgetAction
{
    use CacheResponseTrait;

    public function __construct(
        Context $context,
        private readonly ArticlePaginatedViewProvider $articlePaginatedViewProvider,
        private readonly Date $date,
        private readonly ArticleRepository $articleRepository,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): Response
    {
        if ($this->articleRepository->resultExists($id, $this->date->getCurrentDateTime()) === true) {
            $viewData = ($this->articlePaginatedViewProvider)($id);

            if ($this->articlePaginatedViewProvider->getLayout()) {
                $this->view->setLayout($this->articlePaginatedViewProvider->getLayout());
            }

            $response = $this->renderTemplate(null, $viewData);
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        }

        throw new ResultNotExistsException();
    }
}
