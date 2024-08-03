<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Services;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Modules\ACP3\Articles\Helpers;
use ACP3\Modules\ACP3\Articles\Model\ArticlesModel;
use ACP3\Modules\ACP3\Articles\Validation\AdminFormValidation;

class ArticleUpsertService
{
    public function __construct(private readonly UserModelInterface $user, private readonly ArticlesModel $articlesModel, private readonly AdminFormValidation $adminFormValidation)
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
    public function upsert(array $updatedData, ?int $articleId = null): int
    {
        $this->adminFormValidation
            ->withUriAlias($articleId !== null ? \sprintf(Helpers::URL_KEY_PATTERN, $articleId) : '')
            ->validate($updatedData);

        $updatedData['user_id'] = $this->user->getUserId();

        return $this->articlesModel->save($updatedData, $articleId);
    }
}
