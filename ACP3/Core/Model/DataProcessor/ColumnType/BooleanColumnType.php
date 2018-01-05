<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Model\DataProcessor\ColumnType;

class BooleanColumnType implements ColumnTypeStrategyInterface
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function doEscape($value)
    {
        return (bool)$value;
    }
}
