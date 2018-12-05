<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Model\Repository;

use ACP3\Core\NestedSet\Model\Repository\BlockAwareNestedSetRepositoryInterface;
use ACP3\Core\NestedSet\Model\Repository\NestedSetRepository;

class MenuItemRepository extends NestedSetRepository implements BlockAwareNestedSetRepositoryInterface
{
    const TABLE_NAME = 'menu_items';

    /**
     * @param int $menuItemId
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function menuItemExists(int $menuItemId)
    {
        return (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->getTableName()} WHERE id = :id",
                ['id' => $menuItemId]
            ) > 0;
    }

    /**
     * @param string $uri
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getOneMenuItemByUri(string $uri)
    {
        return $this->db->fetchAssoc(
            "SELECT * FROM {$this->getTableName()} WHERE uri = ?",
            [$uri]
        );
    }

    /**
     * @param int $menuId
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllItemsByBlockId(int $menuId)
    {
        return $this->db->fetchAll(
            "SELECT `id` FROM {$this->getTableName()} WHERE block_id = ?",
            [$menuId]
        );
    }

    /**
     * @param int $menuItemId
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMenuItemUriById(int $menuItemId)
    {
        return $this->db->fetchColumn(
            "SELECT `uri` FROM {$this->getTableName()} WHERE id = ?",
            [$menuItemId]
        );
    }

    /**
     * @param int $menuItemId
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMenuIdByMenuItemId(int $menuItemId)
    {
        return (int) $this->db->fetchColumn(
            "SELECT `block_id` FROM {$this->getTableName()} WHERE id = ?",
            [$menuItemId]
        );
    }

    /**
     * @param string $uri
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMenuItemIdByUri(string $uri)
    {
        return (int) $this->db->fetchColumn(
            "SELECT `id` FROM {$this->getTableName()} WHERE uri = ?",
            [$uri]
        );
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllMenuItems()
    {
        return $this->db->fetchAll(
            "SELECT n.*, COUNT(*)-1 AS level, ROUND((n.right_id - n.left_id - 1) / 2) AS children FROM {$this->getTableName()} AS p, {$this->getTableName()} AS n WHERE n.left_id BETWEEN p.left_id AND p.right_id GROUP BY n.left_id, n.id, n.mode, n.block_id, n.root_id, n.parent_id, n.right_id, n.display, n.title, n.uri, n.target ORDER BY n.left_id"
        );
    }

    /**
     * @param string $blockName
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getVisibleMenuItemsByBlockName(string $blockName)
    {
        return $this->db->fetchAll(
            "SELECT n.*, COUNT(*)-1 AS level, ROUND((n.right_id - n.left_id - 1) / 2) AS children, b.title AS block_title, b.index_name AS block_name FROM {$this->getTableName()} AS p, {$this->getTableName()} AS n JOIN {$this->getTableName(MenuRepository::TABLE_NAME)} AS b ON(n.block_id = b.id) WHERE b.index_name = ? AND n.display = 1 AND n.left_id BETWEEN p.left_id AND p.right_id GROUP BY n.left_id, n.id, n.mode, n.block_id, n.root_id, n.parent_id, n.right_id, n.display, n.title, n.uri, n.target, b.title, b.index_name ORDER BY n.left_id",
            [$blockName]
        );
    }

    /**
     * @param string $menuName
     * @param array  $uris
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLeftIdByUris(string $menuName, array $uris)
    {
        return (int) $this->db->executeQuery(
            "SELECT m.left_id FROM {$this->getTableName()} AS m JOIN {$this->getTableName(MenuRepository::TABLE_NAME)} AS b ON(m.block_id = b.id) WHERE b.index_name = ? AND m.uri IN(?) ORDER BY LENGTH(m.uri) DESC",
            [$menuName, \array_unique($uris)],
            [\PDO::PARAM_STR, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetch(
            \PDO::FETCH_COLUMN
        );
    }

    /**
     * @param array $uris
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMenuItemsByUri(array $uris)
    {
        return $this->db->executeQuery(
            "SELECT p.title, p.uri, p.left_id, p.right_id FROM {$this->getTableName()} AS c, {$this->getTableName()} AS p WHERE c.left_id BETWEEN p.left_id AND p.right_id AND c.uri IN(?) GROUP BY p.uri, p.title, p.left_id, p.right_id ORDER BY p.left_id ASC",
            [\array_unique($uris)],
            [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchAllSortedByBlock(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->getTableName()} ORDER BY `block_id` ASC");
    }
}
