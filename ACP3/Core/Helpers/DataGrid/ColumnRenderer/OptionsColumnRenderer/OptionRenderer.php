<?php
namespace ACP3\Core\Helpers\DataGrid\ColumnRenderer\OptionsColumnRenderer;

use ACP3\Core\Lang;
use ACP3\Core\Router;

/**
 * Class OptionsRenderer
 * @package ACP3\Core\Helpers\DataGrid\ColumnRenderer\OptionsColumnRenderer
 */
class OptionRenderer
{
    /**
     * @var \ACP3\Core\Router
     */
    protected $router;
    /**
     * @var array
     */
    protected $options = [];

    /**
     * OptionsRenderer constructor.
     *
     * @param \ACP3\Core\Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $route
     * @param string $translationPhrase
     * @param string $iconClass
     * @param string $buttonClass
     * @param bool   $useAjax
     */
    public function addOption($route, $translationPhrase, $iconClass, $buttonClass = 'btn-default', $useAjax = false)
    {
        $ajax = $useAjax === true ? ' data-ajax-form="true"' : '';
        $value = ' <a href="' . $this->router->route($route) . '" class="btn btn-xs btn-block ' . $buttonClass . '"' . $ajax . '>';
        $value .= '<i class="glyphicon ' . $iconClass . '"></i> ' . $translationPhrase;
        $value .= '</a>';

        $this->options[] = $value;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function clearOptions()
    {
        $this->options = [];
    }
}