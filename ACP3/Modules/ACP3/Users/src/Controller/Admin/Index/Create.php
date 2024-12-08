<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Users;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Users\ViewProviders\AdminUserEditViewProvider $adminUserEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $defaults = [
            'nickname' => '',
            'realname' => '',
            'street' => '',
            'house_number' => '',
            'zip' => '',
            'city' => '',
        ];

        return ($this->adminUserEditViewProvider)($defaults);
    }
}
