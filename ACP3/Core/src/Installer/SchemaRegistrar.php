<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Installer;

use ACP3\Core\Modules\Installer\SchemaInterface;

class SchemaRegistrar
{
    /**
     * @var SchemaInterface[]
     */
    private $schemas = [];

    public function set(SchemaInterface $schema)
    {
        $this->schemas[$schema->getModuleName()] = $schema;
    }

    /**
     * @return SchemaInterface[]
     */
    public function all()
    {
        return $this->schemas;
    }

    /**
     * @return bool
     */
    public function has(string $moduleName)
    {
        return isset($this->schemas[$moduleName]);
    }

    /**
     * @return SchemaInterface
     *
     * @throws \InvalidArgumentException
     */
    public function get(string $moduleName)
    {
        if ($this->has($moduleName)) {
            return $this->schemas[$moduleName];
        }

        throw new \InvalidArgumentException(\sprintf('The schema with the service id "%s" could not be found.', $moduleName));
    }
}