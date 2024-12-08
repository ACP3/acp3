<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Articles\ViewProviders\AdminArticleEditViewProvider;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly AdminArticleEditViewProvider $adminArticleEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $defaults = [
            'active' => 1,
            'layout' => '',
            'start' => '',
            'end' => '',
            'title' => '',
            'subtitle' => '',
            'text' => '',
        ];

        return ($this->adminArticleEditViewProvider)($defaults);
    }
}
