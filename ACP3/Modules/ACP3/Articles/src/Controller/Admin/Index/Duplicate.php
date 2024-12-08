<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Articles\Model\ArticlesModel;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class Duplicate extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly FormAction $actionHelper,
        private readonly ArticlesModel $articlesModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Exception
     */
    public function __invoke(int $id): Response
    {
        return $this->actionHelper->handleDuplicateAction(fn () => $this->articlesModel->duplicate($id));
    }
}
