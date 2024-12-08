<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Pictures;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Modules\ACP3\Gallery\Model\PictureModel;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;
use Symfony\Component\HttpFoundation\Response;

class OrderPost extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly RedirectResponse $redirectResponse,
        private readonly PictureRepository $pictureRepository,
        private readonly PictureModel $pictureModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, string $action): Response
    {
        if (($action === 'up' || $action === 'down') && $this->pictureRepository->pictureExists($id) === true) {
            if ($action === 'up') {
                $this->pictureModel->moveUp($id);
            } else {
                $this->pictureModel->moveDown($id);
            }

            $galleryId = $this->pictureRepository->getGalleryIdFromPictureId($id);

            return $this->redirectResponse->temporary('acp/gallery/pictures/index/id_' . $galleryId);
        }

        throw new ResultNotExistsException();
    }
}
