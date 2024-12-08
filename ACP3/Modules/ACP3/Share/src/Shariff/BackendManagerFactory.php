<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Shariff;

use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Share\Helpers\SocialServices;
use ACP3\Modules\ACP3\Share\Installer\Schema;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class BackendManagerFactory
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly SettingsInterface $settings,
        private readonly LoggerInterface $logger,
        private readonly ClientInterface $client,
        private readonly CacheItemPoolInterface $servicesCacheItemPool,
        private readonly SocialSharingBackendServiceLocator $serviceLocator,
        private readonly SocialServices $socialServices,
    ) {
    }

    public function create(): BackendManager
    {
        $config = $this->getOptions();

        $baseCacheKey = md5(json_encode($config, JSON_THROW_ON_ERROR));

        return new BackendManager(
            $baseCacheKey,
            $this->servicesCacheItemPool,
            $this->client,
            $this->logger,
            $config['domains'],
            $this->serviceLocator->getServicesByName($config['services'], $config)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getOptions(): array
    {
        return array_merge(
            [
                'domains' => [$this->request->getHttpHost()],
                'services' => $this->socialServices->getActiveBackendServices(),
            ],
            $this->getFacebookCredentials()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getFacebookCredentials(): array
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        if (!empty($settings['fb_app_id']) && !empty($settings['fb_secret'])) {
            return [
                'facebook' => [
                    'app_id' => $settings['fb_app_id'],
                    'secret' => $settings['fb_secret'],
                ],
            ];
        }

        return [];
    }
}
