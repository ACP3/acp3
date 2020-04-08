<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Galleryshare\Event\Listener;

use ACP3\Core\Model\Event\ModelSaveEvent;
use ACP3\Modules\ACP3\Gallery\Helpers;
use ACP3\Modules\ACP3\Gallery\Model\Repository\PictureRepository;
use ACP3\Modules\ACP3\Share\Helpers\SocialSharingManager;

class OnGalleryModelBeforeDeleteListener
{
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\Repository\PictureRepository
     */
    private $pictureRepository;
    /**
     * @var \ACP3\Modules\ACP3\Share\Helpers\SocialSharingManager
     */
    private $socialSharingManager;

    public function __construct(PictureRepository $pictureRepository, SocialSharingManager $socialSharingManager)
    {
        $this->pictureRepository = $pictureRepository;
        $this->socialSharingManager = $socialSharingManager;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __invoke(ModelSaveEvent $event)
    {
        if (!$event->isDeleteStatement()) {
            return;
        }

        foreach ($event->getEntryId() as $item) {
            $uri = \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $item);
            $this->socialSharingManager->deleteSharingInfo($uri);

            $this->deletePictureSocialSharing($item);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function deletePictureSocialSharing(int $galleryId): void
    {
        foreach ($this->pictureRepository->getPicturesByGalleryId($galleryId) as $picture) {
            $uri = \sprintf(Helpers::URL_KEY_PATTERN_PICTURE, $picture['id']);

            $this->socialSharingManager->deleteSharingInfo($uri);
        }
    }
}