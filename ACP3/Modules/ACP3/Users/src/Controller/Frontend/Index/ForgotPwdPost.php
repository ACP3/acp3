<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\FormAction;
use ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException;
use ACP3\Core\Validation\ValidationRules\EmailValidationRule;
use ACP3\Modules\ACP3\Users;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class ForgotPwdPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly FormAction $actionHelper,
        private readonly Core\Validation\Validator $validator,
        private readonly Core\Helpers\Alerts $alertsHelper,
        private readonly Core\Helpers\Secure $secureHelper,
        private readonly Users\Model\UsersModel $usersModel,
        private readonly Users\Repository\UserRepository $userRepository,
        private readonly Users\Validation\AccountForgotPasswordFormValidation $accountForgotPasswordFormValidation,
        private readonly Users\Helpers\SendPasswordChangeEmail $sendPasswordChangeEmail,
    ) {
        parent::__construct($context);
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

                $this->accountForgotPasswordFormValidation->validate($formData);

                $newPassword = $this->secureHelper->salt(Users\Model\UserModel::SALT_LENGTH);
                $user = $this->fetchUserByFormFieldValue($formData['nick_mail']);
                $isMailSent = ($this->sendPasswordChangeEmail)($user, $newPassword);
                $result = false;

                // Das Passwort des Benutzers nur abändern, wenn die E-Mail erfolgreich versendet werden konnte
                if ($isMailSent === true) {
                    $updateValues = [
                        'pwd' => $newPassword,
                        'login_errors' => 0,
                    ];
                    $result = $this->usersModel->save($updateValues, $user['id']);
                }

                return $this->alertsHelper->confirmBox(
                    $this->translator->t(
                        'users',
                        $isMailSent === true && $result !== false ? 'forgot_pwd_success' : 'forgot_pwd_error'
                    ),
                    $this->applicationPath->getWebRoot()
                );
            },
            $this->request->getFullPath()
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     * @throws ValidationRuleNotFoundException
     */
    protected function fetchUserByFormFieldValue(string $nickNameOrEmail): array
    {
        if ($this->validator->is(EmailValidationRule::class, $nickNameOrEmail) === true
            && $this->userRepository->resultExistsByEmail($nickNameOrEmail) === true
        ) {
            $user = $this->userRepository->getOneByEmail($nickNameOrEmail);
        } else {
            $user = $this->userRepository->getOneByNickname($nickNameOrEmail);
        }

        return $user;
    }
}
