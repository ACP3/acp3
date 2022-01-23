<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\EventListener;

use ACP3\Core\ACL;
use ACP3\Core\Model\Event\ModelSaveEvent;
use ACP3\Modules\ACP3\Menus\Helpers\ManageMenuItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ManageMenuItemOnModelSaveAfterListener implements EventSubscriberInterface
{
    public function __construct(private ACL $acl, private ManageMenuItem $menuItemManager)
    {
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function __invoke(ModelSaveEvent $event): void
    {
        if ($this->acl->hasPermission('admin/menus/items/create') === true
            && $this->hasNecessaryMenuItemFormFields($event->getRawData())) {
            $formData = $event->getRawData();

            $data = [
                'mode' => 2,
                'block_id' => $formData['block_id'],
                'parent_id' => (int) $formData['parent_id'],
                'display' => $formData['display'],
                'title' => $formData['menu_item_title'],
                'target' => 1,
            ];

            $this->menuItemManager->manageMenuItem(
                sprintf($formData['menu_item_uri_pattern'], $event->getEntryId()),
                isset($formData['create_menu_item']) ? $data : []
            );
        }
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function hasNecessaryMenuItemFormFields(array $formData): bool
    {
        return isset(
            $formData['block_id'],
            $formData['parent_id'],
            $formData['display'],
            $formData['menu_item_title'],
            $formData['menu_item_uri_pattern']
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'core.model.after_save' => '__invoke',
        ];
    }
}
