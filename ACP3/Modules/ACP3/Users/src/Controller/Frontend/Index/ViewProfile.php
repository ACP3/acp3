<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users;

class ViewProfile extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Users\Model\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \ACP3\Modules\ACP3\Users\ViewProviders\UserProfileViewProvider
     */
    private $userProfileViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Users\ViewProviders\UserProfileViewProvider $userProfileViewProvider,
        Users\Model\Repository\UserRepository $userRepository
    ) {
        parent::__construct($context);

        $this->userRepository = $userRepository;
        $this->userProfileViewProvider = $userProfileViewProvider;
    }

    /**
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $id): array
    {
        if ($this->userRepository->resultExists($id) === true) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return ($this->userProfileViewProvider)($id);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}