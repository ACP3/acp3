<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Menus\ViewProviders\AdminMenuItemEditViewProvider $adminMenuItemEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array
    {
        $defaults = [
            'title' => '',
            'uri' => '',
            'mode' => '',
            'target' => null,
            'block_id' => 0,
            'parent_id' => 0,
            'left_id' => 0,
            'right_id' => 0,
            'display' => 1,
        ];

        return ($this->adminMenuItemEditViewProvider)($defaults);
    }
}
