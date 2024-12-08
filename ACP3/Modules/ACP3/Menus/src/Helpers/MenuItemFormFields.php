<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Helpers;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus\Repository\MenuRepository;

class MenuItemFormFields
{
    public function __construct(private readonly Core\Helpers\Forms $formsHelper, private readonly MenuItemsList $menusHelper, private readonly MenuRepository $menuRepository)
    {
    }

    /**
     * Gibt alle Menüleisten zur Benutzung in einem Dropdown-Menü aus.
     *
     * @return array<string, mixed>[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function menusDropDown(int $selected = 0): array
    {
        $menus = [];
        foreach ($this->menuRepository->getAllMenus() as $menu) {
            $menus[(int) $menu['id']] = $menu['title'];
        }

        return $this->formsHelper->choicesGenerator('block_id', $menus, $selected);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createMenuItemFormFields(
        int $blockId = 0,
        int $parentId = 0,
        int $leftId = 0,
        int $rightId = 0,
        int $displayMenuItem = 1,
    ): array {
        return [
            'blocks' => $this->menusDropDown($blockId),
            'display' => $this->formsHelper->yesNoCheckboxGenerator('display', $displayMenuItem),
            'menuItems' => $this->menusHelper->menuItemsList($parentId, $leftId, $rightId),
        ];
    }
}
