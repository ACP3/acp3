<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Pagination\Exception\InvalidPageException;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users;
use Symfony\Component\HttpFoundation\Response;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Users\ViewProviders\UserListViewProvider $userListViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): Response
    {
        try {
            $response = $this->renderTemplate(null, ($this->userListViewProvider)());
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        } catch (InvalidPageException) {
            throw new ResultNotExistsException();
        }
    }
}
