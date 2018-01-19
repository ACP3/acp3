<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions;

use ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository;
use ACP3\Modules\ACP3\Permissions\Model\Repository\UserRoleRepository;

class Helpers
{
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository
     */
    protected $roleRepository;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\UserRoleRepository
     */
    protected $userRoleRepository;

    /**
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository     $roleRepository
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\UserRoleRepository $userRoleRepository
     */
    public function __construct(
        RoleRepository $roleRepository,
        UserRoleRepository $userRoleRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->userRoleRepository = $userRoleRepository;
    }

    /**
     * @param array $roles
     * @param       $userId
     *
     * @return bool
     */
    public function updateUserRoles(array $roles, $userId)
    {
        $bool = $this->userRoleRepository->delete($userId, 'user_id');

        $bool2 = false;
        foreach ($roles as $role) {
            $bool2 = $this->userRoleRepository->insert(['user_id' => $userId, 'role_id' => $role]);
        }

        return $bool !== false && $bool2 !== false;
    }
}
