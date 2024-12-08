<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Menus\Model\MenuItemsModel $menuItemsModel,
        private readonly Menus\ViewProviders\AdminMenuItemEditViewProvider $adminMenuItemEditViewProvider,
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
        $menuItem = $this->menuItemsModel->getOneById($id);

        if (empty($menuItem) === false) {
            return ($this->adminMenuItemEditViewProvider)($menuItem);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
