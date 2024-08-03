<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Services;

use Psr\Cache\CacheItemPoolInterface;

class CachingMenuService implements MenuServiceInterface
{
    private const CACHE_KEY_ALL_MENU_ITEMS = 'menu_items';
    private const CACHE_KEY_VISIBLE_MENU_ITEMS = 'menu_items_visible_%s';

    public function __construct(private readonly CacheItemPoolInterface $menusCachePool, private readonly MenuService $menuService)
    {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllMenuItems(): array
    {
        $cacheItem = $this->menusCachePool->getItem(self::CACHE_KEY_ALL_MENU_ITEMS);

        if (!$cacheItem->isHit()) {
            $cacheItem->set($this->menuService->getAllMenuItems());
            $this->menusCachePool->saveDeferred($cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getVisibleMenuItemsByMenu(string $menuIdentifier): array
    {
        $cacheKey = \sprintf(self::CACHE_KEY_VISIBLE_MENU_ITEMS, $menuIdentifier);
        $cacheItem = $this->menusCachePool->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            $cacheItem->set($this->menuService->getVisibleMenuItemsByMenu($menuIdentifier));
            $this->menusCachePool->saveDeferred($cacheItem);
        }

        return $cacheItem->get();
    }
}
