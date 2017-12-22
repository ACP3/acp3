<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Validation;

use ACP3\Core;
use ACP3\Core\Validation\AbstractFormValidation;
use ACP3\Modules\ACP3\Comments\Validation\ValidationRules\UserNameValidationRule;

/**
 * Class AdminFormValidation
 * @package ACP3\Modules\ACP3\Comments\Validation
 */
class AdminFormValidation extends AbstractFormValidation
{
    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                UserNameValidationRule::class,
                [
                    'data' => $formData,
                    'field' => ['name', 'user_id'],
                    'message' => $this->translator->t('system', 'name_to_short')
                ])
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'message',
                    'message' => $this->translator->t('system', 'message_to_short'),
                ]);

        $this->validator->validate();
    }
}
