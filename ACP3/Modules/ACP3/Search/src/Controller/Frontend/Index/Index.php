<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Search\Controller\Frontend\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\InvokableActionInterface;
use ACP3\Modules\ACP3\Search\ViewProviders\SearchViewProvider;
use Symfony\Component\DependencyInjection\ServiceLocator;

class Index extends AbstractWidgetAction
{
    /**
     * @param ServiceLocator<InvokableActionInterface> $controllerActionServiceLocator
     */
    public function __construct(
        Context $context,
        private readonly SearchViewProvider $searchViewProvider,
        private readonly ServiceLocator $controllerActionServiceLocator,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $q = ''): array
    {
        if (!empty($q)) {
            return ($this->controllerActionServiceLocator->get('search.controller.frontend.index.index_post'))($q);
        }

        return ($this->searchViewProvider)();
    }
}
