<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Galleryseo\EventListener;

use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Core\Modules;
use ACP3\Modules\ACP3\Gallery\Helpers;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;
use ACP3\Modules\ACP3\Seo\Helper\UriAliasManager;
use ACP3\Modules\ACP3\Seo\Installer\Schema as SeoSchema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GeneratePictureUriAliasesOnModelAfterSaveListener implements EventSubscriberInterface
{
    public function __construct(private readonly Modules $modules, private readonly PictureRepository $pictureRepository, private readonly UriAliasManager $uriAliasManager)
    {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(AfterModelSaveEvent $event): void
    {
        if (!$this->modules->isInstalled(SeoSchema::MODULE_NAME)) {
            return;
        }

        if ($event->isIsNewEntry()) {
            return;
        }

        $galleryId = $event->getEntryId();
        $pictures = $this->pictureRepository->getPicturesByGalleryId($galleryId);

        $rawData = $event->getRawData();

        foreach ($pictures as $picture) {
            $this->uriAliasManager->insertUriAlias(
                \sprintf(Helpers::URL_KEY_PATTERN_PICTURE, $picture['id']),
                !empty($rawData['alias']) ? $rawData['alias'] . '/img-' . $picture['id'] : '',
                $rawData['seo_keywords'],
                $rawData['seo_description'],
                $rawData['seo_robots'],
                $rawData['seo_title'],
                $rawData['seo_structured_data'],
                $rawData['seo_canonical'],
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'gallery.model.gallery.after_save' => ['__invoke', -250],
        ];
    }
}
