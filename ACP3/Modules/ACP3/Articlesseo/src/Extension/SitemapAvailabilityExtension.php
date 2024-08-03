<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articlesseo\Extension;

use ACP3\Core\Date;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Modules\ACP3\Articles\Helpers;
use ACP3\Modules\ACP3\Articles\Installer\Schema;
use ACP3\Modules\ACP3\Articles\Repository\ArticleRepository;
use ACP3\Modules\ACP3\Seo\Extension\AbstractSitemapAvailabilityExtension;

class SitemapAvailabilityExtension extends AbstractSitemapAvailabilityExtension
{
    public function __construct(
        protected Date $date,
        RouterInterface $router,
        protected ArticleRepository $articleRepository,
        MetaStatementsServiceInterface $metaStatements
    ) {
        parent::__construct($router, $metaStatements);
    }

    public function getModuleName(): string
    {
        return Schema::MODULE_NAME;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchSitemapUrls(?bool $isSecure = null): void
    {
        $this->addUrl('articles/index/index', null, $isSecure);

        foreach ($this->articleRepository->getAll($this->date->getCurrentDateTime()) as $result) {
            $this->addUrl(
                \sprintf(Helpers::URL_KEY_PATTERN, $result['id']),
                $this->date->toDateTime($result['updated_at']),
                $isSecure
            );
        }
    }
}
