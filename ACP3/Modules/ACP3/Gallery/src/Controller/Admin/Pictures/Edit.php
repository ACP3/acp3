<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Pictures;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Gallery\Model\PictureModel $pictureModel,
        private readonly Gallery\ViewProviders\AdminGalleryPictureEditViewProvider $adminGalleryPictureEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $picture = $this->pictureModel->getOneById($id);

        if (!empty($picture)) {
            return ($this->adminGalleryPictureEditViewProvider)($picture);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
