<?php
namespace ACP3\Modules\ACP3\Menus\Helpers;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus\Cache\MenusCacheStorage;

class MenuItemsList
{
    const ARTICLES_URL_KEY_REGEX = '/^(articles\/index\/details\/id_([0-9]+)\/)$/';

    /**
     * @var array
     */
    protected $menuItems = [];
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Cache\MenusCacheStorage
     */
    protected $menusCache;

    /**
     * @param \ACP3\Core\Helpers\Forms       $formsHelper
     * @param \ACP3\Modules\ACP3\Menus\Cache\MenusCacheStorage $menusCache
     */
    public function __construct(
        Core\Helpers\Forms $formsHelper,
        MenusCacheStorage $menusCache
    ) {
        $this->formsHelper = $formsHelper;
        $this->menusCache = $menusCache;
    }

    /**
     * List all available menu items
     *
     * @param integer $parentId
     * @param integer $leftId
     * @param integer $rightId
     *
     * @return array
     */
    public function menuItemsList($parentId = 0, $leftId = 0, $rightId = 0)
    {
        // Menüpunkte einbinden
        if (empty($this->menuItems)) {
            $this->menuItems = $this->menusCache->getMenusCache();
        }

        $output = [];

        if (count($this->menuItems) > 0) {
            foreach ($this->menuItems as $row) {
                if (!($row['left_id'] >= $leftId && $row['right_id'] <= $rightId)) {
                    $row['selected'] = $this->formsHelper->selectEntry('parent_id', $row['id'], $parentId);
                    $row['spaces'] = str_repeat('&nbsp;&nbsp;', $row['level']);

                    // Titel für den aktuellen Block setzen
                    $output[$row['block_name']]['title'] = $row['block_title'];
                    $output[$row['block_name']]['menu_id'] = $row['block_id'];
                    $output[$row['block_name']]['items'][] = $row;
                }
            }
        }
        return $output;
    }
}
