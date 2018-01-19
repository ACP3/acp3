<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Validation\ValidationRules;

use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;
use ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository;

class RoleNotExistsValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository
     */
    protected $roleRepository;

    /**
     * RoleExistsValidationRule constructor.
     *
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return $this->roleRepository->roleExistsByName($data, $extra['role_id'] ?? 0) === false;
    }
}
