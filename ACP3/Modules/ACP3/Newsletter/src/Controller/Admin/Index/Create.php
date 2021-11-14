<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private Newsletter\ViewProviders\AdminNewsletterEditViewProvider $adminNewsletterEditViewProvider
    ) {
        parent::__construct($context);
    }

    public function __invoke(): array
    {
        $defaults = [
            'action' => 1,
            'title' => '',
            'test' => 0,
            'text' => '',
            'date' => '',
        ];

        return ($this->adminNewsletterEditViewProvider)($defaults);
    }
}
