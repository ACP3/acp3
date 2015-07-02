<?php
namespace ACP3\Installer\Core;

use ACP3\Core;

/**
 * Class Router
 * @package ACP3\Installer\Core
 */
class Router extends Core\Router
{
    /**
     * @var \ACP3\Installer\Core\Request
     */
    protected $request;

    /**
     * @param \ACP3\Installer\Core\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $path
     * @param bool $absolute
     * @param bool $forceSecure
     * @return string
     */
    public function route($path, $absolute = false, $forceSecure = false)
    {
        $path = $path . (!preg_match('/\/$/', $path) ? '/' : '');
        $pathArray = preg_split('=/=', $path, -1, PREG_SPLIT_NO_EMPTY);

        if (isset($pathArray[1]) === false) {
            $path .= 'index/';
        }
        if (isset($pathArray[2]) === false) {
            $path .= 'index/';
        }

        $prefix = '';
        // Append the current hostname to the URL
        if ($absolute === true) {
            $prefix .= ($forceSecure === true) ? 'https://' : $this->request->getProtocol();
            $prefix .= $this->request->getHostname();
        }

        $prefix .= PHP_SELF . '/';
        return $prefix . $path;
    }
}
