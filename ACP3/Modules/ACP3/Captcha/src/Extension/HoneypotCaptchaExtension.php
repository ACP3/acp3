<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Extension;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\View;

class HoneypotCaptchaExtension implements CaptchaExtensionInterface
{
    public function __construct(private readonly Translator $translator, private readonly View $view, private readonly UserModelInterface $userModel)
    {
    }

    public function getCaptchaName(): string
    {
        return $this->translator->t('captcha', 'honeypot');
    }

    public function getCaptcha(
        int $captchaLength = self::CAPTCHA_DEFAULT_LENGTH,
        string $formFieldId = self::CAPTCHA_DEFAULT_INPUT_ID,
        array $displayOptions = [],
    ): string {
        if (!$this->userModel->isAuthenticated()) {
            $this->view->assign(
                'captcha',
                array_merge(
                    [
                        'id' => $formFieldId,
                        'inputOnly' => false,
                        'floatingLabel' => false,
                    ],
                    $displayOptions
                )
            );

            return $this->view->fetchTemplate('Captcha/Partials/captcha_honeypot.tpl');
        }

        return '';
    }

    public function isCaptchaValid(mixed $formData, string $formFieldName, array $extra = []): bool
    {
        return empty($formData[$formFieldName]);
    }
}
