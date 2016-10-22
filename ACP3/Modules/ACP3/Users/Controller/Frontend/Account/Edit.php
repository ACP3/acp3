<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Account;

use ACP3\Core;
use ACP3\Modules\ACP3\Users;

/**
 * Class Edit
 * @package ACP3\Modules\ACP3\Users\Controller\Frontend\Account
 */
class Edit extends AbstractAction
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
     * @var \ACP3\Modules\ACP3\Users\Helpers\Forms
     */
    protected $userFormsHelper;
    /**
     * @var \ACP3\Modules\ACP3\Users\Validation\AccountFormValidation
     */
    protected $accountFormValidation;
    /**
     * @var \ACP3\Modules\ACP3\Users\Model\AuthenticationModel
     */
    protected $authenticationModel;
    /**
     * @var Users\Model\UsersModel
     */
    protected $usersModel;

    /**
     * Edit constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Core\Helpers\FormToken $formTokenHelper
     * @param \ACP3\Core\Helpers\Secure $secureHelper
     * @param \ACP3\Modules\ACP3\Users\Helpers\Forms $userFormsHelper
     * @param \ACP3\Modules\ACP3\Users\Model\AuthenticationModel $authenticationModel
     * @param Users\Model\UsersModel $usersModel
     * @param \ACP3\Modules\ACP3\Users\Validation\AccountFormValidation $accountFormValidation
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Helpers\FormToken $formTokenHelper,
        Core\Helpers\Secure $secureHelper,
        Users\Helpers\Forms $userFormsHelper,
        Users\Model\AuthenticationModel $authenticationModel,
        Users\Model\UsersModel $usersModel,
        Users\Validation\AccountFormValidation $accountFormValidation
    ) {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
        $this->secureHelper = $secureHelper;
        $this->userFormsHelper = $userFormsHelper;
        $this->authenticationModel = $authenticationModel;
        $this->accountFormValidation = $accountFormValidation;
        $this->usersModel = $usersModel;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute()
    {
        if ($this->request->getPost()->count() !== 0) {
            return $this->executePost($this->request->getPost()->all());
        }

        $user = $this->user->getUserInfo();

        $this->view->assign(
            $this->userFormsHelper->fetchUserProfileFormFields(
                $user['birthday'],
                $user['country'],
                $user['gender']
            )
        );

        return [
            'contact' => $this->userFormsHelper->fetchContactDetails(
                $user['mail'],
                $user['website'],
                $user['icq'],
                $user['skype']
            ),
            'form' => array_merge($user, $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken()
        ];
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($formData) {
                $this->accountFormValidation
                    ->setUserId($this->user->getUserId())
                    ->validate($formData);

                if (!empty($formData['new_pwd']) && !empty($formData['new_pwd_repeat'])) {
                    $salt = $this->secureHelper->salt(Users\Model\UserModel::SALT_LENGTH);
                    $newPassword = $this->secureHelper->generateSaltedPassword($salt, $formData['new_pwd'], 'sha512');
                    $formData['pwd'] = $newPassword;
                    $formData['pwd_salt'] = $salt;
                }

                $bool = $this->usersModel->save($formData, $this->user->getUserId());

                $user = $this->usersModel->getOneById($this->user->getUserId());
                $cookie = $this->authenticationModel->setRememberMeCookie(
                    $this->user->getUserId(),
                    $user['remember_me_token']
                );
                $this->response->headers->setCookie($cookie);

                return $this->redirectMessages()->setMessage(
                    $bool,
                    $this->translator->t('system', $bool !== false ? 'edit_success' : 'edit_error')
                );
            }
        );
    }
}
