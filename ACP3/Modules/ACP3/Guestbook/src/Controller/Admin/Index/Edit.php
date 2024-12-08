<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Guestbook;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Guestbook\Model\GuestbookModel $guestbookModel,
        private readonly Guestbook\ViewProviders\AdminGuestbookEditViewProvider $adminGuestbookEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $guestbookEntry = $this->guestbookModel->getOneById($id);
        if (empty($guestbookEntry) === false) {
            return ($this->adminGuestbookEditViewProvider)($guestbookEntry);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
