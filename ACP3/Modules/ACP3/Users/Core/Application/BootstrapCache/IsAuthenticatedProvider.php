<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Core\Application\BootstrapCache;

use ACP3\Core\ACL;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users\Model\UserModel;
use FOS\HttpCache\UserContext\ContextProvider;
use FOS\HttpCache\UserContext\UserContext;

class IsAuthenticatedProvider implements ContextProvider
{
    /**
     * @var SettingsInterface
     */
    private $settings;
    /**
     * @var UserModel
     */
    private $userModel;
    /**
     * @var ACL
     */
    private $acl;

    /**
     * IsAuthenticatedProvider constructor.
     *
     * @param SettingsInterface $settings
     * @param ACL               $acl
     * @param UserModel         $userModel
     */
    public function __construct(SettingsInterface $settings, ACL $acl, UserModel $userModel)
    {
        $this->settings = $settings;
        $this->userModel = $userModel;
        $this->acl = $acl;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserContext(UserContext $context)
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        $context->addParameter('security_secret', $settings['security_secret']);
        $context->addParameter('authenticated', $this->userModel->isAuthenticated());
        $context->addParameter('user_id', $this->userModel->getUserId());
        $context->addParameter('roles', $this->acl->getUserRoleIds($this->userModel->getUserId()));
    }
}
