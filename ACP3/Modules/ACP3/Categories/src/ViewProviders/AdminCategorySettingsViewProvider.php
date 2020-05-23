<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\ViewProviders;

use ACP3\Core\Helpers\FormToken;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Categories\Installer\Schema;

class AdminCategorySettingsViewProvider
{
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    private $formTokenHelper;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    private $request;
    /**
     * @var \ACP3\Core\Settings\SettingsInterface
     */
    private $settings;

    public function __construct(FormToken $formTokenHelper, RequestInterface $request, SettingsInterface $settings)
    {
        $this->formTokenHelper = $formTokenHelper;
        $this->request = $request;
        $this->settings = $settings;
    }

    public function __invoke()
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        return [
            'form' => \array_merge($settings, $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
        ];
    }
}
