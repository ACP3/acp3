<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Emoticons\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Emoticons;

/**
 * Class Settings
 * @package ACP3\Modules\ACP3\Emoticons\Controller\Admin\Index
 */
class Settings extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Emoticons\Validation\AdminSettingsFormValidation
     */
    protected $adminSettingsFormValidation;

    /**
     * Settings constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext                          $context
     * @param \ACP3\Core\Helpers\FormToken                                        $formTokenHelper
     * @param \ACP3\Modules\ACP3\Emoticons\Validation\AdminSettingsFormValidation $adminSettingsFormValidation
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Core\Helpers\FormToken $formTokenHelper,
        Emoticons\Validation\AdminSettingsFormValidation $adminSettingsFormValidation)
    {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
        $this->adminSettingsFormValidation = $adminSettingsFormValidation;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute()
    {
        return [
            'form' => array_merge(
                $this->config->getSettings(Emoticons\Installer\Schema::MODULE_NAME),
                $this->request->getPost()->all()
            ),
            'form_token' => $this->formTokenHelper->renderFormToken()
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePost()
    {
        return $this->actionHelper->handleSettingsPostAction(function () {
            $formData = $this->request->getPost()->all();
            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'width' => (int)$formData['width'],
                'height' => (int)$formData['height'],
                'filesize' => (int)$formData['filesize'],
            ];

            return $this->config->saveSettings($data, Emoticons\Installer\Schema::MODULE_NAME);
        });
    }
}
