<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\ViewProviders\DataGridViewProvider
     */
    private $dataGridViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Newsletter\ViewProviders\DataGridViewProvider $dataGridViewProvider
    ) {
        parent::__construct($context);

        $this->dataGridViewProvider = $dataGridViewProvider;
    }

    /**
     * @return array|array[]|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function execute()
    {
        return ($this->dataGridViewProvider)();
    }
}
