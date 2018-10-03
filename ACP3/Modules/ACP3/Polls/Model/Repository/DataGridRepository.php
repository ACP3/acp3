<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Model\Repository;

use ACP3\Core\DataGrid\Model\Repository\AbstractDataGridRepository;

class DataGridRepository extends AbstractDataGridRepository
{
    const TABLE_NAME = PollRepository::TABLE_NAME;
}
