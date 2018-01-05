<?php
namespace ACP3\Core\View\Renderer\Smarty\Filters;

use ACP3\Core\Assets;
use ACP3\Core\Http\RequestInterface;

class MoveToBottom extends AbstractMoveElementFilter
{
    const ELEMENT_CATCHER_REGEX_PATTERN = '!@@@SMARTY:JAVASCRIPTS:BEGIN@@@(.*?)@@@SMARTY:JAVASCRIPTS:END@@@!is';
    const PLACEHOLDER = '<!-- JAVASCRIPTS -->';

    /**
     * @var \ACP3\Core\Assets\Minifier\AbstractMinifier
     */
    protected $minifier;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;

    /**
     * @param \ACP3\Core\Assets\Minifier\MinifierInterface $minifier
     * @param \ACP3\Core\Http\RequestInterface    $request
     */
    public function __construct(
        Assets\Minifier\MinifierInterface $minifier,
        RequestInterface $request
    ) {
        $this->minifier = $minifier;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionName()
    {
        return 'output';
    }

    /**
     * @inheritdoc
     */
    public function process($tplOutput, \Smarty_Internal_Template $smarty)
    {
        if (strpos($tplOutput, static::PLACEHOLDER) !== false) {
            return str_replace(
                static::PLACEHOLDER,
                $this->addElementFromMinifier() . $this->addElementsFromTemplates($tplOutput),
                $this->getCleanedUpTemplateOutput($tplOutput)
            );
        }

        return $tplOutput;
    }

    /**
     * @return string
     */
    protected function addElementFromMinifier()
    {
        $minifyJs = '';
        if (!$this->request->isXmlHttpRequest()) {
            $minifyJs = '<script src="' . $this->minifier->getURI() . '"></script>' . "\n";
        }
        return $minifyJs;
    }
}
