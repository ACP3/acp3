<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Gallery;
use Symfony\Component\HttpFoundation\Response;

class Delete extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Gallery\Helper\ThumbnailGenerator $thumbnailGenerator,
        private readonly Gallery\Model\GalleryModel $galleryModel,
        private readonly Gallery\Repository\PictureRepository $pictureRepository,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     */
    public function __invoke(?string $action = null): array|Response
    {
        return $this->actionHelper->handleDeleteAction(
            $action,
            function (array $items) {
                foreach ($items as $item) {
                    $pictures = $this->pictureRepository->getPicturesByGalleryId($item);
                    foreach ($pictures as $row) {
                        $this->thumbnailGenerator->removePictureFromFilesystem($row['file']);
                    }
                }

                return $this->galleryModel->delete($items);
            }
        );
    }
}
