<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Categories\Model\Repository;

use ACP3\Core\NestedSet\Model\Repository\BlockAwareNestedSetRepositoryInterface;
use ACP3\Core\NestedSet\Model\Repository\NestedSetRepository;
use ACP3\Modules\ACP3\System\Model\Repository\ModulesRepository;

class CategoriesRepository extends NestedSetRepository implements BlockAwareNestedSetRepositoryInterface
{
    const TABLE_NAME = 'categories';
    const BLOCK_COLUMN_NAME = 'module_id';

    /**
     * @param int $categoryId
     *
     * @return bool
     */
    public function resultExists(int $categoryId)
    {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->getTableName()} WHERE id = ?", [$categoryId]) > 0;
    }

    /**
     * @param string $title
     * @param int    $moduleId
     * @param int    $categoryId
     *
     * @return bool
     */
    public function resultIsDuplicate(string $title, int $moduleId, int $categoryId)
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->getTableName()} WHERE title = ? AND module_id = ? AND id != ?",
                [$title, $moduleId, $categoryId]
        ) > 0;
    }

    /**
     * @param int $categoryId
     *
     * @return string
     */
    public function getTitleById(int $categoryId)
    {
        return $this->db->fetchColumn("SELECT `title` FROM {$this->getTableName()} WHERE id = ?", [$categoryId]);
    }

    /**
     * @param string $moduleName
     *
     * @return array
     */
    public function getAllByModuleName(string $moduleName)
    {
        return $this->db->fetchAll(
            'SELECT c.*, COUNT(*)-1 AS `level`, ROUND((c.right_id - c.left_id - 1) / 2) AS children FROM ' . $this->getTableName() . ' AS main, ' . $this->getTableName() . ' AS c JOIN ' . $this->getTableName(ModulesRepository::TABLE_NAME) . ' AS m ON(m.id = c.module_id) WHERE m.name = ? AND c.left_id BETWEEN main.left_id AND main.right_id GROUP BY c.left_id ORDER BY c.left_id ASC',
            [$moduleName]
        );
    }

    /**
     * @param int $moduleId
     * @return array
     */
    public function getAllByModuleId(int $moduleId)
    {
        return $this->db->fetchAll(
            'SELECT c.*, COUNT(*)-1 AS `level`, ROUND((c.right_id - c.left_id - 1) / 2) AS children FROM ' . $this->getTableName() . ' AS main, ' . $this->getTableName() . ' AS c WHERE c.module_id = ? AND c.left_id BETWEEN main.left_id AND main.right_id GROUP BY c.left_id ORDER BY c.left_id ASC',
            [$moduleId]
        );
    }

    /**
     * @param int $categoryId
     *
     * @return string
     */
    public function getModuleNameFromCategoryId(int $categoryId)
    {
        return $this->db->fetchColumn(
            'SELECT m.name FROM ' . $this->getTableName(ModulesRepository::TABLE_NAME) . ' AS m JOIN ' . $this->getTableName() . ' AS c ON(m.id = c.module_id) WHERE c.id = ?',
            [$categoryId]
        );
    }

    /**
     * @param int $categoryId
     *
     * @return int
     */
    public function getModuleIdByCategoryId(int $categoryId)
    {
        return (int)$this->db->fetchColumn(
            "SELECT `module_id` FROM {$this->getTableName()} WHERE `id` = ?",
            [$categoryId]
        );
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function getCategoryDeleteInfosById(int $categoryId)
    {
        return $this->db->fetchAssoc(
            'SELECT c.picture, m.name AS module FROM ' . $this->getTableName() . ' AS c JOIN ' . $this->getTableName(ModulesRepository::TABLE_NAME) . ' AS m ON(m.id = c.module_id) WHERE c.id = ?',
            [$categoryId]
        );
    }

    /**
     * @param string $title
     * @param string $moduleName
     *
     * @return array
     */
    public function getOneByTitleAndModule(string $title, string $moduleName)
    {
        return $this->db->fetchAssoc(
            'SELECT c.* FROM ' . $this->getTableName() . ' AS c JOIN ' . $this->getTableName(ModulesRepository::TABLE_NAME) . ' AS m ON(m.id = c.module_id) WHERE c.title = ? AND m.name = ?',
            [$title, $moduleName]
        );
    }

    /**
     * @inheritdoc
     */
    public function fetchAllSortedByBlock(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->getTableName()} ORDER BY `module_id` ASC");
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getAllSiblingsAsId(int $categoryId)
    {
        $categoryIds = [];
        foreach ($this->fetchNodeWithSiblings($categoryId) as $category) {
            $categoryIds[] = $category['id'];
        }

        return $categoryIds;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getAllDirectSiblings(int $categoryId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()} WHERE `parent_id` = ?",
            [$categoryId]
        );
    }

    /**
     * @param int $moduleId
     * @return array
     */
    public function getAllRootCategoriesByModuleId(int $moduleId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()} WHERE `module_id` = ? AND `parent_id` = ?",
            [$moduleId, 0]
        );
    }

    /**
     * @param string $moduleName
     * @return array
     */
    public function getAllRootCategoriesByModuleName(string $moduleName)
    {
        return $this->db->fetchAll(
            "SELECT c.* FROM {$this->getTableName()} AS c JOIN {$this->getTableName(ModulesRepository::TABLE_NAME)} AS m ON(m.id = c.module_id) WHERE m.`name` = ? AND c.`parent_id` = ?",
            [$moduleName, 0]
        );
    }
}