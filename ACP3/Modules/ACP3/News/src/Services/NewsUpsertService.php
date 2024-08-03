<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Services;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Modules\ACP3\Categories\Services\CategoryUpsertService;
use ACP3\Modules\ACP3\News\Helpers;
use ACP3\Modules\ACP3\News\Installer\Schema;
use ACP3\Modules\ACP3\News\Model\NewsModel;
use ACP3\Modules\ACP3\News\Validation\AdminFormValidation;

class NewsUpsertService
{
    public function __construct(private readonly UserModelInterface $user, private readonly NewsModel $newsModel, private readonly AdminFormValidation $adminFormValidation, private readonly CategoryUpsertService $categoryUpsertService)
    {
    }

    /**
     * @param array<string, mixed> $updatedData
     *
     * @throws \ACP3\Core\Validation\Exceptions\InvalidFormTokenException
     * @throws \ACP3\Core\Validation\Exceptions\ValidationFailedException
     * @throws \ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function upsert(array $updatedData, ?int $newsId = null): int
    {
        $this->adminFormValidation
            ->withUriAlias($newsId !== null ? \sprintf(Helpers::URL_KEY_PATTERN, $newsId) : '')
            ->validate($updatedData);

        $updatedData['cat'] = !empty($updatedData['cat_create'])
            ? $this->categoryUpsertService->createCategoryInline($updatedData['cat_create'], Schema::MODULE_NAME)
            : $updatedData['cat'];
        $updatedData['user_id'] = $this->user->getUserId();

        return $this->newsModel->save($updatedData, $newsId);
    }
}
