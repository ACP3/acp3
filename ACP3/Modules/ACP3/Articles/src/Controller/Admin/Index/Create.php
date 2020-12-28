<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Articles;

class Create extends Core\Controller\AbstractFrontendAction implements Core\Controller\InvokableActionInterface
{
    /**
     * @var \ACP3\Modules\ACP3\Articles\ViewProviders\AdminArticleEditViewProvider
     */
    private $adminArticleEditViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Articles\ViewProviders\AdminArticleEditViewProvider $adminArticleEditViewProvider
    ) {
        parent::__construct($context);

        $this->adminArticleEditViewProvider = $adminArticleEditViewProvider;
    }

    /**
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function __invoke(): array
    {
        $defaults = [
            'active' => 1,
            'layout' => '',
            'start' => '',
            'end' => '',
            'title' => '',
            'subtitle' => '',
            'text' => '',
        ];

        return ($this->adminArticleEditViewProvider)($defaults);
    }
}