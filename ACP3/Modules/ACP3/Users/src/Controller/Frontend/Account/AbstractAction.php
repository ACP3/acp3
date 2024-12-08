<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Account;

use ACP3\Core\Authentication\Exception\UnauthorizedAccessException;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context;

abstract class AbstractAction extends AbstractWidgetAction
{
    public function __construct(
        Context\Context $context,
        private readonly UserModelInterface $user,
    ) {
        parent::__construct($context);
    }

    public function preDispatch(): void
    {
        parent::preDispatch();

        if ($this->user->isAuthenticated() === false) {
            throw new UnauthorizedAccessException(['redirect' => base64_encode($this->request->getPathInfo())]);
        }
    }
}
