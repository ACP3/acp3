<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter\ViewProviders\NewsletterSubscribeWidgetViewProvider;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly NewsletterSubscribeWidgetViewProvider $newsletterSubscribeWidgetViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $template = ''): array
    {
        $this->setTemplate($template);

        return ($this->newsletterSubscribeWidgetViewProvider)();
    }
}
