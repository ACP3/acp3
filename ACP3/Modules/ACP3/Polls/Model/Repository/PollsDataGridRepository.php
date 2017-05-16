<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Polls\Model\Repository;

use ACP3\Core\Helpers\DataGrid\Model\Repository\AbstractDataGridRepository;

class PollsDataGridRepository extends AbstractDataGridRepository
{
    const TABLE_NAME = PollsRepository::TABLE_NAME;
}
