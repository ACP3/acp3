<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Extension;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Core\View;
use ACP3\Modules\ACP3\Captcha\Installer\Schema;
use ReCaptcha\ReCaptcha;

class ReCaptchaCaptchaExtension implements CaptchaExtensionInterface
{
    private const TEMPLATE = 'Captcha/Partials/captcha_recaptcha.tpl';

    private bool $includeJsAssets = true;

    public function __construct(private readonly Translator $translator, private readonly RequestInterface $request, private readonly SettingsInterface $settings, private readonly View $view, private readonly UserModelInterface $user)
    {
    }

    public function getCaptchaName(): string
    {
        return $this->translator->t('captcha', 'recaptcha');
    }

    public function getCaptcha(
        int $captchaLength = self::CAPTCHA_DEFAULT_LENGTH,
        string $formFieldId = self::CAPTCHA_DEFAULT_INPUT_ID,
        array $displayOptions = [],
    ): string {
        if (!$this->user->isAuthenticated()) {
            $settings = $this->settings->getSettings(Schema::MODULE_NAME);

            $this->view->assign(
                'captcha',
                array_merge(
                    [
                        'length' => $captchaLength,
                        'sitekey' => $settings['recaptcha_sitekey'],
                        'includeJsAssets' => $this->includeJsAssets,
                        'id' => $formFieldId,
                        'inputOnly' => false,
                        'floatingLabel' => false,
                    ],
                    $displayOptions
                )
            );

            if ($this->includeJsAssets) {
                $this->includeJsAssets = false;
            }

            return $this->view->fetchTemplate(self::TEMPLATE);
        }

        return '';
    }

    public function isCaptchaValid(mixed $formData, string $formFieldName, array $extra = []): bool
    {
        if (empty($formData['g-recaptcha-response'])) {
            return false;
        }

        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        $recaptcha = new ReCaptcha($settings['recaptcha_secret']);
        $response = $recaptcha->verify(
            $formData['g-recaptcha-response'],
            $this->request->getSymfonyRequest()->getClientIp()
        );

        return $response->isSuccess();
    }
}
