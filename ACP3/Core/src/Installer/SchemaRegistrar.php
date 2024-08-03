<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Installer;

use Psr\Container\ContainerInterface;

class SchemaRegistrar implements ContainerInterface
{
    /**
     * @var array<string, SchemaInterface>
     */
    private array $schemas = [];

    public function set(SchemaInterface $schema): void
    {
        $this->schemas[$schema->getModuleName()] = $schema;
    }

    /**
     * @return array<string, SchemaInterface>
     */
    public function all(): array
    {
        return $this->schemas;
    }

    public function has(string $moduleName): bool
    {
        return isset($this->schemas[$moduleName]);
    }

    public function get(string $moduleName): SchemaInterface
    {
        if ($this->has($moduleName)) {
            return $this->schemas[$moduleName];
        }

        throw new \InvalidArgumentException(\sprintf('The schema with the service id "%s" could not be found.', $moduleName));
    }
}
