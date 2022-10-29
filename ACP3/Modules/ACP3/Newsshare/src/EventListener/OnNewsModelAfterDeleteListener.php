<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsshare\EventListener;

use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Modules;
use ACP3\Modules\ACP3\News\Helpers;
use ACP3\Modules\ACP3\Share\Helpers\SocialSharingManager;
use ACP3\Modules\ACP3\Share\Installer\Schema as ShareSchema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnNewsModelAfterDeleteListener implements EventSubscriberInterface
{
    public function __construct(private readonly Modules $modules, private readonly SocialSharingManager $socialSharingManager)
    {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(AfterModelDeleteEvent $event): void
    {
        if (!$this->modules->isInstalled(ShareSchema::MODULE_NAME)) {
            return;
        }

        foreach ($event->getEntryIdList() as $item) {
            $uri = sprintf(Helpers::URL_KEY_PATTERN, $item);
            $this->socialSharingManager->deleteSharingInfo($uri);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'news.model.news.after_delete' => '__invoke',
        ];
    }
}
