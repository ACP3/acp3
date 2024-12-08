<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Feeds\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Feeds;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Core\ACL $acl,
        private readonly Feeds\View\Renderer\FeedGenerator $feedGenerator,
        private readonly Feeds\Utility\FeedAvailabilityRegistrar $availableFeedsRegistrar,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Core\Controller\Exception\ResultNotExistsException
     */
    public function __invoke(string $feed): Response
    {
        if ($this->acl->hasPermission('frontend/' . $feed) === true) {
            try {
                $feedItems = $this->availableFeedsRegistrar
                    ->getFeedItemsByModuleName($feed)
                    ->fetchFeedItems();

                $this->feedGenerator
                    ->setTitle($this->config->getSettings(Schema::MODULE_NAME)['site_title'])
                    ->setDescription($this->translator->t($feed, $feed))
                    ->assign($feedItems);

                $response = new Response($this->feedGenerator->generateFeed(), Response::HTTP_OK, [
                    'Content-type' => 'text/xml',
                ]);
                $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

                return $response;
            } catch (\InvalidArgumentException) {
            }
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
