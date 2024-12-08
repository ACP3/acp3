<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Menus\Model\MenusModel $menusModel,
        private readonly Menus\ViewProviders\AdminMenuEditViewProvider $adminMenuEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $menu = $this->menusModel->getOneById($id);

        if (empty($menu) === false) {
            return ($this->adminMenuEditViewProvider)($menu);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
