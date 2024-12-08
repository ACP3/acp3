<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Permissions\Model\AclRoleModel $rolesModel,
        private readonly Permissions\ViewProviders\AdminRoleEditViewProvider $adminRoleEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $role = $this->rolesModel->getOneById($id);

        if (!empty($role)) {
            return ($this->adminRoleEditViewProvider)($role);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
