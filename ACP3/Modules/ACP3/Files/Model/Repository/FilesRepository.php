<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Model\Repository;

use ACP3\Core;
use ACP3\Core\Database\Connection;
use ACP3\Modules\ACP3\Files\Installer\Schema;

class FilesRepository extends Core\Model\Repository\AbstractRepository
{
    use Core\Model\Repository\PublicationPeriodAwareTrait;

    const TABLE_NAME = 'files';
    /**
     * @var Core\Settings\SettingsInterface
     */
    private $settings;

    /**
     * FilesRepository constructor.
     *
     * @param Connection                      $db
     * @param Core\Settings\SettingsInterface $settings
     */
    public function __construct(Connection $db, Core\Settings\SettingsInterface $settings)
    {
        parent::__construct($db);

        $this->settings = $settings;
    }

    /**
     * @param int    $fileId
     * @param string $time
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function resultExists(int $fileId, string $time = '')
    {
        $period = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';

        return (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->getTableName()} WHERE `id` = :id" . $period,
                ['id' => $fileId, 'time' => $time, 'active' => 1]
            ) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getOneById(int $entryId)
    {
        return $this->db->fetchAssoc(
            'SELECT n.*, c.title AS category_title FROM ' . $this->getTableName() . ' AS n LEFT JOIN ' . $this->getTableName(\ACP3\Modules\ACP3\Categories\Model\Repository\CategoriesRepository::TABLE_NAME) . ' AS c ON(n.category_id = c.id) WHERE n.id = ?',
            [$entryId]
        );
    }

    /**
     * @param int $fileId
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getFileById(int $fileId)
    {
        return $this->db->fetchColumn("SELECT `file` FROM {$this->getTableName()} WHERE `id` = ?", [$fileId]);
    }

    /**
     * @param string   $time
     * @param int|null $categoryId
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countAll(string $time = '', int $categoryId = null)
    {
        if ($categoryId !== null) {
            $results = $this->getAllByCategoryId($categoryId, $time);
        } else {
            $results = $this->getAll($time);
        }

        return \count($results);
    }

    /**
     * @param int      $categoryId
     * @param string   $time
     * @param int|null $limitStart
     * @param int|null $resultsPerPage
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllByCategoryId(int $categoryId, string $time = '', ?int $limitStart = null, ?int $resultsPerPage = null)
    {
        $where = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);

        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()} WHERE `category_id` = :categoryId {$where} ORDER BY {$this->getOrderBy()}{$limitStmt}",
            ['time' => $time, 'active' => 1, 'categoryId' => $categoryId]
        );
    }

    /**
     * @param string   $time
     * @param int|null $limitStart
     * @param int|null $resultsPerPage
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAll(string $time = '', ?int $limitStart = null, ?int $resultsPerPage = null)
    {
        $where = empty($time) === false ? ' WHERE ' . $this->getPublicationPeriod() . ' AND `active` = :active' : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);

        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()}{$where} ORDER BY {$this->getOrderBy()}{$limitStmt}",
            ['time' => $time, 'active' => 1]
        );
    }

    /**
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMaxSort()
    {
        return (int) $this->db->fetchColumn("SELECT MAX(`sort`) FROM {$this->getTableName()};");
    }

    /**
     * @return string
     */
    private function getOrderBy()
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        $orderByMap = [
            'date' => '`start` DESC, `end` DESC, `id` DESC',
            'custom' => '`sort` ASC',
        ];

        if (isset($settings['order_by']) && isset($orderByMap[$settings['order_by']])) {
            return $orderByMap[$settings['order_by']];
        }

        return $orderByMap['date'];
    }
}
