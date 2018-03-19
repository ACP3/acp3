<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Router;

use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Router implements RouterInterface
{
    const ADMIN_PANEL_PATTERN = '=^(acp|admin)/=';

    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;
    /**
     * @var \ACP3\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var SettingsInterface
     */
    protected $config;

    /**
     * Router constructor.
     *
     * @param RequestInterface  $request
     * @param ApplicationPath   $appPath
     * @param SettingsInterface $config
     */
    public function __construct(
        RequestInterface $request,
        ApplicationPath $appPath,
        SettingsInterface $config
    ) {
        $this->request = $request;
        $this->appPath = $appPath;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function route($path, $isAbsolute = false, $isSecure = null)
    {
        if ($path !== '') {
            $path = $this->preparePath($path);

            if ($this->isAdminUri($path) === false) {
                $path .= (!\preg_match('/\/$/', $path) ? '/' : '');
            }
        }

        if ($path === '/') {
            $path = '';
        }

        return $this->addUriPrefix($path, $isAbsolute, $isSecure) . $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function preparePath(string $path): string
    {
        $path = $path . (!\preg_match('/\/$/', $path) ? '/' : '');
        if ($path === 'acp/') {
            $path = 'acp/acp/index/index/';
        }

        $prefix = 'admin/';
        if (\substr($path, 0, \strlen($prefix)) == $prefix) {
            $path = 'acp/' . \substr($path, \strlen($prefix));
        }

        return $this->addControllerAndAction($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function addControllerAndAction(string $path): string
    {
        $pathArray = \preg_split('=/=', $path, -1, PREG_SPLIT_NO_EMPTY);
        $indexes = ($this->isAdminUri($path) === true) ? [2, 3] : [1, 2];

        foreach ($indexes as $index) {
            if (isset($pathArray[$index]) === false) {
                $path .= 'index/';
            }
        }

        if ($this->isHomepage($path) === true) {
            $path = '/';
        }

        return $path;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function isAdminUri(string $path): bool
    {
        return \preg_match(self::ADMIN_PANEL_PATTERN, $path) != false;
    }

    /**
     * @param string    $path
     * @param bool      $isAbsolute
     * @param bool|null $isSecure
     *
     * @return string
     */
    protected function addUriPrefix(string $path, bool $isAbsolute, ?bool $isSecure): string
    {
        $prefix = '';
        if ($isAbsolute === true || $isSecure !== null) {
            $prefix .= $this->getScheme($isSecure);
            $prefix .= $this->request->getHost();
        }

        if ($this->useModRewrite($path) || $path === '') {
            $prefix .= $this->appPath->getWebRoot();
        } else {
            $prefix .= $this->appPath->getPhpSelf() . '/';
        }

        return $prefix;
    }

    /**
     * @param bool|null $isSecure
     *
     * @return string
     */
    private function getScheme(?bool $isSecure): string
    {
        if ($isSecure === null) {
            return $this->request->getScheme() . '://';
        } elseif ($isSecure === true) {
            return 'https://';
        }

        return 'http://';
    }

    /**
     * Check, whether to use urls with mod_rewrite or not.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function useModRewrite(string $path): bool
    {
        return (bool) $this->getSystemSettings()['mod_rewrite'] === true
            && $this->isAdminUri($path) === false;
    }

    protected function getSystemSettings(): array
    {
        return $this->config->getSettings(Schema::MODULE_NAME);
    }

    protected function isHomepage(string $path): bool
    {
        return $path === $this->getSystemSettings()['homepage'];
    }
}
