<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\ViewProviders;

use ACP3\Core\ACL;
use ACP3\Core\ACL\PermissionEnum;
use ACP3\Core\ACL\PermissionServiceInterface;
use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Helpers\Forms;
use ACP3\Core\Helpers\FormToken;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Modules;
use ACP3\Modules\ACP3\Permissions\Repository\AclPrivilegeRepository;
use ACP3\Modules\ACP3\Permissions\Repository\AclResourceRepository;

class AdminRoleEditViewProvider
{
    /**
     * @var \ACP3\Core\ACL
     */
    private $acl;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    private $formsHelper;
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    private $formTokenHelper;
    /**
     * @var \ACP3\Core\Modules
     */
    private $modules;
    /**
     * @var PermissionServiceInterface
     */
    private $permissionService;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Repository\AclPrivilegeRepository
     */
    private $privilegeRepository;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    private $request;
    /**
     * @var \ACP3\Core\Breadcrumb\Title
     */
    private $title;
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    private $translator;
    /**
     * @var AclResourceRepository
     */
    private $resourceRepository;

    public function __construct(
        ACL $acl,
        Forms $formsHelper,
        FormToken $formTokenHelper,
        Modules $modules,
        PermissionServiceInterface $permissionService,
        AclPrivilegeRepository $privilegeRepository,
        AclResourceRepository $resourceRepository,
        RequestInterface $request,
        Title $title,
        Translator $translator
    ) {
        $this->acl = $acl;
        $this->formsHelper = $formsHelper;
        $this->formTokenHelper = $formTokenHelper;
        $this->modules = $modules;
        $this->permissionService = $permissionService;
        $this->privilegeRepository = $privilegeRepository;
        $this->request = $request;
        $this->title = $title;
        $this->translator = $translator;
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(array $role): array
    {
        $this->title->setPageTitlePrefix($role['name']);

        return [
            'parent' => $role['id'] != 1
                ? $this->fetchRoles($role['parent_id'], $role['left_id'], $role['right_id'])
                : [],
            'modules' => $this->fetchModulePermissions($role['id']),
            'form' => array_merge($role, $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
        ];
    }

    private function fetchRoles(int $roleParentId = 0, int $roleLeftId = 0, int $roleRightId = 0): array
    {
        $roles = [];
        foreach ($this->acl->getAllRoles() as $role) {
            if ($role['left_id'] >= $roleLeftId && $role['right_id'] <= $roleRightId) {
                continue;
            }

            $roles[(int) $role['id']] = str_repeat('&nbsp;&nbsp;', $role['level']) . $role['name'];
        }

        return $this->formsHelper->choicesGenerator('parent_id', $roles, $roleParentId);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchModulePermissions(int $roleId): array
    {
        $rules = $this->permissionService->getRules([$roleId]);
        $permissions = $this->permissionService->getPermissions([$roleId]);
        $modules = array_filter($this->modules->getInstalledModules(), function ($module) {
            return $this->modules->isInstallable($module['name']);
        });
        $allResources = $this->resourceRepository->getAllResources();
        $privileges = $this->privilegeRepository->getAllPrivileges();

        foreach ($modules as $moduleName => $moduleInfo) {
            $moduleResources = array_filter($allResources, static function ($resource) use ($moduleInfo) {
                return (int) $resource['module_id'] === $moduleInfo['id'];
            });
            foreach ($moduleResources as &$resource) {
                $resource['select'] = $this->generatePermissionCheckboxes(
                    $roleId,
                    $moduleInfo['id'],
                    $resource['resource_id'],
                    (int) ($permissions[$roleId][(int) $resource['resource_id']] ?? 2)
                );
            }
            unset($resource);

            $modules[$moduleName]['resources'] = $moduleResources;
            foreach ($privileges as $j => $privilege) {
                $privileges[$j]['select'] = $this->generatePrivilegeCheckboxes(
                    $roleId,
                    $moduleInfo['id'],
                    $privilege['id'],
                    (int) ($rules[$moduleInfo['name']][$privilege['key']]['permission'] ?? 0)
                );
                if ($roleId !== 0) {
                    $privileges[$j]['calculated'] = $this->calculatePermission($rules, $moduleInfo['name'], $privilege['key']);
                }
            }
            $modules[$moduleName]['privileges'] = $privileges;
        }

        return $modules;
    }

    private function generatePermissionCheckboxes(int $roleId, int $moduleId, int $resourceId, int $defaultValue): array
    {
        $permissions = [
            PermissionEnum::PERMIT_ACCESS => 'allow_access',
            PermissionEnum::INHERIT_ACCESS => 'inherit_access',
        ];

        $select = [];
        foreach ($permissions as $value => $phrase) {
            if ($roleId === 1 && $value === 2) {
                continue;
            }

            $select[$value] = [
                'value' => $value,
                'selected' => $this->resourceIsChecked($moduleId, $resourceId, $value, $defaultValue),
                'lang' => $this->translator->t('permissions', $phrase),
            ];
        }

        return $select;
    }

    private function resourceIsChecked(int $moduleId, int $resourceId, int $value, int $defaultValue): string
    {
        if (($this->request->getPost()->count() === 0 && $defaultValue === $value) ||
            ($this->request->getPost()->count() !== 0 && (int) $this->request->getPost()->get('resources')[$moduleId][$resourceId] === $value)
        ) {
            return ' checked="checked"';
        }

        return '';
    }

    private function generatePrivilegeCheckboxes(int $roleId, int $moduleId, int $privilegeId, int $defaultValue): array
    {
        $permissions = [
            PermissionEnum::DENY_ACCESS => 'deny_access',
            PermissionEnum::PERMIT_ACCESS => 'allow_access',
            PermissionEnum::INHERIT_ACCESS => 'inherit_access',
        ];

        $select = [];
        foreach ($permissions as $value => $phrase) {
            if ($roleId === 1 && $value === 2) {
                continue;
            }

            $select[$value] = [
                'value' => $value,
                'selected' => $this->privilegeIsChecked($moduleId, $privilegeId, $value, $defaultValue),
                'lang' => $this->translator->t('permissions', $phrase),
            ];
        }

        return $select;
    }

    private function privilegeIsChecked(int $moduleId, int $privilegeId, int $value = 0, ?int $defaultValue = null): string
    {
        if (($this->request->getPost()->count() === 0 && $defaultValue === $value) ||
            ($this->request->getPost()->count() !== 0 && (int) $this->request->getPost()->get('privileges')[$moduleId][$privilegeId] === $value)
        ) {
            return ' checked="checked"';
        }

        return '';
    }

    private function calculatePermission(array $rules, string $moduleName, string $key): string
    {
        return sprintf(
            $this->translator->t('permissions', 'calculated_permission'),
            $this->translator->t(
                'permissions',
                isset($rules[$moduleName][$key]) && $rules[$moduleName][$key]['access'] === true ? 'allow_access' : 'deny_access'
            )
        );
    }
}
