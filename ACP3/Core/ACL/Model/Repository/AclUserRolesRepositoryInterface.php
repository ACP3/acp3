<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\ACL\Model\Repository;

interface AclUserRolesRepositoryInterface
{
    /**
     * @param int $userId
     *
     * @return array
     */
    public function getRolesByUserId(int $userId);
}
