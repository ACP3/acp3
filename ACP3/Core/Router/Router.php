<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Router;

use ACP3\Core\Config;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Http\RequestInterface;
use ACP3\Modules\ACP3\System;

/**
 * Class Router
 * @package ACP3\Core\Router
 */
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
     * @var \ACP3\Core\Config
     */
    protected $config;
    /**
     * @var string
     */
    protected $environment;

    /**
     * Router constructor.
     *
     * @param \ACP3\Core\Http\RequestInterface       $request
     * @param \ACP3\Core\Environment\ApplicationPath $appPath
     * @param \ACP3\Core\Config                      $config
     * @param string                                 $environment
     */
    public function __construct(
        RequestInterface $request,
        ApplicationPath $appPath,
        Config $config,
        $environment
    ) {
        $this->request = $request;
        $this->appPath = $appPath;
        $this->config = $config;
        $this->environment = $environment;
    }

    /**
     * @inheritdoc
     */
    public function route($path, $isAbsolute = false, $forceSecure = false)
    {
        if ($path !== '') {
            $path = $this->preparePath($path);

            if ($this->isAdminUri($path) === false) {
                $path .= (!preg_match('/\/$/', $path) ? '/' : '');
            }
        }

        return $this->addUriPrefix($path, $isAbsolute, $forceSecure) . $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function preparePath($path)
    {
        $path = $path . (!preg_match('/\/$/', $path) ? '/' : '');
        if ($path === 'acp/') {
            $path = 'acp/acp/index/index/';
        }

        $prefix = 'admin/';
        if (substr($path, 0, strlen($prefix)) == $prefix) {
            $path = 'acp/' . substr($path, strlen($prefix));
        }

        return $this->addControllerAndAction($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function addControllerAndAction($path)
    {
        $pathArray = preg_split('=/=', $path, -1, PREG_SPLIT_NO_EMPTY);
        $indexes = ($this->isAdminUri($path) === true) ? [2, 3] : [1, 2];

        foreach ($indexes as $index) {
            if (isset($pathArray[$index]) === false) {
                $path .= 'index/';
            }
        }

        return $path;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function isAdminUri($path)
    {
        return preg_match(self::ADMIN_PANEL_PATTERN, $path) != false;
    }

    /**
     * @param string $path
     * @param bool   $isAbsolute
     * @param bool   $forceSecure
     *
     * @return string
     */
    protected function addUriPrefix($path, $isAbsolute, $forceSecure)
    {
        $prefix = '';
        if ($isAbsolute === true || $forceSecure === true) {
            $prefix .= ($forceSecure === true) ? 'https://' : $this->request->getScheme() . '://';
            $prefix .= $this->request->getHost();
        }

        $prefix .= $this->useModRewrite($path) ? $this->appPath->getWebRoot() : $this->appPath->getPhpSelf() . '/';

        return $prefix;
    }

    /**
     * Check, whether to use urls with mod_rewrite or not
     *
     * @param string $path
     *
     * @return bool
     */
    protected function useModRewrite($path)
    {
        return (bool)$this->config->getSettings(System\Installer\Schema::MODULE_NAME)['mod_rewrite'] === true &&
        $this->isAdminUri($path) === false;
    }
}
