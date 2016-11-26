<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users;

/**
 * Class ForgotPwd
 * @package ACP3\Modules\ACP3\Users\Controller\Frontend\Index
 */
class ForgotPwd extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    protected $secureHelper;
    /**
     * @var \ACP3\Modules\ACP3\Users\Model\Repository\UserRepository
     */
    protected $userRepository;
    /**
     * @var \ACP3\Modules\ACP3\Users\Validation\AccountForgotPasswordFormValidation
     */
    protected $accountForgotPasswordFormValidation;
    /**
     * @var \ACP3\Core\Helpers\SendEmail
     */
    protected $sendEmail;

    /**
     * ForgotPwd constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Core\Helpers\FormToken $formTokenHelper
     * @param \ACP3\Core\Helpers\Secure $secureHelper
     * @param \ACP3\Modules\ACP3\Users\Model\Repository\UserRepository $userRepository
     * @param \ACP3\Modules\ACP3\Users\Validation\AccountForgotPasswordFormValidation $accountForgotPasswordFormValidation
     * @param \ACP3\Core\Helpers\SendEmail $sendEmail
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Helpers\FormToken $formTokenHelper,
        Core\Helpers\Secure $secureHelper,
        Users\Model\Repository\UserRepository $userRepository,
        Users\Validation\AccountForgotPasswordFormValidation $accountForgotPasswordFormValidation,
        Core\Helpers\SendEmail $sendEmail
    ) {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
        $this->secureHelper = $secureHelper;
        $this->userRepository = $userRepository;
        $this->accountForgotPasswordFormValidation = $accountForgotPasswordFormValidation;
        $this->sendEmail = $sendEmail;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute()
    {
        if ($this->user->isAuthenticated() === true) {
            return $this->redirect()->toNewPage($this->appPath->getWebRoot());
        }

        return [
            'form' => array_merge(['nick_mail' => ''], $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken()
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePost()
    {
        return $this->actionHelper->handlePostAction(
            function () {
                $formData = $this->request->getPost()->all();

                $this->accountForgotPasswordFormValidation->validate($formData);

                $newPassword = $this->secureHelper->salt(Users\Model\UserModel::SALT_LENGTH);
                $user = $this->fetchUserByFormFieldValue($formData['nick_mail']);
                $mailIsSent = $this->sendPasswordChangeEmail($user, $newPassword);

                // Das Passwort des Benutzers nur abändern, wenn die E-Mail erfolgreich versendet werden konnte
                if ($mailIsSent === true) {
                    $salt = $this->secureHelper->salt(Users\Model\UserModel::SALT_LENGTH);
                    $updateValues = [
                        'pwd' => $this->secureHelper->generateSaltedPassword($salt, $newPassword, 'sha512'),
                        'pwd_salt' => $salt,
                        'login_errors' => 0
                    ];
                    $bool = $this->userRepository->update($updateValues, $user['id']);
                }

                $this->setTemplate($this->get('core.helpers.alerts')->confirmBox(
                    $this->translator->t(
                        'users',
                        $mailIsSent === true && isset($bool) && $bool !== false ? 'forgot_pwd_success' : 'forgot_pwd_error'
                    ),
                    $this->appPath->getWebRoot()
                ));
            },
            $this->request->getFullPath()
        );
    }

    /**
     * @param string $nickNameOrEmail
     * @return array
     */
    protected function fetchUserByFormFieldValue($nickNameOrEmail)
    {
        if ($this->get('core.validation.validation_rules.email_validation_rule')->isValid($nickNameOrEmail) === true &&
            $this->userRepository->resultExistsByEmail($nickNameOrEmail) === true
        ) {
            $user = $this->userRepository->getOneByEmail($nickNameOrEmail);
        } else {
            $user = $this->userRepository->getOneByNickname($nickNameOrEmail);
        }

        return $user;
    }

    /**
     * @param array $user
     * @param string $newPassword
     * @return bool
     */
    protected function sendPasswordChangeEmail(array $user, $newPassword)
    {
        $host = $this->request->getHost();
        $systemSettings = $this->config->getSettings(Schema::MODULE_NAME);

        $subject = $this->translator->t(
            'users',
            'forgot_pwd_mail_subject',
            [
                '{title}' => $systemSettings['site_title'],
                '{host}' => $host
            ]
        );
        $body = $this->translator->t(
            'users',
            'forgot_pwd_mail_message', [
                '{name}' => $user['nickname'],
                '{mail}' => $user['mail'],
                '{password}' => $newPassword,
                '{title}' => $systemSettings['site_title'],
                '{host}' => $host
            ]
        );

        $settings = $this->config->getSettings(Users\Installer\Schema::MODULE_NAME);
        return $this->sendEmail->execute(
            substr($user['realname'], 0, -2),
            $user['mail'],
            $settings['mail'],
            $subject,
            $body
        );
    }
}
