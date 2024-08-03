<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\DataGrid\ColumnRenderer;

use ACP3\Core\DataGrid\ColumnRenderer\AbstractColumnRenderer;
use ACP3\Core\Helpers\View\Icon;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;

class AccountStatusColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(private readonly Translator $translator, private readonly RouterInterface $router, private readonly Icon $icon)
    {
    }

    public function fetchDataAndRenderColumn(array $column, array $dbResultRow): string|array
    {
        $dbValue = $this->getValue($column, $dbResultRow);

        $column['attribute'] += [
            'sort' => $dbValue,
        ];

        return $this->render($column, $this->getHtmlValue($column, $dbResultRow, $dbValue));
    }

    public static function mandatoryAttributes(): array
    {
        return ['sort', '_'];
    }

    /**
     * @param array<string, mixed> $column
     * @param array<string, mixed> $dbResultRow
     */
    private function getHtmlValue(array $column, array $dbResultRow, ?string $dbValue): string
    {
        if ($dbValue === $this->getDefaultValue($column)) {
            return $this->getDefaultValue($column);
        }

        if ((int) $dbValue === 0 && isset($dbResultRow[$this->getPrimaryKey()])) {
            return \sprintf(
                '<a href="%s" title="%s">' . ($this->icon)('solid', 'user-xmark', ['cssSelectors' => 'text-danger']) . '</a>',
                $this->router->route('acp/newsletter/accounts/activate/id_' . $dbResultRow[$this->getPrimaryKey()]),
                $this->translator->t('newsletter', 'activate_account')
            );
        }

        return ($this->icon)('solid', 'user-check', ['cssSelectors' => 'text-success']);
    }
}
