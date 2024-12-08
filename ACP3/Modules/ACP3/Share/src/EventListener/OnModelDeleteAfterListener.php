<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\EventListener;

use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Modules;
use ACP3\Core\Router\RoutePathPatterns;
use ACP3\Modules\ACP3\Share\Helpers\SocialSharingManager;
use ACP3\Modules\ACP3\Share\Installer\Schema as ShareSchema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnModelDeleteAfterListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RoutePathPatterns $routePathPatterns,
        private readonly Modules $modules,
        private readonly SocialSharingManager $socialSharingManager,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(AfterModelDeleteEvent $event): void
    {
        if ($event->getModuleName() === ShareSchema::MODULE_NAME) {
            return;
        }

        if (!$this->modules->isInstalled(ShareSchema::MODULE_NAME)) {
            return;
        }

        foreach ($event->getEntryIdList() as $entryId) {
            if (!$this->routePathPatterns->hasRoutePathPattern($event->getTableName())) {
                continue;
            }

            $route = \sprintf(
                $this->routePathPatterns->getRoutePathPattern($event->getTableName()),
                $entryId
            );

            $this->socialSharingManager->deleteSharingInfo($route);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterModelDeleteEvent::class => '__invoke',
        ];
    }
}
