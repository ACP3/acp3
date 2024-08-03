<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer;

use ACP3\Core\DataGrid\ColumnRenderer\Event\CustomOptionEvent;
use ACP3\Core\DataGrid\ColumnRenderer\OptionColumnRenderer\OptionRenderer;
use ACP3\Core\Helpers\View\Icon;
use ACP3\Core\I18n\Translator;
use Symfony\Component\EventDispatcher\EventDispatcher;

class OptionColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(private readonly Translator $translator, private readonly OptionRenderer $optionRenderer, private readonly EventDispatcher $eventDispatcher, private readonly Icon $icon)
    {
    }

    public function fetchDataAndRenderColumn(array $column, array $dbResultRow): string|array
    {
        $this->eventDispatcher->dispatch(
            new CustomOptionEvent($this->optionRenderer, $dbResultRow, $this->getIdentifier()),
            'data_grid.column_renderer.custom_option_before'
        );

        if ($column['custom']['can_edit']) {
            $resourcePathEdit = $column['custom']['resource_path_edit'];
            $resourcePathEdit .= !str_ends_with((string) $resourcePathEdit, '/') ? '/' : '';
            $this->optionRenderer->addOption(
                $this->getEditRoute($dbResultRow, $resourcePathEdit),
                $this->translator->t('system', 'edit'),
                'pen',
            );
        }

        $this->eventDispatcher->dispatch(
            new CustomOptionEvent($this->optionRenderer, $dbResultRow, $this->getIdentifier()),
            'data_grid.column_renderer.custom_option_between'
        );

        if ($column['custom']['can_delete']) {
            $resourcePathDelete = $column['custom']['resource_path_delete'];
            $resourcePathDelete .= !str_ends_with((string) $resourcePathDelete, '/') ? '/' : '';
            $this->optionRenderer->addOption(
                $resourcePathDelete . 'entries_' . $dbResultRow[$this->getPrimaryKey()],
                $this->translator->t('system', 'delete'),
                'trash',
                'text-danger'
            );
        }

        $this->eventDispatcher->dispatch(
            new CustomOptionEvent($this->optionRenderer, $dbResultRow, $this->getIdentifier()),
            'data_grid.column_renderer.custom_option_after'
        );

        return $this->render($column, $this->collectOptions());
    }

    protected function collectOptions(): string
    {
        $icon = ($this->icon)('solid', 'ellipsis-vertical');
        $options = implode('', $this->optionRenderer->getOptions());
        $value = <<<HTML
<button type="button" class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
  {$icon}
</button>
<ul class="dropdown-menu dropdown-menu-end">
  {$options}
</ul>
HTML;

        $this->optionRenderer->clearOptions();

        return $value;
    }

    /**
     * @param array<string, mixed> $dbResultRow
     */
    private function getEditRoute(array $dbResultRow, string $resourcePathEdit): string
    {
        if (!str_contains($resourcePathEdit, '%s')) {
            return $resourcePathEdit . 'id_' . $dbResultRow[$this->getPrimaryKey()];
        }

        return \sprintf($resourcePathEdit, $dbResultRow[$this->getPrimaryKey()]);
    }
}
