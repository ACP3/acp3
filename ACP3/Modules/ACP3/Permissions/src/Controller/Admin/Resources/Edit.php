<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Resources;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions;

class Edit extends Core\Controller\AbstractWidgetAction implements Core\Controller\InvokableActionInterface
{
    /**
     * @var Permissions\Model\AclResourceModel
     */
    private $resourcesModel;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\ViewProviders\AdminResourceEditViewProvider
     */
    private $adminResourceEditViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Permissions\Model\AclResourceModel $resourcesModel,
        Permissions\ViewProviders\AdminResourceEditViewProvider $adminResourceEditViewProvider
    ) {
        parent::__construct($context);

        $this->resourcesModel = $resourcesModel;
        $this->adminResourceEditViewProvider = $adminResourceEditViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \ReflectionException
     */
    public function __invoke(int $id): array
    {
        $resource = $this->resourcesModel->getOneById($id);

        if (!empty($resource)) {
            return ($this->adminResourceEditViewProvider)($resource);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
