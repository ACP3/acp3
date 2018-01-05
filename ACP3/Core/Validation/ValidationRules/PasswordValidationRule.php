<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

class PasswordValidationRule extends AbstractValidationRule
{
    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \is_array($field)) {
            $password = \reset($field);
            $passwordConfirmation = \next($field);

            if ($password !== false && $passwordConfirmation !== false) {
                return $this->checkPassword($data[$password], $data[$passwordConfirmation]);
            }
        }

        return false;
    }

    /**
     * @param string $password
     * @param string $passwordConfirmation
     *
     * @return bool
     */
    protected function checkPassword($password, $passwordConfirmation)
    {
        return !empty($password) && $password === $passwordConfirmation;
    }
}
