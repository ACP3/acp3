<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Repository;

use ACP3\Core;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;

class NewsRepository extends Core\Repository\AbstractRepository
{
    use Core\Repository\PublicationPeriodAwareTrait;

    public const TABLE_NAME = 'news';

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function resultExists(int $newsId, string $time = ''): bool
    {
        $period = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';

        return (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE `id` = :id' . $period,
            ['id' => $newsId, 'time' => $time, 'active' => 1]
        ) > 0;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getOneById(int|string $entryId): array
    {
        return $this->db->fetchAssoc(
            'SELECT n.*, c.title AS category_title FROM ' . $this->getTableName() . ' AS n LEFT JOIN ' . $this->getTableName(\ACP3\Modules\ACP3\Categories\Repository\CategoryRepository::TABLE_NAME) . ' AS c ON(n.category_id = c.id) WHERE n.id = ?',
            [$entryId]
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAll(string $time = '', ?int $categoryId = null): int
    {
        if ($categoryId !== null) {
            return $this->countAllByCategoryId([$categoryId], $time);
        }

        $where = empty($time) === false ? ' WHERE ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';

        return (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM ' . $this->getTableName() . $where,
            ['time' => $time, 'active' => 1]
        );
    }

    /**
     * @param int[] $categoryId
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllByCategoryId(array $categoryId, string $time = ''): int
    {
        $where = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';

        return (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM ' . $this->getTableName() . " WHERE `category_id` IN(:categoryId) {$where}",
            ['time' => $time, 'categoryId' => $categoryId, 'active' => 1],
            ['time' => ParameterType::STRING, 'categoryId' => ArrayParameterType::INTEGER, 'active' => ParameterType::INTEGER]
        );
    }

    /**
     * @param int[]|int $categoryId
     *
     * @return array<string, mixed>[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllByCategoryId(
        array|int $categoryId,
        string $time = '',
        ?int $limitStart = null,
        ?int $resultsPerPage = null,
    ): array {
        if (false === \is_array($categoryId)) {
            $categoryId = [$categoryId];
        }

        $where = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);

        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()} WHERE category_id IN(:categoryId) {$where} ORDER BY `start` DESC, `end` DESC, `id` DESC {$limitStmt}",
            ['time' => $time, 'categoryId' => $categoryId, 'active' => 1],
            ['time' => ParameterType::STRING, 'categoryId' => ArrayParameterType::INTEGER, 'active' => ParameterType::INTEGER]
        );
    }

    /**
     * @return array<string, mixed>[]
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAll(string $time = '', ?int $limitStart = null, ?int $resultsPerPage = null): array
    {
        $where = empty($time) === false ? ' WHERE ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);

        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()}{$where} ORDER BY `start` DESC, `end` DESC, `id` DESC" . $limitStmt,
            ['time' => $time, 'active' => 1]
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLatestByCategoryId(int $categoryId, string $time): array
    {
        $period = " AND {$this->getPublicationPeriod()} AND `active` = :active";

        return $this->db->fetchAssoc(
            "SELECT * FROM {$this->getTableName()} WHERE `category_id` = :category_id {$period} ORDER BY `start` DESC LIMIT 1",
            ['category_id' => $categoryId, 'time' => $time, 'active' => 1]
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLatest(string $time): array
    {
        return $this->db->fetchAssoc(
            "SELECT * FROM {$this->getTableName()} WHERE {$this->getPublicationPeriod()} AND `active` = :active ORDER BY `start` DESC LIMIT 1",
            ['time' => $time, 'active' => 1]
        );
    }
}
