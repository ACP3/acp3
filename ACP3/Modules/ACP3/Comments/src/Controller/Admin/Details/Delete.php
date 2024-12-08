<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Comments;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class Delete extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Comments\Model\CommentsModel $commentsModel,
        private readonly Comments\Repository\CommentRepository $commentRepository,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     *
     * @throws Exception
     */
    public function __invoke(int $id, ?string $action = null): array|Response
    {
        return $this->actionHelper->handleCustomDeleteAction(
            $action,
            function (array $items) use ($id) {
                $result = $this->commentsModel->delete($items);

                // If there are no comments for the given module, redirect to the general comments admin panel page
                if ($this->commentRepository->countAll($id) === 0) {
                    $redirectUrl = 'acp/comments';
                } else {
                    $redirectUrl = 'acp/comments/details/index/id_' . $id;
                }

                return $this->actionHelper->setRedirectMessage(
                    $result,
                    $this->translator->t('system', $result ? 'delete_success' : 'delete_error'),
                    $redirectUrl
                );
            },
            'acp/comments/details/delete/id_' . $id,
            'acp/comments/details/index/id_' . $id
        );
    }
}
