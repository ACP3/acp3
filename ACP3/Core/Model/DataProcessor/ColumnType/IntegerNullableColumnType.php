<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model\DataProcessor\ColumnType;

class IntegerNullableColumnType extends IntegerColumnType
{
    /**
     * @param mixed $value
     *
     * @return int|null
     */
    public function doEscape($value)
    {
        if ($value !== null) {
            $value = parent::doEscape($value);
        }

        return $value;
    }
}
