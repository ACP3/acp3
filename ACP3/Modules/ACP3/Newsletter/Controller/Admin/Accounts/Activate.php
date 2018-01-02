<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Accounts;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;

/**
 * Class Activate
 * @package ACP3\Modules\ACP3\Newsletter\Controller\Admin\Accounts
 */
class Activate extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Helper\AccountStatus
     */
    protected $accountStatusHelper;

    /**
     * Activate constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext         $context
     * @param \ACP3\Modules\ACP3\Newsletter\Helper\AccountStatus $accountStatusHelper
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Newsletter\Helper\AccountStatus $accountStatusHelper
    ) {
        parent::__construct($context);

        $this->accountStatusHelper = $accountStatusHelper;
    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute(int $id)
    {
        $bool = $this->accountStatusHelper->changeAccountStatus(
            Newsletter\Helper\AccountStatus::ACCOUNT_STATUS_CONFIRMED,
            $id
        );

        return $this->redirectMessages()->setMessage(
            $bool,
            $this->translator->t('newsletter', $bool !== false ? 'activate_success' : 'activate_error')
        );
    }
}
