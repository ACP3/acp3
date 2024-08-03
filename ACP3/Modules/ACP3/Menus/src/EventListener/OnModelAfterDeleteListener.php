<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\EventListener;

use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Modules;
use ACP3\Core\Router\RoutePathPatterns;
use ACP3\Core\Validation\Exceptions\InvalidFormTokenException;
use ACP3\Core\Validation\Exceptions\ValidationFailedException;
use ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException;
use ACP3\Modules\ACP3\Menus\Helpers\ManageMenuItem;
use ACP3\Modules\ACP3\Menus\Installer\Schema as MenusSchema;
use Doctrine\DBAL\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnModelAfterDeleteListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RoutePathPatterns $routePathPatterns,
        private readonly Modules $modules,
        private readonly ManageMenuItem $manageMenuItem
    ) {
    }

    /**
     * @throws InvalidFormTokenException
     * @throws ValidationFailedException
     * @throws ValidationRuleNotFoundException
     * @throws Exception
     */
    public function __invoke(AfterModelDeleteEvent $event): void
    {
        if ($event->getModuleName() === MenusSchema::MODULE_NAME) {
            return;
        }

        if (!$this->modules->isInstalled(MenusSchema::MODULE_NAME)) {
            return;
        }

        foreach ($event->getEntryIdList() as $entryId) {
            if (!$this->routePathPatterns->hasRoutePathPattern($event->getTableName())) {
                continue;
            }

            $uri = \sprintf(
                $this->routePathPatterns->getRoutePathPattern($event->getTableName()),
                $entryId
            );

            $this->manageMenuItem->manageMenuItem($uri);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterModelDeleteEvent::class => '__invoke',
        ];
    }
}
