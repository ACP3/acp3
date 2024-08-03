<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer;

use ACP3\Core\Helpers\View\Icon;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;

class SortColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(private readonly Translator $translator, protected RouterInterface $router, private readonly Icon $icon)
    {
    }

    public function fetchDataAndRenderColumn(array $column, array $dbResultRow): string|array
    {
        $dbValue = $this->getValue($column, $dbResultRow);

        $value = '';
        if ($dbResultRow['last'] != $dbValue) {
            $value .= $this->fetchSortDirectionHtml(
                $this->router->route(\sprintf($column['custom']['route_sort_down'], $dbResultRow[$this->getPrimaryKey()])),
                'down'
            );
        }
        if ($dbResultRow['first'] != $dbValue) {
            $value .= $this->fetchSortDirectionHtml(
                $this->router->route(\sprintf($column['custom']['route_sort_up'], $dbResultRow[$this->getPrimaryKey()])),
                'up'
            );
        }
        if ($dbResultRow['first'] == $dbResultRow['last']) {
            $value = $this->fetchSortForbiddenHtml();
        }

        $column['attribute'] += [
            'sort' => str_pad(
                (string) $dbResultRow[$this->getFirstDbField($column)],
                \strlen((string) $this->getTotalResults()),
                '0',
                STR_PAD_LEFT
            ),
        ];

        return $this->render($column, $value);
    }

    protected function fetchSortDirectionHtml(string $url, string $direction): string
    {
        $arrowIcon = ($this->icon)('solid', 'arrow-' . $direction);
        $html = <<<HTML
<a href="%s"
   title="%s"
   class="btn btn-light btn-sm mx-1"
   data-ajax-form="true"
   data-ajax-form-method="post"
   data-ajax-form-loading-text="%s"
   data-ajax-form-custom-form-data='%s'
   data-ajax-form-complete-callback="%s">
    $arrowIcon
</a>
HTML;

        return \sprintf(
            $html,
            $url,
            $this->translator->t('system', 'move_' . $direction),
            $this->translator->t('system', 'loading_please_wait'),
            json_encode(['submit' => true], JSON_THROW_ON_ERROR),
            substr($this->getIdentifier(), 1) . 'ReloadDataTable',
            $direction
        );
    }

    protected function fetchSortForbiddenHtml(): string
    {
        $html = ($this->icon)('solid', 'circle-xmark', ['cssSelectors' => 'text-danger', 'title' => $this->translator->t('system', 'move_impossible')]);

        return '<span class="btn btn-light btn-sm disabled">' . \sprintf($html, $this->translator->t('system', 'move_impossible')) . '</span>';
    }

    public static function mandatoryAttributes(): array
    {
        return ['sort', '_'];
    }
}
