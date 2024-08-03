<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer;

use ACP3\Core\Helpers\View\Icon;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;

class RouteColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(private readonly RouterInterface $router, private readonly Translator $translator, private readonly Icon $icon)
    {
    }

    protected function getValue(array $column, array $dbResultRow): ?string
    {
        $value = parent::getValue($column, $dbResultRow);

        if (!empty($column['custom']['path'])) {
            $route = $this->router->route(\sprintf($column['custom']['path'], $value));
            $linkIcon = ($this->icon)('solid', 'link');
            $pattern = <<<HTML
<a href="%s" target="_blank" title="%s">%s <small>$linkIcon</small></a>
HTML;
            $value = \sprintf(
                $pattern,
                $route,
                $this->translator->t('system', 'open_in_new_window'),
                $value
            );
        }

        return $value;
    }
}
