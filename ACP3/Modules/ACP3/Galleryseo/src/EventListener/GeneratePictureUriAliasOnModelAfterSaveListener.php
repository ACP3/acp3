<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Galleryseo\EventListener;

use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Core\Modules;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Modules\ACP3\Gallery\Helpers;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;
use ACP3\Modules\ACP3\Seo\Core\Router\Aliases;
use ACP3\Modules\ACP3\Seo\Helper\UriAliasManager;
use ACP3\Modules\ACP3\Seo\Installer\Schema as SeoSchema;
use Doctrine\DBAL\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GeneratePictureUriAliasOnModelAfterSaveListener implements EventSubscriberInterface
{
    public function __construct(private readonly Modules $modules, private readonly PictureRepository $pictureRepository, private readonly Aliases $aliases, private readonly UriAliasManager $uriAliasManager, private readonly MetaStatementsServiceInterface $metaStatements)
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(AfterModelSaveEvent $event): void
    {
        if (!$this->modules->isInstalled(SeoSchema::MODULE_NAME)) {
            return;
        }

        if (!$event->isIsNewEntry()) {
            return;
        }

        $pictureId = $event->getEntryId();

        $galleryId = $this->pictureRepository->getGalleryIdFromPictureId($pictureId);
        $alias = $this->aliases->getUriAlias(\sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $galleryId), true);
        if (!empty($alias)) {
            $alias .= '/img-' . $pictureId;
        }
        $seoKeywords = $this->metaStatements->getKeywords(
            \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $galleryId)
        );
        $seoDescription = $this->metaStatements->getDescription(
            \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $galleryId)
        );
        $seoRobots = $this->metaStatements->getRobotsSetting(
            \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $galleryId)
        );

        $robotsSetting = array_flip($this->metaStatements->getRobotsMap());

        $this->uriAliasManager->insertUriAlias(
            \sprintf(Helpers::URL_KEY_PATTERN_PICTURE, $pictureId),
            $alias,
            $seoKeywords,
            $seoDescription,
            $robotsSetting[$seoRobots] ?? 0
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'gallery.model.gallery_pictures.after_save' => ['__invoke', -250],
        ];
    }
}
