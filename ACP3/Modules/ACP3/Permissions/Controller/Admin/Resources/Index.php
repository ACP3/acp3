<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Resources;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\ResourceRepository
     */
    protected $resourceRepository;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext              $context
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\ResourceRepository $resourceRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Permissions\Model\Repository\ResourceRepository $resourceRepository
    ) {
        parent::__construct($context);

        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $resources = $this->resourceRepository->getAllResources();
        $cResources = count($resources);
        $output = [];
        for ($i = 0; $i < $cResources; ++$i) {
            if ($this->modules->isActive($resources[$i]['module_name']) === true) {
                $module = $this->translator->t($resources[$i]['module_name'], $resources[$i]['module_name']);
                $output[$module][] = $resources[$i];
            }
        }
        ksort($output);

        return [
            'resources' => $output,
            'can_delete_resource' => $this->acl->hasPermission('admin/permissions/resources/delete'),
            'can_edit_resource' => $this->acl->hasPermission('admin/permissions/resources/edit')
        ];
    }
}
