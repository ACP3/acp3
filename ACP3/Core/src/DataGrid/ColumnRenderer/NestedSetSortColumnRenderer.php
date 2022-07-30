<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer;

class NestedSetSortColumnRenderer extends SortColumnRenderer
{
    /**
     * {@inheritdoc}
     */
    public function fetchDataAndRenderColumn(array $column, array $dbResultRow): string|array
    {
        $value = '';
        if ($dbResultRow['first'] === true && $dbResultRow['last'] === true) {
            $value = $this->fetchSortForbiddenHtml();
        } else {
            if ($dbResultRow['last'] === false) {
                $value .= $this->fetchSortDirectionHtml(
                    $this->router->route(sprintf($column['custom']['route_sort_down'], $dbResultRow[$this->getPrimaryKey()])),
                    'down'
                );
            }
            if ($dbResultRow['first'] === false) {
                $value .= $this->fetchSortDirectionHtml(
                    $this->router->route(sprintf($column['custom']['route_sort_up'], $dbResultRow[$this->getPrimaryKey()])),
                    'up'
                );
            }
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
}
