<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newssearch\Extension;

use ACP3\Modules\ACP3\News\Helpers;
use ACP3\Modules\ACP3\News\Installer\Schema;
use ACP3\Modules\ACP3\Search\Extension\AbstractSearchAvailabilityExtension;

class SearchAvailabilityExtension extends AbstractSearchAvailabilityExtension
{
    public function getModuleName(): string
    {
        return Schema::MODULE_NAME;
    }

    protected function getRouteName(): string
    {
        return Helpers::URL_KEY_PATTERN;
    }
}
