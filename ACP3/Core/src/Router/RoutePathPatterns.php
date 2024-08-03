<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Router;

class RoutePathPatterns
{
    /**
     * @var array<string, string>
     */
    private array $routePathPatterns = [];

    public function addRoutePathPattern(string $tableName, string $routePathPattern): void
    {
        if (\array_key_exists($tableName, $this->routePathPatterns)) {
            throw new \RuntimeException(\sprintf('Route-Path-Pattern for table name "%s" already defined.', $tableName));
        }

        $this->routePathPatterns[$tableName] = $routePathPattern;
    }

    public function hasRoutePathPattern(string $tableName): bool
    {
        return \array_key_exists($tableName, $this->routePathPatterns);
    }

    public function getRoutePathPattern(string $tableName): string
    {
        return $this->routePathPatterns[$tableName];
    }
}
