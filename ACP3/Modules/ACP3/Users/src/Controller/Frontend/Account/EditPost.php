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

class EditPost extends AbstractAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly UserModelInterface $user,
        private readonly Users\Model\UsersModel $usersModel,
        private readonly Users\Validation\AccountFormValidation $accountFormValidation,
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

                $this->accountFormValidation
                    ->withUserId($this->user->getUserId())
                    ->validate($formData);

                $result = $this->usersModel->save($formData, $this->user->getUserId());

                return $this->actionHelper->setRedirectMessage(
                    $result,
                    $this->translator->t('system', $result ? 'edit_success' : 'edit_error')
                );
            }
        );
    }
}
