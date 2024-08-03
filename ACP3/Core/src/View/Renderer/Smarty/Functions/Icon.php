<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty\Functions;

use ACP3\Core\Helpers\View\Icon as IconViewHelper;

class Icon extends AbstractFunction
{
    public function __construct(private readonly IconViewHelper $iconHelper)
    {
    }

    public function __invoke(array $params, \Smarty_Internal_Template $smarty): mixed
    {
        if (isset($params['iconSet'], $params['icon'])) {
            $iconSet = $params['iconSet'];
            $icon = $params['icon'];

            return ($this->iconHelper)($iconSet, $icon, $params);
        }

        throw new \InvalidArgumentException(\sprintf('Not all necessary arguments for the function %s were passed!', __FUNCTION__));
    }
}
