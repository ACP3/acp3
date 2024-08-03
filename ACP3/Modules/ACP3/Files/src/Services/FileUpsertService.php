<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Services;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Helpers\Upload;
use ACP3\Core\Validation\Exceptions\ValidationFailedException;
use ACP3\Modules\ACP3\Categories\Services\CategoryUpsertService;
use ACP3\Modules\ACP3\Files\Helpers;
use ACP3\Modules\ACP3\Files\Installer\Schema;
use ACP3\Modules\ACP3\Files\Model\FilesModel;
use ACP3\Modules\ACP3\Files\Validation\AdminFormValidation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpsertService
{
    public function __construct(private readonly Upload $filesUploadHelper, private readonly UserModelInterface $user, private readonly FilesModel $filesModel, private readonly AdminFormValidation $adminFormValidation, private readonly CategoryUpsertService $categoryUpsertService)
    {
    }

    /**
     * @param array<string, mixed> $updatedData
     *
     * @throws ValidationFailedException
     * @throws \ACP3\Core\Validation\Exceptions\InvalidFormTokenException
     * @throws \ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function upsert(array $updatedData, UploadedFile|string|null $file, ?int $downloadId = null): int
    {
        $this->adminFormValidation
            ->withFile($file)
            ->withUriAlias($downloadId !== null ? \sprintf(Helpers::URL_KEY_PATTERN, $downloadId) : '')
            ->validate($updatedData);

        $updatedData['user_id'] = $this->user->getUserId();
        $updatedData['cat'] = !empty($updatedData['cat_create'])
            ? $this->categoryUpsertService->createCategoryInline($updatedData['cat_create'], Schema::MODULE_NAME)
            : $updatedData['cat'];

        $currentData = $downloadId !== null ? $this->filesModel->getOneById($downloadId) : [];
        $updatedFileData = $this->updateAssociatedFile(
            $file,
            $updatedData,
            $currentData
        );
        $updatedData = array_merge($updatedData, $updatedFileData);

        return $this->filesModel->save($updatedData, $downloadId);
    }

    /**
     * @param array<string, mixed> $updatedData
     * @param array<string, mixed> $currentData
     *
     * @return array<string, mixed>
     *
     * @throws ValidationFailedException
     */
    private function updateAssociatedFile(string|UploadedFile|null $uploadedFile, array $updatedData, array $currentData): array
    {
        if ($uploadedFile instanceof UploadedFile) {
            $result = $this->filesUploadHelper->moveFile($uploadedFile->getPathname(), $uploadedFile->getClientOriginalName());
            $updatedFile = $result['name'];
            $fileSize = $result['size'];
        } elseif (\is_string($uploadedFile)) {
            $updatedFile = $uploadedFile;
            $fileSize = ((float) $updatedData['filesize']) . ' ' . $updatedData['unit'];
        } else {
            $updatedFile = $currentData['file'];
            $fileSize = $currentData['size'];
        }

        if (!empty($currentData['file'])) {
            $this->filesUploadHelper->removeUploadedFile($currentData['file']);
        }

        return [
            'file' => $updatedFile,
            'filesize' => $fileSize,
        ];
    }
}
