<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Resources;

use ACP3\Core\Controller\AbstractFrontendAction;
use ACP3\Core\Controller\Context\FrontendContext;
use ACP3\Core\Controller\InvokableActionInterface;
use ACP3\Core\Modules;

abstract class AbstractFormAction extends AbstractFrontendAction implements InvokableActionInterface
{
    /**
     * @var \ACP3\Core\Modules
     */
    private $modules;

    public function __construct(
        FrontendContext $context,
        Modules $modules
    ) {
        parent::__construct($context);

        $this->modules = $modules;
    }

    /**
     * @param string $moduleName
     *
     * @return int
     */
    protected function fetchModuleId(string $moduleName): int
    {
        $moduleInfo = $this->modules->getModuleInfo($moduleName);

        return $moduleInfo['id'] ?? 0;
    }
}