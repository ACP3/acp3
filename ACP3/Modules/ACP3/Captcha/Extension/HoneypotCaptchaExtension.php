<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Extension;

use ACP3\Core\I18n\Translator;
use ACP3\Core\View;
use ACP3\Modules\ACP3\Users\Model\UserModel;

class HoneypotCaptchaExtension implements CaptchaExtensionInterface
{
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    private $translator;
    /**
     * @var View
     */
    private $view;
    /**
     * @var UserModel
     */
    private $userModel;

    public function __construct(Translator $translator, View $view, UserModel $userModel)
    {
        $this->translator = $translator;
        $this->view = $view;
        $this->userModel = $userModel;
    }

    /**
     * @return string
     */
    public function getCaptchaName()
    {
        return $this->translator->t('captcha', 'honeypot');
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptcha(
        $captchaLength = self::CAPTCHA_DEFAULT_LENGTH,
        $formFieldId = self::CAPTCHA_DEFAULT_INPUT_ID,
        $inputOnly = false,
        $path = ''
    ) {
        if (!$this->userModel->isAuthenticated()) {
            $this->view->assign('captcha', [
                'id' => $formFieldId,
                'input_only' => $inputOnly,
            ]);

            return $this->view->fetchTemplate('Captcha/Partials/captcha_honeypot.tpl');
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isCaptchaValid($formData, $formFieldName, array $extra = [])
    {
        return empty($formData[$formFieldName]);
    }
}