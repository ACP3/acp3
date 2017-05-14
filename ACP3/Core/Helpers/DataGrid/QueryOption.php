<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Helpers\DataGrid;


class QueryOption
{
    /**
     * @var string
     */
    private $tableAlias;
    /**
     * @var string
     */
    private $columnName;
    /**
     * @var string
     */
    private $value;
    /**
     * @var string
     */
    private $operator;

    /**
     * QueryOption constructor.
     * @param string $tableAlias
     * @param string $columnName
     * @param string $value
     * @param string $operator
     */
    public function __construct(string $tableAlias, string $columnName, string $value, string $operator)
    {
        $this->tableAlias = $tableAlias;
        $this->columnName = $columnName;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getTableAlias(): string
    {
        return $this->tableAlias;
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }
}
