<?php
namespace ACP3\Modules\ACP3\Users\Validation;

use ACP3\Core;
use ACP3\Core\Validation\AbstractFormValidation;

class AdminSettingsFormValidation extends AbstractFormValidation
{

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\EmailValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('system', 'wrong_email_format')
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\InArrayValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'enable_registration',
                    'message' => $this->translator->t('users', 'select_enable_registration'),
                    'extra' => [
                        'haystack' => [0, 1]
                    ]
                ]
            );

        $this->validator->validate();
    }
}
