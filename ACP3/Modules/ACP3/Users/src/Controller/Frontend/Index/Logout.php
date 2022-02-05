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
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class Logout extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private ApplicationPath $applicationPath,
        private UserModelInterface $user,
        private Core\Http\RedirectResponse $redirectResponse,
        private Users\ViewProviders\LogoutViewProvider $logoutViewProvider,
        private Users\Model\AuthenticationModel $authenticationModel
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     *
     * @throws Exception
     */
    public function __invoke(): array|Response
    {
        if (!$this->user->isAuthenticated()) {
            return $this->redirectResponse->toNewPage($this->applicationPath->getWebRoot());
        }

        $this->authenticationModel->logout();

        return ($this->logoutViewProvider)();
    }
}
