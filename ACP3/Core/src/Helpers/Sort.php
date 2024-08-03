<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

use ACP3\Core\Database\Connection;
use Doctrine\DBAL\Exception;

class Sort
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Moves a database result one step upwards.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function up(string $table, string $idField, string $sortField, int $id, string $where = ''): bool
    {
        return $this->moveOneStep('up', $table, $idField, $sortField, $id, $where);
    }

    /**
     * Moves a database result one step downwards.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    public function down(string $table, string $idField, string $sortField, int $id, string $where = ''): bool
    {
        return $this->moveOneStep('down', $table, $idField, $sortField, $id, $where);
    }

    /**
     * Moves a database result one step upwards/downwards.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws Exception
     */
    private function moveOneStep(string $action, string $table, string $idField, string $sortField, int $id, string $where = ''): bool
    {
        try {
            $this->db->beginTransaction();

            $table = $this->db->getPrefixedTableName($table);

            // Zusätzliche WHERE-Bedingung
            $where = !empty($where) ? 'a.' . $where . ' = b.' . $where . ' AND ' : '';

            // Aktuelles Element und das vorherige Element selektieren
            $query = 'SELECT a.%2$s AS other_id FROM %1$s AS a, %1$s AS b WHERE %4$sb.%2$s = :id AND a.%3$s %5$s b.%3$s ORDER BY a.%3$s %6$s LIMIT 1';

            if ($action === 'up') {
                $otherId = $this->db->getConnection()->fetchOne(\sprintf($query, $table, $idField, $sortField, $where, '<', 'DESC'), ['id' => $id]);
            } else {
                $otherId = $this->db->getConnection()->fetchOne(\sprintf($query, $table, $idField, $sortField, $where, '>', 'ASC'), ['id' => $id]);
            }

            if ($otherId !== null) {
                $swapSortSql = 'UPDATE %1$s t1 INNER JOIN %1$s t2 ON (t1.%2$s, t2.%2$s) IN ((:id, :otherId),(:otherId,:id)) SET t1.%3$s = t2.%3$s';

                $this->db->getConnection()->executeStatement(
                    \sprintf($swapSortSql, $table, $idField, $sortField), ['id' => $id, 'otherId' => $otherId]
                );

                $this->db->commit();

                return true;
            }
        } catch (Exception $e) {
            $this->db->rollBack();

            throw $e;
        }

        return false;
    }
}
