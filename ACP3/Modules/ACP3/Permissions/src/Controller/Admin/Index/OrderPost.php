<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Modules\ACP3\Permissions\Model\AclRoleModel;
use ACP3\Modules\ACP3\Permissions\Repository\AclRoleRepository;
use Symfony\Component\HttpFoundation\Response;

class OrderPost extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly RedirectResponse $redirectResponse,
        private readonly AclRoleRepository $roleRepository,
        private readonly AclRoleModel $rolesModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, string $action): Response
    {
        if (($action === 'up' || $action === 'down') && $this->roleRepository->roleExists($id) === true) {
            if ($action === 'up') {
                $this->rolesModel->moveUp($id);
            } else {
                $this->rolesModel->moveDown($id);
            }

            return $this->redirectResponse->temporary('acp/permissions');
        }

        throw new ResultNotExistsException();
    }
}
