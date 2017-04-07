<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Acp\Controller\Admin\Index;

use ACP3\Core;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Acp\Controller\Admin\Index
 */
class Index extends Core\Controller\AbstractAdminAction
{
    /**
     * @return array
     */
    public function execute()
    {
        return [
            'modules' => $this->getAllowedModules()
        ];
    }

    /**
     * @return array
     */
    protected function getAllowedModules()
    {
        $allowedModules = [];

        foreach ($this->modules->getActiveModules() as $name => $info) {
            $dir = strtolower($info['dir']);
            if ($this->acl->hasPermission('admin/' . $dir) === true && $dir !== 'acp') {
                $allowedModules[$name] = [
                    'name' => $name,
                    'dir' => $dir
                ];
            }
        }
        return $allowedModules;
    }
}
