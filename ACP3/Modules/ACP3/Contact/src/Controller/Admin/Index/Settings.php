<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Contact\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Contact;

class Settings extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Contact\Validation\AdminSettingsFormValidation
     */
    private $adminSettingsFormValidation;
    /**
     * @var Core\Helpers\Secure
     */
    private $secureHelper;
    /**
     * @var \ACP3\Modules\ACP3\Contact\ViewProviders\AdminSettingsViewProvider
     */
    private $adminSettingsViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Helpers\Secure $secureHelper,
        Contact\Validation\AdminSettingsFormValidation $adminSettingsFormValidation,
        Contact\ViewProviders\AdminSettingsViewProvider $adminSettingsViewProvider
    ) {
        parent::__construct($context);

        $this->adminSettingsFormValidation = $adminSettingsFormValidation;
        $this->secureHelper = $secureHelper;
        $this->adminSettingsViewProvider = $adminSettingsViewProvider;
    }

    public function execute(): array
    {
        return ($this->adminSettingsViewProvider)();
    }

    /**
     * @return array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executePost()
    {
        return $this->actionHelper->handleSettingsPostAction(function () {
            $formData = $this->request->getPost()->all();
            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'address' => $this->secureHelper->strEncode($formData['address'], true),
                'ceo' => $this->secureHelper->strEncode($formData['ceo']),
                'disclaimer' => $this->secureHelper->strEncode($formData['disclaimer'], true),
                'fax' => $this->secureHelper->strEncode($formData['fax']),
                'mail' => $formData['mail'],
                'mobile_phone' => $this->secureHelper->strEncode($formData['mobile_phone']),
                'picture_credits' => $this->secureHelper->strEncode($formData['picture_credits'], true),
                'telephone' => $this->secureHelper->strEncode($formData['telephone']),
                'vat_id' => $this->secureHelper->strEncode($formData['vat_id']),
            ];

            return $this->config->saveSettings($data, Contact\Installer\Schema::MODULE_NAME);
        });
    }
}
