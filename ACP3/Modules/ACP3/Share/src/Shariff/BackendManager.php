<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Shariff;

use ACP3\Modules\ACP3\Share\Shariff\Backend\ServiceInterface;
use GuzzleHttp\Exception\TransferException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class BackendManager
{
    /**
     * @param string[]           $domains
     * @param ServiceInterface[] $services
     */
    public function __construct(
        private readonly string $baseCacheKey,
        private readonly CacheItemPoolInterface $cache,
        private readonly ClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly array $domains,
        private readonly array $services,
    ) {
    }

    /**
     * @return array|mixed|null
     *
     * @throws \JsonException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(string $url)
    {
        // Changing configuration invalidates the cache
        $cacheKey = md5($url . $this->baseCacheKey);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return json_decode((string) $cacheItem->get(), true, 512, JSON_THROW_ON_ERROR);
        }

        if (!$this->isValidDomain($url)) {
            return null;
        }

        $requests = array_map(
            static fn ($service) => $service->getRequest($url),
            $this->services
        );

        $results = [];
        // @TODO: Make this async again!
        foreach ($requests as $request) {
            $results[] = $this->client->sendRequest($request);
        }

        $counts = [];
        $i = 0;
        foreach ($this->services as $service) {
            if ($results[$i] instanceof TransferException) {
                $this->logger->warning($results[$i]->getMessage(), ['exception' => $results[$i]]);
            } else {
                $content = $service->filterResponse($results[$i]->getBody()->getContents());

                try {
                    $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                    $counts[$service->getName()] = \is_array($json) ? $service->extractCount($json) : 0;
                } catch (\Exception $e) {
                    $this->logger->warning($e->getMessage(), ['exception' => $e, 'content' => $content, 'uri' => $requests[$i]->getUri()]);
                }
            }
            ++$i;
        }

        $cacheItem->set(json_encode($counts, JSON_THROW_ON_ERROR));
        $this->cache->save($cacheItem);

        return $counts;
    }

    private function isValidDomain(string $url): bool
    {
        if (!empty($this->domains)) {
            $parsed = parse_url($url);

            return \in_array($parsed['host'], $this->domains, true);
        }

        return true;
    }
}
