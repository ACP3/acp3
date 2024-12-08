<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Modules\ACP3\Newsletter;

class Activate extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly Core\Helpers\Alerts $alertsHelper,
        private readonly Newsletter\Helper\AccountStatus $accountStatusHelper,
        private readonly Newsletter\Validation\ActivateAccountFormValidation $activateAccountFormValidation,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Core\Validation\Exceptions\InvalidFormTokenException
     * @throws Core\Validation\Exceptions\ValidationRuleNotFoundException
     */
    public function __invoke(string $hash): string
    {
        try {
            $this->activateAccountFormValidation->validate(['hash' => $hash]);

            $result = $this->accountStatusHelper->changeAccountStatus(
                Newsletter\Helper\AccountStatus::ACCOUNT_STATUS_CONFIRMED,
                ['hash' => $hash]
            );

            return $this->alertsHelper->confirmBox($this->translator->t(
                'newsletter',
                $result ? 'activate_success' : 'activate_error'
            ), $this->applicationPath->getWebRoot());
        } catch (Core\Validation\Exceptions\ValidationFailedException $e) {
            return $this->alertsHelper->errorBox($e->getMessage());
        }
    }
}
