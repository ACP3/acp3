<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Services;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Modules\ACP3\Gallery\Helpers;
use ACP3\Modules\ACP3\Gallery\Model\GalleryModel;
use ACP3\Modules\ACP3\Gallery\Validation\GalleryFormValidation;

class GalleryUpsertService
{
    public function __construct(private readonly UserModelInterface $userModel, private readonly GalleryFormValidation $galleryFormValidation, private readonly GalleryModel $galleryModel)
    {
    }

    /**
     * @param array<string, mixed> $updatedData
     *
     * @throws \ACP3\Core\Validation\Exceptions\InvalidFormTokenException
     * @throws \ACP3\Core\Validation\Exceptions\ValidationFailedException
     * @throws \ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function upsert(array $updatedData, ?int $galleryId = null): int
    {
        $this->galleryFormValidation
            ->withUriAlias($galleryId === null ? '' : \sprintf(Helpers::URL_KEY_PATTERN_GALLERY, $galleryId))
            ->validate($updatedData);

        $updatedData['user_id'] = $this->userModel->getUserId();

        return $this->galleryModel->save($updatedData, $galleryId);
    }
}
