<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Comments;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class EditPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Comments\Model\CommentsModel $commentsModel,
        private readonly Comments\Validation\AdminFormValidation $adminFormValidation,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(int $id): array|string|Response
    {
        $comment = $this->commentsModel->getOneById($id);

        return $this->actionHelper->handleSaveAction(
            function () use ($id, $comment) {
                $formData = $this->request->getPost()->all();

                $formData = array_merge($comment, $formData);

                $this->adminFormValidation->validate($formData);

                return $this->commentsModel->save($formData, $id);
            },
            'acp/comments/details/index/id_' . $comment['module_id']
        );
    }
}
