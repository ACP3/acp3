<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid;

/** @extends \SplPriorityQueue<mixed, array<string, mixed>> */
class ColumnPriorityQueue extends \SplPriorityQueue
{
    private int $serial = PHP_INT_MAX;

    /**
     * @see http://php.net/manual/en/splpriorityqueue.compare.php#93999
     */
    #[\ReturnTypeWillChange]
    public function insert($value, $priority): bool
    {
        return parent::insert($value, [$priority, $this->serial--]);
    }
}
