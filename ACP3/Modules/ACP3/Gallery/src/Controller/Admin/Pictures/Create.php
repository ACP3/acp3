<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Pictures;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private Gallery\Repository\GalleryRepository $galleryRepository,
        private Gallery\ViewProviders\AdminGalleryPictureCreateViewProvider $adminGalleryPictureCreateViewProvider
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        if ($this->galleryRepository->galleryExists($id) === true) {
            return ($this->adminGalleryPictureCreateViewProvider)($id);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
