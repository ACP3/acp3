<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Router;

use ACP3\Core;
use ACP3\Modules\ACP3\Seo;

/**
 * Class Aliases
 * @package ACP3\Modules\ACP3\Seo\Core\Router
 */
class Aliases
{
    /**
     * @var \ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage
     */
    protected $seoCache;
    /**
     * @var array
     */
    protected $aliasesCache = [];
    /**
     * @var bool
     */
    private $isActive;

    /**
     * @param Core\Modules $modules
     * @param \ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage $seoCache
     */
    public function __construct(
        Core\Modules $modules,
        Seo\Cache\SeoCacheStorage $seoCache)
    {
        $this->seoCache = $seoCache;
        $this->isActive = $modules->isActive(Seo\Installer\Schema::MODULE_NAME);
    }

    /**
     * Returns an uri alias by a given path
     *
     * @param string $path
     * @param bool   $emptyOnNoResult
     *
     * @return string
     */
    public function getUriAlias($path, $emptyOnNoResult = false)
    {
        $path .= !preg_match('/\/$/', $path) ? '/' : '';

        if ($this->isActive === false) {
            return $path;
        }

        if ($this->aliasesCache === []) {
            $this->aliasesCache = $this->seoCache->getCache();
        }

        return !empty($this->aliasesCache[$path]['alias'])
            ? $this->aliasesCache[$path]['alias']
            : ($emptyOnNoResult === true ? '' : $path);
    }

    /**
     * Checks, whether an uri alias exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function uriAliasExists($path)
    {
        return ($this->getUriAlias($path, true) !== '');
    }
}
