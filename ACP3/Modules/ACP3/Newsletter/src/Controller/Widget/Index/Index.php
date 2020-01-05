<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Widget\Index;

use ACP3\Core;

class Index extends Core\Controller\AbstractWidgetAction
{
    /**
     * @var Core\Helpers\FormToken
     */
    protected $formTokenHelper;

    /**
     * @param \ACP3\Core\Controller\Context\WidgetContext $context
     * @param \ACP3\Core\Helpers\FormToken                $formTokenHelper
     */
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Core\Helpers\FormToken $formTokenHelper
    ) {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
    }

    /**
     * @param string $template
     *
     * @return array
     */
    public function execute($template = '')
    {
        $this->setTemplate($template);

        return [
            'form_token' => $this->formTokenHelper->renderFormToken(),
        ];
    }
}