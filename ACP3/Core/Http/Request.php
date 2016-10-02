<?php
namespace ACP3\Core\Http;

use ACP3\Core\Controller\AreaEnum;

/**
 * Class Request
 * @package ACP3\Core\Http
 */
class Request extends AbstractRequest
{
    const ADMIN_PANEL_PATTERN = '=^acp/=';
    const WIDGET_PATTERN = '=^widget/=';

    /**
     * @var string
     */
    protected $query = '';
    /**
     * @var string
     */
    protected $pathInfo = '';

    /**
     * Request constructor.
     *
     * @param \Symfony\Component\HttpFoundation\Request $symfonyRequest
     */
    public function __construct(\Symfony\Component\HttpFoundation\Request $symfonyRequest)
    {
        parent::__construct($symfonyRequest);
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * @inheritdoc
     */
    public function getArea()
    {
        return $this->symfonyRequest->attributes->get('_area');
    }

    /**
     * @inheritdoc
     */
    public function getModule()
    {
        return $this->symfonyRequest->attributes->get('_module');
    }

    /**
     * @inheritdoc
     */
    public function getController()
    {
        return $this->symfonyRequest->attributes->get('_controller');
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->symfonyRequest->attributes->get('_controllerAction');
    }

    /**
     * @inheritdoc
     */
    public function getFullPath()
    {
        return $this->getModuleAndController() . $this->getAction() . '/';
    }

    /**
     * @inheritdoc
     */
    public function getFullPathWithoutArea()
    {
        return $this->getModuleAndControllerWithoutArea() . $this->getAction() . '/';
    }

    /**
     * @inheritdoc
     */
    public function getModuleAndController()
    {
        $path = ($this->getArea() === AreaEnum::AREA_ADMIN) ? 'acp/' : '';
        $path .= $this->getModuleAndControllerWithoutArea();

        return $path;
    }

    /**
     * @inheritdoc
     */
    public function getModuleAndControllerWithoutArea()
    {
        return $this->getModule() . '/' . $this->getController() . '/';
    }

    /**
     * Processes the URL of the current request
     */
    public function processQuery()
    {
        $this->setPathInfo();

        $this->query = $this->pathInfo;

        // It's an request for the admin panel page
        if (preg_match(self::ADMIN_PANEL_PATTERN, $this->query)) {
            $this->symfonyRequest->attributes->set('_area', AreaEnum::AREA_ADMIN);
            // strip "acp/"
            $this->query = substr($this->query, 4);
        } elseif (preg_match(self::WIDGET_PATTERN, $this->query)) {
            $this->symfonyRequest->attributes->set('_area', AreaEnum::AREA_WIDGET);

            // strip "widget/"
            $this->query = substr($this->query, 7);
        } else {
            if (strpos($this->query, 'frontend/') === 0) {
                $this->query = substr($this->query, 9);
            }

            $this->symfonyRequest->attributes->set('_area', AreaEnum::AREA_FRONTEND);

            // Set the user defined homepage of the website
            if ($this->query === '/' && $this->homepage !== '') {
                $this->query = $this->homepage;
            }
        }

        $this->parseURI();
    }

    /**
     * Setzt alle in URI::query enthaltenen Parameter
     */
    protected function parseURI()
    {
        $query = preg_split('=/=', $this->query, -1, PREG_SPLIT_NO_EMPTY);

        if (isset($query[0])) {
            $this->symfonyRequest->attributes->set('_module', $query[0]);
        } else {
            $this->symfonyRequest->attributes->set(
                '_module',
                ($this->getArea() === AreaEnum::AREA_ADMIN) ? 'acp' : 'news'
            );
        }

        $this->symfonyRequest->attributes->set(
            '_controller',
            isset($query[1]) ? $query[1] : 'index'
        );
        $this->symfonyRequest->attributes->set(
            '_controllerAction',
            isset($query[2]) ? $query[2] : 'index'
        );

        $this->completeQuery($query);
        $this->setRequestParameters($query);
    }

    /**
     * @inheritdoc
     */
    public function isHomepage()
    {
        return ($this->query === $this->homepage) && $this->getArea() === AreaEnum::AREA_FRONTEND;
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return $this->symfonyRequest->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getUriWithoutPages()
    {
        return preg_replace('/\/page_(\d+)\//', '/', $this->query);
    }

    protected function setPathInfo()
    {
        $this->pathInfo = substr($this->symfonyRequest->getPathInfo(), 1);
        $this->pathInfo .= !preg_match('/\/$/', $this->pathInfo) ? '/' : '';
    }

    /**
     * @param array $query
     */
    protected function setRequestParameters(array $query)
    {
        if (isset($query[3])) {
            $cQuery = count($query);

            for ($i = 3; $i < $cQuery; ++$i) {
                if (preg_match('/^(page_(\d+))$/', $query[$i])) { // Current page
                    $this->symfonyRequest->attributes->add(['page' => (int)substr($query[$i], 5)]);
                } elseif (preg_match('/^(id_(\d+))$/', $query[$i])) { // result ID
                    $this->symfonyRequest->attributes->add(['id' => (int)substr($query[$i], 3)]);
                } elseif (preg_match('/^(([a-zA-Z0-9-]+)_(.+))$/', $query[$i])) { // Additional URI parameters
                    $param = explode('_', $query[$i], 2);
                    $this->symfonyRequest->attributes->add([$param[0] => $param[1]]);
                }
            }
        }

        $this->symfonyRequest->attributes->set(
            'cat',
            (int)$this->getPost()->get('cat', $this->symfonyRequest->attributes->get('cat'))
        );
        $this->symfonyRequest->attributes->set(
            'action',
            $this->getPost()->get('action', $this->symfonyRequest->attributes->get('action'))
        );
    }

    /**
     * @param array $query
     */
    protected function completeQuery(array $query)
    {
        if (!isset($query[0])) {
            $this->query = $this->getModule() . '/';
        }
        if (!isset($query[1])) {
            $this->query .= $this->getController() . '/';
        }
        if (!isset($query[2])) {
            $this->query .= $this->getAction() . '/';
        }
    }
}
