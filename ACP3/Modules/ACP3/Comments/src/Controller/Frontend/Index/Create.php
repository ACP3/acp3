<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;

class Create extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Comments\ViewProviders\CommentCreateViewProvider $commentCreateViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $module, int $entryId, string $redirectUrl, bool $embed = false): array
    {
        if ($embed === true) {
            $this->view->setLayout('System/layout.content_only.tpl');
        }

        return ($this->commentCreateViewProvider)($module, $entryId, $redirectUrl);
    }
}
