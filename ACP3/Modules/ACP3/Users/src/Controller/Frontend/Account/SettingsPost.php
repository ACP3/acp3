<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Account;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Users;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class SettingsPost extends AbstractAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly UserModelInterface $user,
        private readonly Users\Model\AuthenticationModel $authenticationModel,
        private readonly Users\Model\UsersModel $usersModel,
        private readonly Users\Validation\AccountSettingsFormValidation $accountSettingsFormValidation,
    ) {
        parent::__construct($context, $user);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(): array|string|Response
    {
        return $this->actionHelper->handlePostAction(
            function () {
                $formData = $this->request->getPost()->all();

                $this->accountSettingsFormValidation->validate($formData);

                if (!empty($formData['new_pwd']) && !empty($formData['new_pwd_repeat'])) {
                    $formData['pwd'] = $formData['new_pwd'];
                }

                $result = $this->usersModel->save($formData, $this->user->getUserId());

                $user = $this->usersModel->getOneById($this->user->getUserId());
                $cookie = $this->authenticationModel->setRememberMeCookie(
                    $this->user->getUserId(),
                    $user['remember_me_token']
                );

                $response = $this->actionHelper->setRedirectMessage(
                    $result,
                    $this->translator->t('system', $result ? 'settings_success' : 'settings_error')
                );

                $response->headers->setCookie($cookie);

                return $response;
            }
        );
    }
}
