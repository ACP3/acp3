<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\EventListener;

use ACP3\Core\ACL;
use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Modules\ACP3\Share\Helpers\SocialSharingManager;
use ACP3\Modules\ACP3\Share\Installer\Schema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SaveSharingInfoOnModelAfterSaveListener implements EventSubscriberInterface
{
    public function __construct(private readonly ACL $acl, private readonly SocialSharingManager $socialSharingManager)
    {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(AfterModelSaveEvent $event): void
    {
        if (!$this->acl->hasPermission('admin/share/index/create')) {
            return;
        }

        $formData = $event->getRawData();

        if (!empty($formData['share_uri_pattern']) && $event->getModuleName() !== Schema::MODULE_NAME) {
            $this->socialSharingManager->saveSharingInfo(
                \sprintf($formData['share_uri_pattern'], $event->getEntryId()),
                $formData['share_active'] ?? false,
                (isset($formData['share_customize_services']) && (int) $formData['share_customize_services'] === 1) ? $formData['share_services'] : [],
                $formData['share_ratings_active']
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterModelSaveEvent::class => '__invoke',
        ];
    }
}
