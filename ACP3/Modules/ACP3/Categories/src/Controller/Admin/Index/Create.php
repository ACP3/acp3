<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Categories;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly Categories\ViewProviders\AdminCategoryEditViewProvider $adminCategoryEditViewProvider,
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
        return ($this->adminCategoryEditViewProvider)($this->getDefaultFormData());
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultFormData(): array
    {
        return [
            'parent_id' => 0,
            'title' => '',
            'description' => '',
        ];
    }
}
