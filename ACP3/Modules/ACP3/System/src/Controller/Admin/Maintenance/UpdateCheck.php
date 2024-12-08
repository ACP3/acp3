<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Controller\Admin\Maintenance;

use ACP3\Core;
use ACP3\Core\Controller\Context;
use ACP3\Modules\ACP3\System;

class UpdateCheck extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context\Context $context,
        private readonly System\Helper\UpdateCheck $updateCheck,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $this->updateCheck->checkForNewVersion();

        return [
            'update' => $this->updateCheck->getLatestUpdateCheckInformation(),
        ];
    }
}
