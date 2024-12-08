<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Router;

use ACP3\Core;
use ACP3\Modules\ACP3\Seo;
use ACP3\Modules\ACP3\Seo\Services\SeoInformationService;

class Aliases
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $aliasesCache = [];

    private readonly bool $isActive;

    public function __construct(
        Core\Modules $modules,
        private readonly SeoInformationService $seoInformationService,
    ) {
        $this->isActive = $modules->isInstalled(Seo\Installer\Schema::MODULE_NAME);
    }

    /**
     * Returns an uri alias by a given path.
     */
    public function getUriAlias(string $path, bool $emptyOnNoResult = false): string
    {
        if ($this->isActive === false) {
            return $path;
        }

        if ($this->aliasesCache === []) {
            $this->aliasesCache = $this->seoInformationService->getAllSeoInformation();
        }

        $path .= (!preg_match('/\/$/', $path) ? '/' : '');
        $pathParts = preg_split('=/=', $path, -1, PREG_SPLIT_NO_EMPTY);
        $routeArguments = [];

        while (\count($pathParts) >= 3) {
            $newPath = implode('/', $pathParts) . '/';
            if (!empty($this->aliasesCache[$newPath]['alias'])) {
                return $this->aliasesCache[$newPath]['alias']
                    . (!empty($routeArguments) ? '/' . implode('/', $routeArguments) : '');
            }

            $routeArguments[] = array_pop($pathParts);
        }

        return $emptyOnNoResult === true ? '' : $path;
    }

    /**
     * Checks, whether an uri alias exists.
     */
    public function uriAliasExists(string $path): bool
    {
        return $this->getUriAlias($path, true) !== '';
    }
}
