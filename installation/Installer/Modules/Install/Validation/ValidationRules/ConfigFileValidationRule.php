<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Installer\Modules\Install\Validation\ValidationRules;

use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;

class ConfigFileValidationRule extends AbstractValidationRule
{
    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        return \is_file($data) === true && \is_writable($data) === true;
    }
}
