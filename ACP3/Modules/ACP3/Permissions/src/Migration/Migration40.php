<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Migration;

use ACP3\Core\Database\Connection;
use ACP3\Core\Migration\MigrationInterface;

class Migration40 implements MigrationInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getSchemaVersion(): int
    {
        return 40;
    }

    public function up(): void
    {
        $this->db->executeQuery("ALTER TABLE {$this->db->getPrefix()}acl_resources DROP FOREIGN KEY {$this->db->getPrefix()}acl_resources_ibfk_1;");
        $this->db->executeQuery("ALTER TABLE {$this->db->getPrefix()}acl_resources DROP COLUMN privilege_id;");
        $this->db->executeQuery("DROP TABLE {$this->db->getPrefix()}acl_rules;");
        $this->db->executeQuery("DROP TABLE {$this->db->getPrefix()}acl_privileges;");
    }

    public function down(): void
    {
    }
}
