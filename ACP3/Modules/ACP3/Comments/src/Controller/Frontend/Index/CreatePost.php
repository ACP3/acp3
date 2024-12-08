<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Comments;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class CreatePost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Core\Modules $modules,
        private readonly UserModelInterface $user,
        private readonly Comments\Model\CommentsModel $commentsModel,
        private readonly Comments\Validation\FormValidation $formValidation,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(string $module, int $entryId, string $redirectUrl): array|string|Response
    {
        return $this->actionHelper->handlePostAction(
            function () use ($module, $entryId, $redirectUrl) {
                $formData = $this->request->getPost()->all();
                $ipAddress = $this->request->getSymfonyRequest()->getClientIp();

                $this->formValidation
                    ->withIpAddress($ipAddress)
                    ->validate($formData);

                $formData['date'] = 'now';
                $formData['ip'] = $ipAddress;
                $formData['user_id'] = $this->user->isAuthenticated() === true ? $this->user->getUserId() : null;
                $formData['module_id'] = $this->modules->getModuleId($module);
                $formData['entry_id'] = $entryId;

                $result = $this->commentsModel->save($formData);

                return $this->actionHelper->setRedirectMessage(
                    $result,
                    $this->translator->t('system', $result ? 'create_success' : 'create_error'),
                    base64_decode(urldecode($redirectUrl))
                );
            }
        );
    }
}
