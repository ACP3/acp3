<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filemanager\Migration;

use ACP3\Core\Database\Connection;
use ACP3\Core\Migration\MigrationInterface;
use Doctrine\DBAL\Exception;

final class Migration202401062206 implements MigrationInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function up(): void
    {
        $this->db->executeStatement("INSERT INTO `{$this->db->getPrefixedTableName('acl_resources')}` (`module_id`, `area`, `controller`, `page`, `params`) VALUES ((SELECT `id` FROM `{$this->db->getPrefixedTableName('modules')}` WHERE `name` = 'filemanager'), 'admin', 'index', 'index', '');");
    }

    public function down(): void
    {
    }

    public function dependencies(): ?array
    {
        return null;
    }
}
