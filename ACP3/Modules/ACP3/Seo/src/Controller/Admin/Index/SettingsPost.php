<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Seo;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class SettingsPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Core\Helpers\Secure $secureHelper,
        private readonly Seo\Validation\AdminSettingsFormValidation $adminSettingsFormValidation,
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
                'meta_description' => $this->secureHelper->strEncode($formData['meta_description']),
                'meta_keywords' => $this->secureHelper->strEncode($formData['meta_keywords']),
                'robots' => (int) $formData['robots'],
                'sitemap_is_enabled' => (int) $formData['sitemap_is_enabled'],
                'sitemap_save_mode' => (int) $formData['sitemap_save_mode'],
                'sitemap_separate' => (int) $formData['sitemap_separate'],
            ];

            return $this->config->saveSettings($data, Seo\Installer\Schema::MODULE_NAME);
        });
    }
}
