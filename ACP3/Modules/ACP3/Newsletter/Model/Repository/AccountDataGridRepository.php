<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Model\Repository;

use ACP3\Core\Model\Repository\DataGridRepository;
use ACP3\Modules\ACP3\Newsletter\Helper\AccountStatus;
use Doctrine\DBAL\Query\QueryBuilder;

class AccountDataGridRepository extends DataGridRepository
{
    const TABLE_NAME = AccountRepository::TABLE_NAME;

    /**
     * {@inheritdoc}
     */
    protected function addWhere(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where('`main`.`status` != :status');
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameters()
    {
        return ['status' => AccountStatus::ACCOUNT_STATUS_DISABLED];
    }
}
