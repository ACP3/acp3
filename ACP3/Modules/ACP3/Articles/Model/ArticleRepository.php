<?php
namespace ACP3\Modules\ACP3\Articles\Model;

use ACP3\Core;

/**
 * Class ArticleRepository
 * @package ACP3\Modules\ACP3\Articles\Model
 */
class ArticleRepository extends Core\Model\AbstractRepository
{
    use Core\Model\PublicationPeriodAwareTrait;

    const TABLE_NAME = 'articles';

    /**
     * @param int    $id
     * @param string $time
     *
     * @return bool
     */
    public function resultExists($id, $time = '')
    {
        $period = empty($time) === false ? ' AND ' . $this->getPublicationPeriod() : '';
        return $this->db->fetchColumn("SELECT COUNT(*) FROM {$this->getTableName()} WHERE id = :id{$period}",
            ['id' => $id, 'time' => $time]) > 0;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getOneById($id)
    {
        return $this->db->fetchAssoc("SELECT * FROM {$this->getTableName()} WHERE id = ?", [$id]);
    }

    /**
     * @param string $time
     *
     * @return int
     */
    public function countAll($time = '')
    {
        return count($this->getAll($time));
    }

    /**
     * @param string $time
     * @param string $limitStart
     * @param string $resultsPerPage
     *
     * @return array
     */
    public function getAll($time = '', $limitStart = '', $resultsPerPage = '')
    {
        $where = empty($time) === false ? ' WHERE ' . $this->getPublicationPeriod() : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);
        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()}{$where} ORDER BY `title` ASC{$limitStmt}",
            ['time' => $time]
        );
    }

    /**
     * @param string $time
     * @param string $limitStart
     * @param string $resultsPerPage
     *
     * @return array
     */
    public function getLatest($time = '', $limitStart = '', $resultsPerPage = '')
    {
        $where = empty($time) === false ? ' WHERE ' . $this->getPublicationPeriod() : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);
        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()}{$where} ORDER BY `start` DESC{$limitStmt}",
            ['time' => $time]
        );
    }


    /**
     * @return array
     */
    public function getAllInAcp()
    {
        return $this->db->fetchAll("SELECT * FROM {$this->getTableName()} ORDER BY `title` ASC");
    }

    /**
     * @param string $fields
     * @param string $searchTerm
     * @param string $sortDirection
     * @param string $time
     *
     * @return array
     */
    public function getAllSearchResults($fields, $searchTerm, $sortDirection, $time)
    {
        return $this->db->fetchAll(
            "SELECT `id`, `title`, `text` FROM {$this->getTableName()} WHERE MATCH ({$fields}) AGAINST ({$this->db->getConnection()->quote($searchTerm)} IN BOOLEAN MODE) AND {$this->getPublicationPeriod()} ORDER BY `start` {$sortDirection}, `end` {$sortDirection}, `title` {$sortDirection}",
            ['time' => $time]
        );
    }
}
