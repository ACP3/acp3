<?php
namespace ACP3\Modules\ACP3\Users\Validation\ValidationRules;

class AccountNotExistsByEmailValidationRule extends AbstractAccountNotExistsValidationRule
{
    /**
     * @inheritdoc
     */
    protected function accountExists($data, $userId)
    {
        return $this->userRepository->resultExistsByEmail($data, $userId) === false;
    }
}
