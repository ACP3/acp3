<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Polls\Model\Repository;

use ACP3\Core;

/**
 * Class PollRepository
 * @package ACP3\Modules\ACP3\Polls\Model\Repository
 */
class PollRepository extends Core\Model\Repository\AbstractRepository
{
    use Core\Model\Repository\PublicationPeriodAwareTrait;

    const TABLE_NAME = 'polls';

    /**
     * @param int    $pollId
     * @param string $time
     * @param bool   $multiple
     *
     * @return bool
     */
    public function pollExists($pollId, $time = '', $multiple = false)
    {
        $where = !empty($time) ? ' AND ' . $this->getPublicationPeriod() : '';
        $multiple = ($multiple === true) ? ' AND multiple = :multiple' : '';
        $query = 'SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE id = :id' . $where . $multiple;
        return $this->db->fetchColumn($query, ['id' => $pollId, 'time' => $time, 'multiple' => 1]) > 0;
    }

    /**
     * @param int $pollId
     *
     * @return array
     */
    public function getOneByIdWithTotalVotes($pollId)
    {
        return $this->db->fetchAssoc(
            'SELECT p.*, COUNT(pv.poll_id) AS total_votes FROM ' . $this->getTableName() . ' AS p LEFT JOIN ' . $this->getTableName(VoteRepository::TABLE_NAME) . ' AS pv ON(p.id = pv.poll_id) WHERE p.id = ?',
            [$pollId]
        );
    }

    /**
     * @param string $status
     *
     * @return array
     */
    public function countAll($status = '')
    {
        return $this->getAll($status);
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
        $where = empty($time) === false ? ' WHERE p.start <= :time' : '';
        $limitStmt = $this->buildLimitStmt($limitStart, $resultsPerPage);
        return $this->db->fetchAll(
            'SELECT p.id, p.start, p.end, p.title, COUNT(pv.poll_id) AS votes FROM ' . $this->getTableName() . ' AS p LEFT JOIN ' . $this->getTableName(VoteRepository::TABLE_NAME) . ' AS pv ON(p.id = pv.poll_id)' . $where . ' GROUP BY p.id ORDER BY p.start DESC, p.end DESC, p.id DESC' . $limitStmt,
            ['time' => $time]
        );
    }

    /**
     * @param string $time
     *
     * @return array
     */
    public function getLatestPoll($time)
    {
        $period = $this->getPublicationPeriod('p.');
        return $this->db->fetchAssoc(
            'SELECT p.id, p.title, p.multiple, COUNT(pv.poll_id) AS total_votes FROM ' . $this->getTableName() . ' AS p LEFT JOIN ' . $this->getTableName(VoteRepository::TABLE_NAME) . ' AS pv ON(p.id = pv.poll_id) WHERE ' . $period . ' GROUP BY p.id ORDER BY p.start DESC LIMIT 1',
            ['time' => $time]
        );
    }
}
