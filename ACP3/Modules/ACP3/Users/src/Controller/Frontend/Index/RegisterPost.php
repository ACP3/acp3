<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Permissions;
use ACP3\Modules\ACP3\Users;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class RegisterPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly FormAction $actionHelper,
        private readonly Core\Helpers\Alerts $alertsHelper,
        private readonly Users\Model\UsersModel $usersModel,
        private readonly Users\Validation\RegistrationFormValidation $registrationFormValidation,
        private readonly Permissions\Helpers $permissionsHelpers,
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

                $this->registrationFormValidation->validate($formData);

                $insertValues = [
                    'nickname' => $formData['nickname'],
                    'pwd' => $formData['pwd'],
                    'mail' => $formData['mail'],
                ];

                $lastId = $this->usersModel->save($insertValues);

                $result = $this->permissionsHelpers->updateUserRoles([2], $lastId);

                return $this->alertsHelper->confirmBox(
                    $this->translator->t(
                        'users',
                        $lastId !== false && $result !== false ? 'register_success' : 'register_error'
                    ),
                    $this->applicationPath->getWebRoot()
                );
            },
            $this->request->getFullPath()
        );
    }
}
