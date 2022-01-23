<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Polls;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EditPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private FormAction $actionHelper,
        private UserModelInterface $user,
        private Polls\Model\PollsModel $pollsModel,
        private Polls\Validation\AdminFormValidation $pollsValidator
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|JsonResponse|RedirectResponse|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(int $id): array|string|JsonResponse|RedirectResponse|Response
    {
        return $this->actionHelper->handleSaveAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $this->pollsValidator->validate($formData);

            $formData['user_id'] = $this->user->getUserId();
            $result = $this->pollsModel->save($formData, $id);

            if (!empty($formData['reset'])) {
                $this->pollsModel->resetVotesByPollId($id);
            }

            $result2 = $this->pollsModel->saveAnswers($formData['answers'], $id);

            return $result && $result2;
        });
    }
}
