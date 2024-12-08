<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;
use Symfony\Component\HttpFoundation\JsonResponse;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Comments\ViewProviders\DataGridByModuleViewProvider $dataGridByModuleViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, array<string, mixed>>|JsonResponse
     */
    public function __invoke(int $id): array|JsonResponse
    {
        return ($this->dataGridByModuleViewProvider)($id);
    }
}
