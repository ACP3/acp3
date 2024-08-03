<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\EventListener;

use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Modules;
use ACP3\Core\Router\RoutePathPatterns;
use ACP3\Modules\ACP3\Seo\Helper\UriAliasManager;
use ACP3\Modules\ACP3\Seo\Installer\Schema as SeoSchema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnModelDeleteAfterListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RoutePathPatterns $routePathPatterns,
        private readonly Modules $modules,
        private readonly UriAliasManager $uriAliasManager,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(AfterModelDeleteEvent $event): void
    {
        if ($event->getModuleName() === SeoSchema::MODULE_NAME) {
            return;
        }

        if (!$this->modules->isInstalled(SeoSchema::MODULE_NAME)) {
            return;
        }

        foreach ($event->getEntryIdList() as $articleId) {
            if (!$this->routePathPatterns->hasRoutePathPattern($event->getTableName())) {
                continue;
            }

            $route = \sprintf(
                $this->routePathPatterns->getRoutePathPattern($event->getTableName()),
                $articleId
            );
            $this->uriAliasManager->deleteUriAlias($route);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterModelDeleteEvent::class => '__invoke',
        ];
    }
}
