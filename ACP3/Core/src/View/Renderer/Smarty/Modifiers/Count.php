<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty\Modifiers;

class Count extends AbstractModifier
{
    public function __invoke(mixed $value): int
    {
        return \count($value);
    }
}
