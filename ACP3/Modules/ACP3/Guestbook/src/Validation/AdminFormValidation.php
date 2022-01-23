<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Validation;

use ACP3\Core;
use ACP3\Core\Validation\AbstractFormValidation;

class AdminFormValidation extends AbstractFormValidation
{
    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * @param array<string, mixed> $settings
     *
     * @return static
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $formData): void
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'message',
                    'message' => $this->translator->t('system', 'message_to_short'),
                ]
            );

        if ($this->settings['notify'] == 2) {
            $this->validator
                ->addConstraint(
                    Core\Validation\ValidationRules\InArrayValidationRule::class,
                    [
                        'data' => $formData,
                        'field' => 'active',
                        'message' => $this->translator->t('guestbook', 'select_activate'),
                        'extra' => [
                            'haystack' => [0, 1],
                        ],
                    ]
                );
        }

        $this->validator->validate();
    }
}
