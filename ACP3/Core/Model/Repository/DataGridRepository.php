<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model\Repository;

use ACP3\Core\DataGrid\ColumnPriorityQueue;
use Doctrine\DBAL\Query\QueryBuilder;

class DataGridRepository extends AbstractRepository
{
    /**
     * @param \ACP3\Core\DataGrid\ColumnPriorityQueue $columns
     *
     * @return array
     */
    public function getAll(ColumnPriorityQueue $columns)
    {
        $queryBuilder = $this->db->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select($this->getColumns($columns))
            ->from($this->getTableName(), 'main')
            ->setParameters($this->getParameters());

        $this->addJoin($queryBuilder);
        $this->addWhere($queryBuilder);
        $this->addGroupBy($queryBuilder);
        $this->setOrderBy($columns, $queryBuilder);

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * @param \ACP3\Core\DataGrid\ColumnPriorityQueue $gridColumns
     *
     * @return array
     */
    protected function getColumns(ColumnPriorityQueue $gridColumns)
    {
        $columnsToSelect = [];
        foreach (clone $gridColumns as $column) {
            if (!empty($column['fields'])) {
                if (!\is_array($column['fields'])) {
                    $column['fields'] = [$column['fields']];
                }

                foreach ($column['fields'] as $field) {
                    $columnsToSelect[] = $field;
                }
            }
        }

        return $columnsToSelect;
    }

    /**
     * @return string
     */
    protected function getFrom()
    {
        return $this->getTableName();
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    protected function addJoin(QueryBuilder $queryBuilder)
    {
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    protected function addWhere(QueryBuilder $queryBuilder)
    {
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     */
    protected function addGroupBy(QueryBuilder $queryBuilder)
    {
    }

    /**
     * @param \ACP3\Core\DataGrid\ColumnPriorityQueue $gridColumns
     * @param \Doctrine\DBAL\Query\QueryBuilder               $queryBuilder
     */
    protected function setOrderBy(ColumnPriorityQueue $gridColumns, QueryBuilder $queryBuilder)
    {
        foreach (clone $gridColumns as $gridColumn) {
            if ($gridColumn['default_sort'] === true) {
                if (!\is_array($gridColumn['fields'])) {
                    $gridColumn['fields'] = [$gridColumn['fields']];
                }

                foreach ($gridColumn['fields'] as $field) {
                    $queryBuilder->addOrderBy($field, \strtoupper($gridColumn['default_sort_direction']));
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getParameters()
    {
        return [];
    }
}
