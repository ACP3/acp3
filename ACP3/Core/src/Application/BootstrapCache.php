<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Application;

use ACP3\Core\Application\BootstrapCache\Event\Listener\UserContextListener;
use ACP3\Core\Session\SessionConstants;
use FOS\HttpCache\SymfonyCache\CacheInvalidation;
use FOS\HttpCache\SymfonyCache\CustomTtlListener;
use FOS\HttpCache\SymfonyCache\DebugListener;
use FOS\HttpCache\SymfonyCache\EventDispatchingHttpCache;
use FOS\HttpCache\SymfonyCache\PurgeListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BootstrapCache extends HttpCache implements CacheInvalidation
{
    use EventDispatchingHttpCache;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpKernelInterface $kernel,
        StoreInterface $store,
        SurrogateInterface $surrogate = null,
        array $options = []
    ) {
        parent::__construct($kernel, $store, $surrogate, $options);

        $this->eventDispatcher = $eventDispatcher;

        $this->addSubscriber(new CustomTtlListener());
        $this->addSubscriber(new PurgeListener());
        $this->addSubscriber(new UserContextListener([
            'user_hash_uri' => '/widget/users/index/hash/',
            'session_name_prefix' => SessionConstants::SESSION_NAME,
        ]));
        if (isset($options['debug']) && $options['debug']) {
            $this->addSubscriber(new DebugListener());
        }
    }

    /**
     * Made public to allow event listeners to do refresh operations.
     *
     * {@inheritdoc}
     */
    public function fetch(Request $request, $catch = false)
    {
        return parent::fetch($request, $catch);
    }
}
