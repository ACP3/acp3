<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;

/**
 * Class Settings
 * @package ACP3\Modules\ACP3\News\Controller\Admin\Index
 */
class Settings extends Core\Controller\AbstractAdminAction
{
    use CommentsHelperTrait;

    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\News\Validation\AdminSettingsFormValidation
     */
    protected $adminSettingsFormValidation;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;

    /**
     * Settings constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext                     $context
     * @param \ACP3\Core\Helpers\Forms                                       $formsHelper
     * @param \ACP3\Core\Helpers\FormToken                                   $formTokenHelper
     * @param \ACP3\Modules\ACP3\News\Validation\AdminSettingsFormValidation $adminSettingsFormValidation
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Core\Helpers\Forms $formsHelper,
        Core\Helpers\FormToken $formTokenHelper,
        News\Validation\AdminSettingsFormValidation $adminSettingsFormValidation)
    {
        parent::__construct($context);

        $this->formsHelper = $formsHelper;
        $this->formTokenHelper = $formTokenHelper;
        $this->adminSettingsFormValidation = $adminSettingsFormValidation;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute()
    {
        if ($this->request->getPost()->count() !== 0) {
            return $this->executePost($this->request->getPost()->all());
        }

        $settings = $this->config->getSettings(News\Installer\Schema::MODULE_NAME);

        if ($this->modules->isActive('comments') === true) {
            $this->view->assign('allow_comments', $this->formsHelper->yesNoCheckboxGenerator('comments', $settings['comments']));
        }

        return [
            'dateformat' => $this->get('core.helpers.date')->dateFormatDropdown($settings['dateformat']),
            'readmore' => $this->formsHelper->yesNoCheckboxGenerator('readmore', $settings['readmore']),
            'readmore_chars' => $this->request->getPost()->get('readmore_chars', $settings['readmore_chars']),
            'sidebar_entries' => $this->formsHelper->recordsPerPage((int)$settings['sidebar'], 1, 10, 'sidebar'),
            'category_in_breadcrumb' => $this->formsHelper->yesNoCheckboxGenerator('category_in_breadcrumb', $settings['category_in_breadcrumb']),
            'form_token' => $this->formTokenHelper->renderFormToken()
        ];
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData)
    {
        return $this->actionHelper->handleSettingsPostAction(function () use ($formData) {
            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'dateformat' => $this->get('core.helpers.secure')->strEncode($formData['dateformat']),
                'sidebar' => (int)$formData['sidebar'],
                'readmore' => $formData['readmore'],
                'readmore_chars' => (int)$formData['readmore_chars'],
                'category_in_breadcrumb' => $formData['category_in_breadcrumb'],
            ];

            if ($this->commentsHelpers) {
                $data['comments'] = $formData['comments'];
            }

            return $this->config->saveSettings($data, News\Installer\Schema::MODULE_NAME);
        });
    }
}
