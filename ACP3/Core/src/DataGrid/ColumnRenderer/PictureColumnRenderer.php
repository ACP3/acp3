<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer;

use ACP3\Core\Router\RouterInterface;

class PictureColumnRenderer extends AbstractColumnRenderer
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    protected function getValue(array $column, array $dbResultRow): ?string
    {
        $field = $this->getFirstDbField($column);
        $value = $this->getDbValueIfExists($dbResultRow, $field);

        if ($value === null) {
            $value = $this->getDefaultValue($column);
        } elseif (isset($column['custom']['pattern'])) {
            $value = '<img src="' . $this->getUrl($column['custom'], $value) . '" loading="lazy" alt="">';
        } elseif (isset($column['custom']['callback']) && \is_callable($column['custom']['callback'])) {
            $value = '<img src="' . $column['custom']['callback']($value) . '" loading="lazy" alt="">';
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function getUrl(array $data, string $value): string
    {
        $url = \sprintf($data['pattern'], $value);
        if (isset($data['isRoute']) && $data['isRoute'] === true) {
            return $this->router->route($url);
        }

        return $url;
    }
}
