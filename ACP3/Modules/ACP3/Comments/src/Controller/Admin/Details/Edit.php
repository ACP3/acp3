<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Comments\Model\CommentsModel $commentsModel,
        private readonly Comments\ViewProviders\AdminCommentEditViewProvider $adminCommentEditViewProvider,
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
        $comment = $this->commentsModel->getOneById($id);

        if (empty($comment) === false) {
            return ($this->adminCommentEditViewProvider)($comment);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
