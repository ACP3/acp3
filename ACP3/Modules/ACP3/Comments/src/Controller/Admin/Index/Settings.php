<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Comments\ViewProviders\AdminCommentsSettingsViewProvider;

class Settings extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly AdminCommentsSettingsViewProvider $adminCommentsSettingsViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return ($this->adminCommentsSettingsViewProvider)();
    }
}
