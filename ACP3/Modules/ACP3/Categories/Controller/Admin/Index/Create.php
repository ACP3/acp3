<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Categories\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;

/**
 * Class Create
 * @package ACP3\Modules\ACP3\Categories\Controller\Admin\Index
 */
class Create extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Modules\ACP3\Categories\Validation\AdminFormValidation
     */
    protected $adminFormValidation;
    /**
     * @var Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;
    /**
     * @var Categories\Model\CategoriesModel
     */
    protected $categoriesModel;

    /**
     * @param \ACP3\Core\Controller\Context\AdminContext $context
     * @param \ACP3\Core\Helpers\Forms $formsHelper
     * @param Categories\Model\CategoriesModel $categoriesModel
     * @param \ACP3\Modules\ACP3\Categories\Validation\AdminFormValidation $adminFormValidation
     * @param \ACP3\Core\Helpers\FormToken $formTokenHelper
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Core\Helpers\Forms $formsHelper,
        Categories\Model\CategoriesModel $categoriesModel,
        Categories\Validation\AdminFormValidation $adminFormValidation,
        Core\Helpers\FormToken $formTokenHelper)
    {
        parent::__construct($context);

        $this->formsHelper = $formsHelper;
        $this->adminFormValidation = $adminFormValidation;
        $this->formTokenHelper = $formTokenHelper;
        $this->categoriesModel = $categoriesModel;
    }

    /**
     * @return array
     */
    public function execute()
    {
        return [
            'form' => array_merge(['title' => '', 'description' => ''], $this->request->getPost()->all()),
            'mod_list' => $this->fetchModules(),
            'form_token' => $this->formTokenHelper->renderFormToken()
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePost()
    {
        return $this->actionHelper->handleSaveAction(function () {
            $formData = $this->request->getPost()->all();
            $file = $this->request->getFiles()->get('picture');

            $this->adminFormValidation
                ->setFile($file)
                ->setSettings($this->config->getSettings(Categories\Installer\Schema::MODULE_NAME))
                ->validate($formData);

            if (!empty($file)) {
                $upload = new Core\Helpers\Upload($this->appPath, Categories\Installer\Schema::MODULE_NAME);
                $result = $upload->moveFile($file->getPathname(), $file->getClientOriginalName());
                $formData['picture'] = $result['name'];
            }

            return $this->categoriesModel->save($formData);
        });
    }

    /**
     * @return array
     */
    protected function fetchModules()
    {
        $modules = $this->modules->getActiveModules();
        foreach ($modules as $name => $info) {
            if ($info['active'] && in_array('categories', $info['dependencies']) === true) {
                $modules[$name]['selected'] = $this->formsHelper->selectEntry('module', $info['id']);
            } else {
                unset($modules[$name]);
            }
        }
        return $modules;
    }
}
