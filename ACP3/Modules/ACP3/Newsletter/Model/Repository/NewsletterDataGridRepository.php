<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Model\Repository;

use ACP3\Core\Model\Repository\DataGridRepository;

/**
 * Class NewsletterDataGridRepository
 * @package ACP3\Modules\ACP3\Newsletter\Model\Repository
 */
class NewsletterDataGridRepository extends DataGridRepository
{
    const TABLE_NAME = NewsletterRepository::TABLE_NAME;
}
