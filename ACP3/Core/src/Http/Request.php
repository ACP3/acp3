<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Http;

use ACP3\Core\Controller\AreaEnum;

class Request extends AbstractRequest
{
    private const ADMIN_PANEL_PATTERN = 'acp/';
    private const WIDGET_PATTERN = 'widget/';
    private const FRONTEND_PATTERN = 'frontend/';

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
     */
    public function __construct(\Symfony\Component\HttpFoundation\Request $symfonyRequest)
    {
        parent::__construct($symfonyRequest);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->symfonyRequest->attributes->get('_area');
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->symfonyRequest->attributes->get('_module');
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->symfonyRequest->attributes->get('_controller');
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->symfonyRequest->attributes->get('_controllerAction');
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPath()
    {
        return $this->getModuleAndController() . $this->getAction() . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getFullPathWithoutArea()
    {
        return $this->getModuleAndControllerWithoutArea() . $this->getAction() . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleAndController()
    {
        $path = ($this->getArea() === AreaEnum::AREA_ADMIN) ? 'acp/' : '';
        $path .= $this->getModuleAndControllerWithoutArea();

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleAndControllerWithoutArea()
    {
        return $this->getModule() . '/' . $this->getController() . '/';
    }

    /**
     * Processes the URL of the current request.
     */
    public function processQuery()
    {
        $this->query = $this->pathInfo;

        // It's an request for the admin panel page
        if (\strpos($this->query, self::ADMIN_PANEL_PATTERN) === 0) {
            $this->symfonyRequest->attributes->set('_area', AreaEnum::AREA_ADMIN);
            // strip "acp/"
            $this->query = \substr($this->query, \strlen(self::ADMIN_PANEL_PATTERN));
        } elseif (\strpos($this->query, self::WIDGET_PATTERN) === 0) {
            $this->symfonyRequest->attributes->set('_area', AreaEnum::AREA_WIDGET);

            // strip "widget/"
            $this->query = \substr($this->query, \strlen(self::WIDGET_PATTERN));
        } else {
            if (\strpos($this->query, self::FRONTEND_PATTERN) === 0) {
                $this->query = \substr($this->query, \strlen(self::FRONTEND_PATTERN));
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
     * Setzt alle in URI::query enthaltenen Parameter.
     */
    protected function parseURI()
    {
        $query = \preg_split('=/=', $this->query, -1, PREG_SPLIT_NO_EMPTY);

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
            $query[1] ?? 'index'
        );
        $this->symfonyRequest->attributes->set(
            '_controllerAction',
            $query[2] ?? 'index'
        );

        $this->completeQuery($query);
        $this->setRequestParameters($query);
    }

    /**
     * {@inheritdoc}
     */
    public function isHomepage()
    {
        return ($this->query === $this->homepage) && $this->getArea() === AreaEnum::AREA_FRONTEND;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->symfonyRequest->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getUriWithoutPages()
    {
        return \preg_replace('/\/page_(\d+)\//', '/', $this->query);
    }

    /**
     * {@inheritdoc}
     */
    public function setPathInfo(?string $pathInfo = null): void
    {
        if ($pathInfo !== null) {
            $this->pathInfo = $pathInfo;
        } else {
            $this->pathInfo = \substr($this->symfonyRequest->getPathInfo(), 1);
        }

        $this->pathInfo .= !\preg_match('/\/$/', $this->pathInfo) ? '/' : '';
    }

    protected function setRequestParameters(array $query)
    {
        if (isset($query[3])) {
            $cQuery = \count($query);

            for ($i = 3; $i < $cQuery; ++$i) {
                if (\preg_match('/^(page_(\d+))$/', $query[$i])) { // Current page
                    $this->symfonyRequest->attributes->add(['page' => (int) \substr($query[$i], 5)]);
                } elseif (\preg_match('/^(id_(\d+))$/', $query[$i])) { // result ID
                    $this->symfonyRequest->attributes->add(['id' => (int) \substr($query[$i], 3)]);
                } elseif (\preg_match('/^(([a-zA-Z0-9\-]+)_(.+))$/', $query[$i])) { // Additional URI parameters
                    $param = \explode('_', $query[$i], 2);
                    $this->symfonyRequest->attributes->add([$param[0] => $param[1]]);
                }
            }
        }

        $this->symfonyRequest->attributes->set(
            'cat',
            (int) $this->getPost()->get('cat', $this->symfonyRequest->attributes->get('cat'))
        );
        $this->symfonyRequest->attributes->set(
            'action',
            $this->getPost()->get('action', $this->symfonyRequest->attributes->get('action'))
        );
    }

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