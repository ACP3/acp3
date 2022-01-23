<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Modules\ACP3\Users;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Login extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private ApplicationPath $applicationPath,
        private UserModelInterface $user,
        private Core\Http\RedirectResponse $redirectResponse,
        private Users\ViewProviders\LoginViewProvider $loginViewProvider
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|JsonResponse|RedirectResponse
     */
    public function __invoke(?string $redirect = null): array|JsonResponse|RedirectResponse
    {
        if ($this->user->isAuthenticated() === true) {
            return $this->redirectResponse->toNewPage($this->applicationPath->getWebRoot());
        }

        return ($this->loginViewProvider)($redirect);
    }
}
