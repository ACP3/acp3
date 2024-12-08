<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Image extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Gallery\Repository\PictureRepository $pictureRepository,
        private readonly Gallery\Helper\ThumbnailGenerator $thumbnailGenerator,
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, ?string $action = null)
    {
        set_time_limit(20);
        $picture = $this->pictureRepository->getFileById($id);
        $action = $action === 'thumb' ? 'thumb' : '';

        try {
            return new RedirectResponse($this->thumbnailGenerator->generateThumbnail($picture, $action)->getFileWeb());
        } catch (Core\Picture\Exception\PictureGenerateException $e) {
            throw new Core\Controller\Exception\ResultNotExistsException('', $e);
        }
    }
}
