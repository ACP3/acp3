<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Seo;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Seo\ViewProviders\AdminSeoEditViewProvider $adminSeoEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $defaults = [
            'alias' => '',
            'uri' => '',
        ];

        return ($this->adminSeoEditViewProvider)($defaults);
    }
}
