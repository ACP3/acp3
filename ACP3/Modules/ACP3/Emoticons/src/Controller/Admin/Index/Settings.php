<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Emoticons\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Emoticons\ViewProviders\AdminSettingsViewProvider;

class Settings extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly AdminSettingsViewProvider $adminSettingsViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return ($this->adminSettingsViewProvider)();
    }
}
