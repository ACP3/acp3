<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Model;


use ACP3\Core\Model\AbstractModel;
use ACP3\Modules\ACP3\Permissions\Installer\Schema;
use ACP3\Modules\ACP3\Permissions\Model\Repository\ResourceRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourcesModel extends AbstractModel
{
    const EVENT_PREFIX = Schema::MODULE_NAME;

    /**
     * ResourcesModel constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param ResourceRepository $resourceRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ResourceRepository $resourceRepository
    ) {
        parent::__construct($eventDispatcher, , $resourceRepository);
    }

    /**
     * @param array $formData
     * @param int|null $entryId
     * @return bool|int
     */
    public function saveResource(array $formData, $entryId = null)
    {
        $data = [
            'module_id' => $formData['module_id'],
            'area' => $formData['area'],
            'controller' => $formData['controller'],
            'page' => $formData['resource'],
            'privilege_id' => $formData['privileges'],
        ];

        return $this->save($data, $entryId);
    }

    /**
     * @return array
     */
    protected function getAllowedColumns()
    {
        return [
            'module_id',
            'area',
            'controller',
            'page',
            'privilege_id'
        ];
    }
}
