<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Guestbook;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private Guestbook\ViewProviders\GuestbookCreateViewProvider $guestbookCreateViewProvider
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return ($this->guestbookCreateViewProvider)();
    }
}
