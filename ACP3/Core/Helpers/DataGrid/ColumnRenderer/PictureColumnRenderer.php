<?php
namespace ACP3\Core\Helpers\DataGrid\ColumnRenderer;

use ACP3\Core\Router\RouterInterface;

class PictureColumnRenderer extends AbstractColumnRenderer
{
    /**
     * @var \ACP3\Core\Router\RouterInterface
     */
    protected $router;

    /**
     * PictureColumnRenderer constructor.
     *
     * @param \ACP3\Core\Router\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    protected function getValue(array $column, array $dbResultRow)
    {
        $field = $this->getFirstDbField($column);
        $value = $this->getDbValueIfExists($dbResultRow, $field);

        if ($value === null) {
            $value = $this->getDefaultValue($column);
        } elseif (isset($column['custom']['pattern'])) {
            $value = '<img src="' . $this->getUrl($column['custom'], $value) . '" alt="">';
        }

        return $value;
    }

    /**
     * @param array  $data
     * @param string $value
     *
     * @return string
     */
    protected function getUrl(array $data, $value)
    {
        $url = sprintf($data['pattern'], $value);
        if (isset($data['isRoute'])) {
            return $this->router->route($url);
        }

        return $url;
    }
}
