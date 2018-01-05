<?php
namespace ACP3\Modules\ACP3\Permissions\Validation;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions\Validation\ValidationRules\PrivilegesExistValidationRule;
use ACP3\Modules\ACP3\Permissions\Validation\ValidationRules\RoleNotExistsValidationRule;

class RoleFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var int
     */
    protected $roleId = 0;

    /**
     * @param int $roleId
     *
     * @return $this
     */
    public function setRoleId($roleId)
    {
        $this->roleId = (int)$roleId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'name',
                    'message' => $this->translator->t('system', 'name_to_short'),
                ]
            )
            ->addConstraint(
                RoleNotExistsValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'name',
                    'message' => $this->translator->t('permissions', 'role_already_exists'),
                    'extra' => [
                        'role_id' => $this->roleId,
                    ],
                ]
            )
            ->addConstraint(
                PrivilegesExistValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'privileges',
                    'message' => $this->translator->t('permissions', 'invalid_privileges'),
                ]
            );

        $this->validator->validate();
    }
}
