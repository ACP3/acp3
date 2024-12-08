<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer\OptionColumnRenderer;

use ACP3\Core\Helpers\View\Icon;
use ACP3\Core\Router\RouterInterface;

class OptionRenderer
{
    /**
     * @var string[]
     */
    private array $options = [];

    public function __construct(private readonly RouterInterface $router, private readonly Icon $icon)
    {
    }

    public function addOption(
        string $route,
        string $translationPhrase,
        string $icon,
        string $iconSelector = '',
        bool $useAjax = false,
    ): void {
        $ajax = $useAjax === true ? ' data-ajax-form="true"' : '';
        $value = '<li><a href="' . $this->router->route($route) . '" class="dropdown-item"' . $ajax . '>';
        $value .= ($this->icon)('solid', str_starts_with($icon, 'fa-') ? substr($icon, 3) : $icon, ['cssSelectors' => $iconSelector]);
        $value .= '<span class="ms-2">' . $translationPhrase . '</span>';
        $value .= '</a></li>';

        $this->options[] = $value;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function clearOptions(): void
    {
        $this->options = [];
    }
}
