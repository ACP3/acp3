<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Files;
use Symfony\Component\HttpFoundation\JsonResponse;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Files\ViewProviders\DataGridViewProvider $dataGridViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, array<string, mixed>>|JsonResponse
     *
     * @throws \JsonException
     */
    public function __invoke()
    {
        return ($this->dataGridViewProvider)();
    }
}
