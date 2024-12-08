<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Extension;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\I18n\Translator;
use Symfony\Component\HttpFoundation\Session\Session;

class NativeCaptchaExtension implements CaptchaExtensionInterface
{
    public function __construct(private readonly Core\ACL $acl, private readonly Translator $translator, private readonly Session $sessionHandler, private readonly Core\View $view, private readonly Core\Helpers\Secure $secureHelper, private readonly UserModelInterface $user)
    {
    }

    public function getCaptchaName(): string
    {
        return $this->translator->t('captcha', 'native');
    }

    /**
     * @throws \Exception
     */
    public function getCaptcha(
        int $captchaLength = self::CAPTCHA_DEFAULT_LENGTH,
        string $formFieldId = self::CAPTCHA_DEFAULT_INPUT_ID,
        array $displayOptions = [],
    ): string {
        if (!$this->user->isAuthenticated() && $this->hasCaptchaAccess()) {
            $token = sha1((string) mt_rand());

            $this->sessionHandler->set('captcha_' . $token, $this->secureHelper->salt($captchaLength));

            $this->view->assign(
                'captcha',
                array_merge(
                    [
                        'width' => $captchaLength * 25,
                        'id' => $formFieldId,
                        'height' => 30,
                        'token' => $token,
                        'inputOnly' => false,
                        'floatingLabel' => false,
                    ],
                    $displayOptions
                )
            );

            return $this->view->fetchTemplate('Captcha/Partials/captcha_native.tpl');
        }

        return '';
    }

    private function hasCaptchaAccess(): bool
    {
        return $this->acl->hasPermission('frontend/captcha/index/image') === true;
    }

    public function isCaptchaValid(mixed $formData, string $formFieldName, array $extra = []): bool
    {
        if (!$this->hasCaptchaAccess()) {
            return true;
        }

        if (!isset($formData[$formFieldName])) {
            return false;
        }

        $value = $formData[$formFieldName];
        $indexName = 'captcha_' . $formData['captcha_token'];

        return preg_match('/^[a-zA-Z0-9]+$/', (string) $value)
            && strtolower((string) $value) === strtolower((string) $this->sessionHandler->get($indexName, ''));
    }
}
