<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Accounts;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Newsletter;
use Symfony\Component\HttpFoundation\Response;

class Activate extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Newsletter\Helper\AccountStatus $accountStatusHelper,
    ) {
        parent::__construct($context);
    }

    public function __invoke(int $id): Response
    {
        $result = $this->accountStatusHelper->changeAccountStatus(
            Newsletter\Helper\AccountStatus::ACCOUNT_STATUS_CONFIRMED,
            $id
        );

        return $this->actionHelper->setRedirectMessage(
            $result,
            $this->translator->t('newsletter', $result ? 'activate_success' : 'activate_error')
        );
    }
}
