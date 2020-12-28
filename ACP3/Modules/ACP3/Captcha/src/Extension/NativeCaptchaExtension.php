<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Extension;

use ACP3\Core;
use ACP3\Core\I18n\Translator;
use ACP3\Modules\ACP3\Users\Model\UserModel;
use Symfony\Component\HttpFoundation\Session\Session;

class NativeCaptchaExtension implements CaptchaExtensionInterface
{
    /**
     * @var Translator
     */
    private $translator;
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    private $secureHelper;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    private $request;
    /**
     * @var \ACP3\Core\Router\RouterInterface
     */
    private $router;
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $sessionHandler;
    /**
     * @var \ACP3\Core\View
     */
    private $view;
    /**
     * @var \ACP3\Modules\ACP3\Users\Model\UserModel
     */
    private $user;
    /**
     * @var Core\ACL
     */
    private $acl;

    public function __construct(
        Core\ACL $acl,
        Translator $translator,
        Core\Http\RequestInterface $request,
        Core\Router\RouterInterface $router,
        Session $sessionHandler,
        Core\View $view,
        Core\Helpers\Secure $secureHelper,
        UserModel $user
    ) {
        $this->translator = $translator;
        $this->request = $request;
        $this->router = $router;
        $this->sessionHandler = $sessionHandler;
        $this->view = $view;
        $this->secureHelper = $secureHelper;
        $this->user = $user;
        $this->acl = $acl;
    }

    public function getCaptchaName(): string
    {
        return $this->translator->t('captcha', 'native');
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptcha(
        int $captchaLength = self::CAPTCHA_DEFAULT_LENGTH,
        string $formFieldId = self::CAPTCHA_DEFAULT_INPUT_ID,
        bool $inputOnly = false,
        string $path = ''
    ): string {
        if (!$this->user->isAuthenticated() && $this->hasCaptchaAccess()) {
            $path = \sha1($this->router->route(empty($path) === true ? $this->request->getQuery() : $path));

            $this->sessionHandler->set('captcha_' . $path, $this->secureHelper->salt($captchaLength));

            $this->view->assign('captcha', [
                'width' => $captchaLength * 25,
                'id' => $formFieldId,
                'height' => 30,
                'input_only' => $inputOnly,
                'path' => $path,
            ]);

            return $this->view->fetchTemplate('Captcha/Partials/captcha_native.tpl');
        }

        return '';
    }

    /**
     * @return bool
     */
    private function hasCaptchaAccess()
    {
        return $this->acl->hasPermission('frontend/captcha/index/image') === true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCaptchaValid($formData, string $formFieldName, array $extra = []): bool
    {
        if (!$this->hasCaptchaAccess()) {
            return true;
        }

        if (!isset($formData[$formFieldName])) {
            return false;
        }

        $value = $formData[$formFieldName];
        $routePath = empty($extra['path']) === true ? $this->request->getQuery() : $extra['path'];
        $indexName = 'captcha_' . \sha1($this->router->route($routePath));

        return \preg_match('/^[a-zA-Z0-9]+$/', $value)
            && \strtolower($value) === \strtolower($this->sessionHandler->get($indexName, ''));
    }
}