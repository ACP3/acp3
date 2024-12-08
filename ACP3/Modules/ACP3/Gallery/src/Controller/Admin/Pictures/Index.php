<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Pictures;

use ACP3\Core;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Modules\ACP3\Gallery;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class Index extends AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Gallery\Model\GalleryModel $galleryModel,
        private readonly Gallery\ViewProviders\PictureDataGridViewProvider $pictureDataGridViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, array<string, mixed>>|JsonResponse
     *
     * @throws Exception
     */
    public function __invoke(int $id): array|JsonResponse
    {
        $gallery = $this->galleryModel->getOneById($id);

        if (!empty($gallery)) {
            return ($this->pictureDataGridViewProvider)($id, $gallery);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
