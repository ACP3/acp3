<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users;
use Symfony\Component\HttpFoundation\Response;

class ViewProfile extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Users\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \ACP3\Modules\ACP3\Users\ViewProviders\UserProfileViewProvider
     */
    private $userProfileViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Users\ViewProviders\UserProfileViewProvider $userProfileViewProvider,
        Users\Repository\UserRepository $userRepository
    ) {
        parent::__construct($context);

        $this->userRepository = $userRepository;
        $this->userProfileViewProvider = $userProfileViewProvider;
    }

    /**
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function execute(int $id): Response
    {
        if ($this->userRepository->resultExists($id) === true) {
            $response = $this->renderTemplate(null, ($this->userProfileViewProvider)($id));
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
