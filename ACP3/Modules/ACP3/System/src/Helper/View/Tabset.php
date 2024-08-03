<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Helper\View;

use ACP3\Core\Helpers\View\Dto\TabDto;
use ACP3\Core\Helpers\View\Exception\InvalidTabsetException;

class Tabset
{
    /**
     * @var array<string, TabDto[]>
     */
    private $tabsets = [];

    public function addTabset(string $identifier): void
    {
        $this->tabsets[$identifier] = [];
    }

    public function addTab(string $tabSetIdentifier, TabDto $tab): void
    {
        $this->tabsets[$tabSetIdentifier][] = $tab;
    }

    /**
     * @return TabDto[]
     */
    public function getTabset(string $identifier): array
    {
        if (!\array_key_exists($identifier, $this->tabsets)) {
            throw new InvalidTabsetException(\sprintf('Could not find tabset with the identifier "%s"!', $identifier));
        }

        return $this->tabsets[$identifier];
    }
}
