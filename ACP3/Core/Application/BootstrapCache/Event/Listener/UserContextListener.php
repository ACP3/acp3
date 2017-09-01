<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Application\BootstrapCache\Event\Listener;

use ACP3\Modules\ACP3\Users\Model\AuthenticationModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Caching proxy side of the user context handling for the symfony built-in HttpCache.
 *
 * @see \FOS\HttpCache\SymfonyCache\UserContextSubscriber for the original file as we had to override some logic...
 */
class UserContextListener extends \FOS\HttpCache\SymfonyCache\UserContextListener
{
    /**
     * Remove unneeded things from the request for user hash generation.
     *
     * Cleans cookies header to only keep the session identifier cookie and the ACP3 remember me cookie
     *
     * @param Request $hashLookupRequest
     * @param Request $originalRequest
     */
    protected function cleanupHashLookupRequest(Request $hashLookupRequest, Request $originalRequest)
    {
        $authCookie = $originalRequest->cookies->get(AuthenticationModel::AUTH_NAME);
        if ($authCookie !== null) {
            $hashLookupRequest->cookies->set(AuthenticationModel::AUTH_NAME, $authCookie);
        }

        parent::cleanupHashLookupRequest($hashLookupRequest, $originalRequest);
    }
}