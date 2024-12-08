<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly News\ViewProviders\AdminNewsEditViewProvider $adminNewsEditViewProvider,
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
            'active' => 1,
            'category_id' => null,
            'readmore' => 0,
            'id' => null,
            'title' => '',
            'subtitle' => '',
            'target' => null,
            'text' => '',
            'uri' => '',
            'link_title' => '',
            'start' => '',
            'end' => '',
        ];

        return ($this->adminNewsEditViewProvider)($defaults);
    }
}
