<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users\ViewProviders\UserMenuViewProvider;
use Symfony\Component\HttpFoundation\Response;

class UserMenu extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly UserModelInterface $user,
        private readonly UserMenuViewProvider $userMenuViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * Displays the user menu, if the user is logged in.
     */
    public function __invoke(): Response
    {
        if ($this->user->isAuthenticated() === true) {
            $response = $this->renderTemplate(null, ($this->userMenuViewProvider)());
        } else {
            $response = new Response('');
        }

        $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        return $response;
    }
}
