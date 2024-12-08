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
use Symfony\Component\HttpFoundation\Response;

class Login extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly UserModelInterface $user,
        private readonly Core\Http\RedirectResponse $redirectResponse,
        private readonly Users\ViewProviders\LoginViewProvider $loginViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     */
    public function __invoke(?string $redirect = null): array|Response
    {
        if ($this->user->isAuthenticated() === true) {
            return $this->redirectResponse->toNewPage($this->applicationPath->getWebRoot());
        }

        return ($this->loginViewProvider)($redirect);
    }
}
