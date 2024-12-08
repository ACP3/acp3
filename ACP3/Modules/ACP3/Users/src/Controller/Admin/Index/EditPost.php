<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Helpers\FormAction;
use ACP3\Core\Helpers\RedirectMessages;
use ACP3\Modules\ACP3\Permissions\Helpers;
use ACP3\Modules\ACP3\Users\Model\AuthenticationModel;
use ACP3\Modules\ACP3\Users\Model\UsersModel;
use ACP3\Modules\ACP3\Users\Validation\AdminFormValidation;
use Doctrine\DBAL\ConnectionException;
use Symfony\Component\HttpFoundation\Response;

class EditPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly FormAction $actionHelper,
        private readonly UserModelInterface $user,
        private readonly AuthenticationModel $authenticationModel,
        private readonly UsersModel $usersModel,
        private readonly AdminFormValidation $adminFormValidation,
        private readonly Helpers $permissionsHelpers,
        private readonly RedirectMessages $redirectMessages,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array|string|Response
    {
        return $this->actionHelper->handleSaveAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $this->adminFormValidation
                ->withUserId($id)
                ->validate($formData);

            $this->permissionsHelpers->updateUserRoles($formData['roles'], $id);

            if (!empty($formData['new_pwd']) && !empty($formData['new_pwd_repeat'])) {
                $formData['pwd'] = $formData['new_pwd'];
            }

            $result = $this->usersModel->save($formData, $id);

            $response = $this->redirectMessages->setMessage(
                (bool) $result,
                $this->translator->t('system', 'save' . ($result ? '_success' : '_error'))
            );

            $this->updateCurrentlyLoggedInUserCookie($id, $response);

            return $response;
        });
    }

    /**
     * @throws \Exception
     */
    protected function updateCurrentlyLoggedInUserCookie(int $userId, Response $response): void
    {
        if ($userId === $this->user->getUserId() && $this->request->getCookies()->has(AuthenticationModel::AUTH_NAME)) {
            $user = $this->usersModel->getOneById($userId);
            $cookie = $this->authenticationModel->setRememberMeCookie(
                $userId,
                $user['remember_me_token']
            );
            $response->headers->setCookie($cookie);
        }
    }
}
