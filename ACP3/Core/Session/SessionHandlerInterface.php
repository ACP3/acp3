<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Session;

interface SessionHandlerInterface extends \SessionHandlerInterface
{
    const SESSION_NAME = 'ACP3_SID';
    const XSRF_TOKEN_NAME = 'security_token';

    /**
     * @param string     $key
     *
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value);

    /**
     * @param string $key
     *
     * @return $this
     */
    public function remove($key);

    /**
     * Secures the current session to prevent from session fixations
     *
     * @return void
     */
    public function secureSession();
}
