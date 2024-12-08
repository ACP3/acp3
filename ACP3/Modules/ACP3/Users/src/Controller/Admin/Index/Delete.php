<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Users;
use Symfony\Component\HttpFoundation\Response;

class Delete extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Users\Model\UsersModel $usersModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(?string $action = null): array|Response
    {
        return $this->actionHelper->handleCustomDeleteAction(
            $action,
            function (array $items) {
                try {
                    $result = $this->usersModel->delete($items);
                    $text = $this->translator->t('system', $result ? 'delete_success' : 'delete_error');
                } catch (Users\Exception\SuperUserNotDeletableException) {
                    $result = false;
                    $text = $this->translator->t('users', 'admin_user_undeletable');
                }

                return $this->actionHelper->setRedirectMessage(
                    $result,
                    $text
                );
            }
        );
    }
}
