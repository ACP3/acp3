<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Auditlog\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Auditlog\ViewProviders\DataGridByTableViewProvider;
use Symfony\Component\HttpFoundation\JsonResponse;

class Table extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly DataGridByTableViewProvider $dataGridByTableViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, array<string, mixed>>|JsonResponse
     */
    public function __invoke(string $table): array|JsonResponse
    {
        return ($this->dataGridByTableViewProvider)($table);
    }
}
