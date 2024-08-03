<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Galleryseo\Extension;

use ACP3\Core\Date;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Modules\ACP3\Gallery\Helpers;
use ACP3\Modules\ACP3\Gallery\Installer\Schema;
use ACP3\Modules\ACP3\Gallery\Repository\GalleryRepository;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;
use ACP3\Modules\ACP3\Seo\Extension\AbstractSitemapAvailabilityExtension;

class SitemapAvailabilityExtension extends AbstractSitemapAvailabilityExtension
{
    public function __construct(
        protected Date $date,
        RouterInterface $router,
        protected GalleryRepository $galleryRepository,
        protected PictureRepository $pictureRepository,
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
    protected function fetchSitemapUrls(?bool $isSecure = null): void
    {
        $this->addUrl('gallery/index/index', null, $isSecure);

        foreach ($this->galleryRepository->getAll($this->date->getCurrentDateTime()) as $result) {
            $this->addUrl(
                \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $result['id']),
                $this->date->toDateTime($result['updated_at']),
                $isSecure
            );

            foreach ($this->pictureRepository->getPicturesByGalleryId($result['id']) as $picture) {
                $this->addUrl(
                    \sprintf(Helpers::URL_KEY_PATTERN_PICTURE, $picture['id']),
                    $this->date->toDateTime($result['updated_at']),
                    $isSecure
                );
            }
        }
    }
}
