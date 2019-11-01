<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

use ACP3\Core\Modules;

class ModuleIsInstalledValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Core\Modules
     */
    protected $modules;

    /**
     * ModuleIsInstalledValidationRule constructor.
     *
     * @param \ACP3\Core\Modules $modules
     */
    public function __construct(Modules $modules)
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return $this->modules->isInstalled($data);
    }
}
