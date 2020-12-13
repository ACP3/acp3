<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets\Dto;

final class LibraryDto
{
    /**
     * @var string
     */
    private $libraryIdentifier;
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var bool
     */
    private $enabledForAjax;
    /**
     * @var array
     */
    private $dependencies;
    /**
     * @var array
     */
    private $css;
    /**
     * @var array
     */
    private $js;
    /**
     * @var string|null
     */
    private $moduleName;

    public function __construct(
        string $libraryIdentifier,
        bool $enabled = false,
        bool $enabledForAjax = true,
        array $dependencies = [],
        array $css = [],
        array $js = [],
        ?string $moduleName = null
    ) {
        $this->libraryIdentifier = $libraryIdentifier;
        $this->enabled = $enabled;
        $this->enabledForAjax = $enabledForAjax;
        $this->dependencies = $dependencies;
        $this->css = $css;
        $this->js = $js;
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

    public function enable(): LibraryDto
    {
        $libraryDto = clone $this;
        $libraryDto->enabled = true;

        return $libraryDto;
    }

    public function isEnabledForAjax(): bool
    {
        return $this->enabledForAjax;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function getCss(): array
    {
        return $this->css;
    }

    public function getJs(): array
    {
        return $this->js;
    }

    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }
}
