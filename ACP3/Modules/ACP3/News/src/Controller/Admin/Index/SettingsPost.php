<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\News;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class SettingsPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private FormAction $actionHelper,
        private Core\Helpers\Secure $secureHelper,
        private News\Validation\AdminSettingsFormValidation $adminSettingsFormValidation
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(): array|string|Response
    {
        return $this->actionHelper->handleSettingsPostAction(function () {
            $formData = $this->request->getPost()->all();

            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'dateformat' => $this->secureHelper->strEncode($formData['dateformat']),
                'sidebar' => (int) $formData['sidebar'],
                'readmore' => $formData['readmore'],
                'readmore_chars' => (int) $formData['readmore_chars'],
                'category_in_breadcrumb' => $formData['category_in_breadcrumb'],
            ];

            return $this->config->saveSettings($data, News\Installer\Schema::MODULE_NAME);
        });
    }
}
