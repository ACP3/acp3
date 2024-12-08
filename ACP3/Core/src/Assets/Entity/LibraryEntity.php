<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets\Entity;

final class LibraryEntity
{
    private bool $enabled = false;

    private readonly string $moduleName;

    /**
     * @param string[] $dependencies
     * @param string[] $css
     * @param string[] $js
     */
    public function __construct(
        private readonly string $libraryIdentifier,
        private readonly bool $enabledForAjax = true,
        private readonly array $dependencies = [],
        private readonly array $css = [],
        private readonly array $js = [],
        string $moduleName = '',
        private readonly bool $deferrableCss = false,
    ) {
        if (!$moduleName) {
            throw new \InvalidArgumentException('The argument `moduleName` is required!');
        }
        $this->moduleName = $moduleName;
    }

    public function getLibraryIdentifier(): string
    {
        return $this->libraryIdentifier;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): LibraryEntity
    {
        $libraryDto = clone $this;
        $libraryDto->enabled = true;

        return $libraryDto;
    }

    public function isEnabledForAjax(): bool
    {
        return $this->enabledForAjax;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @return string[]
     */
    public function getCss(): array
    {
        return $this->css;
    }

    /**
     * @return string[]
     */
    public function getJs(): array
    {
        return $this->js;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function isDeferrableCss(): bool
    {
        return $this->deferrableCss;
    }
}
