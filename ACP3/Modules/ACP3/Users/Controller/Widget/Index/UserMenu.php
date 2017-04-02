<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class UserMenu
 * @package ACP3\Modules\ACP3\Users\Controller\Widget\Index
 */
class UserMenu extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var array
     */
    protected $systemActions = [
        [
            'controller' => 'index',
            'action' => 'configuration',
            'phrase' => 'configuration'
        ],
        [
            'controller' => 'extensions',
            'action' => '',
            'phrase' => 'extensions'
        ],
        [
            'controller' => 'maintenance',
            'action' => '',
            'phrase' => 'maintenance'
        ],
    ];

    /**
     * Displays the user menu, if the user is logged in
     *
     * @return array|void
     */
    public function execute()
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        if ($this->user->isAuthenticated() === true) {
            $prefix = $this->request->getArea() === Core\Controller\AreaEnum::AREA_ADMIN ? 'acp/' : '';

            $userSidebar = [
                'current_page' => base64_encode($prefix . $this->request->getQuery()),
                'modules' => $this->addModules(),
                'system' => $this->addSystemActions()
            ];

            return [
                'user_sidebar' => $userSidebar
            ];
        }

        $this->setContent(false);
    }

    /**
     * @return array
     */
    protected function addSystemActions()
    {
        $navSystem = [];
        foreach ($this->systemActions as $action) {
            $permissions = 'admin/system/' . $action['controller'] . '/' . $action['action'];
            if ($this->acl->hasPermission($permissions) === true) {
                $path = 'system/' . $action['controller'] . '/' . $action['action'];
                $navSystem[] = [
                    'path' => $path,
                    'name' => $this->translator->t('system', $action['phrase']),
                    'is_active' => strpos($this->request->getQuery(), $path) === 0
                ];
            }
        }

        return $navSystem;
    }

    /**
     * @return array
     */
    protected function addModules()
    {
        $activeModules = $this->modules->getActiveModules();
        $navMods = [];
        foreach ($activeModules as $name => $info) {
            $dir = strtolower($info['dir']);
            if (!in_array($dir, ['acp', 'system']) && $this->acl->hasPermission('admin/' . $dir . '/index') === true) {
                $navMods[$name] = [
                    'path' => $dir,
                    'name' => $name,
                    'is_active' => $this->request->getArea() === Core\Controller\AreaEnum::AREA_ADMIN && $dir === $this->request->getModule()
                ];
            }
        }

        return $navMods;
    }
}
