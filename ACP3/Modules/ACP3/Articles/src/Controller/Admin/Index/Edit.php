<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Articles\Model\ArticlesModel;
use ACP3\Modules\ACP3\Articles\ViewProviders\AdminArticleEditViewProvider;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly AdminArticleEditViewProvider $adminArticleEditViewProvider,
        private readonly ArticlesModel $articlesModel,
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
        $article = $this->articlesModel->getOneById($id);

        if (empty($article) === false) {
            return ($this->adminArticleEditViewProvider)($article);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
