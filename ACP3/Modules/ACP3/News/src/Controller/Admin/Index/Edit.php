<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly News\Model\NewsModel $newsModel,
        private readonly News\ViewProviders\AdminNewsEditViewProvider $adminNewsEditViewProvider,
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
        $news = $this->newsModel->getOneById($id);

        if (empty($news) === false) {
            return ($this->adminNewsEditViewProvider)($news);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
