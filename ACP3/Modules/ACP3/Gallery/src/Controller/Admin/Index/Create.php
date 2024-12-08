<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Gallery\ViewProviders\AdminGalleryEditViewProvider $adminGalleryEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $defaults = [
            'id' => null,
            'active' => 1,
            'title' => '',
            'subtitle' => '',
            'description' => '',
            'start' => '',
            'end' => '',
        ];

        return ($this->adminGalleryEditViewProvider)($defaults);
    }
}
