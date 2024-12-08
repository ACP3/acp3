<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filesseo\Extension;

use ACP3\Core\Date;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Modules\ACP3\Categories\Repository\CategoryRepository;
use ACP3\Modules\ACP3\Files\Helpers;
use ACP3\Modules\ACP3\Files\Installer\Schema;
use ACP3\Modules\ACP3\Files\Repository\FilesRepository;
use ACP3\Modules\ACP3\Seo\Extension\AbstractSitemapAvailabilityExtension;

class SitemapAvailabilityExtension extends AbstractSitemapAvailabilityExtension
{
    public function __construct(
        private readonly Date $date,
        RouterInterface $router,
        private readonly FilesRepository $filesRepository,
        private readonly CategoryRepository $categoryRepository,
        MetaStatementsServiceInterface $metaStatements,
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
        $this->addUrl('files/index/index', null, $isSecure);

        foreach ($this->categoryRepository->getAllByModuleName(Schema::MODULE_NAME) as $category) {
            $this->addUrl('files/index/files/cat_' . $category['id'], null, $isSecure);
        }

        foreach ($this->filesRepository->getAll($this->date->getCurrentDateTime()) as $result) {
            $this->addUrl(
                \sprintf(Helpers::URL_KEY_PATTERN, $result['id']),
                $this->date->toDateTime($result['updated_at']),
                $isSecure
            );
        }
    }
}
