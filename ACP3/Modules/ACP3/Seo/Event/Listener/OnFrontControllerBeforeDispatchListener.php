<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Seo\Event\Listener;


use ACP3\Core\Application\Event\FrontControllerDispatchEvent;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\RouterInterface;
use ACP3\Core\SEO;
use ACP3\Modules\ACP3\Seo\Core\Router\Aliases;

class OnFrontControllerBeforeDispatchListener
{
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;
    /**
     * @var \ACP3\Core\RouterInterface
     */
    protected $router;
    /**
     * @var \ACP3\Modules\ACP3\Seo\Core\Router\Aliases
     */
    protected $aliases;
    /**
     * @var \ACP3\Core\SEO
     */
    protected $seo;

    /**
     * OnFrontControllerBeforeDispatchListener constructor.
     *
     * @param \ACP3\Core\Http\RequestInterface           $request
     * @param \ACP3\Core\RouterInterface                 $router
     * @param \ACP3\Modules\ACP3\Seo\Core\Router\Aliases $aliases
     * @param \ACP3\Core\SEO                             $seo
     */
    public function __construct(
        RequestInterface $request,
        RouterInterface $router,
        Aliases $aliases,
        SEO $seo
    ) {
        $this->request = $request;
        $this->seo = $seo;
        $this->router = $router;
        $this->aliases = $aliases;
    }

    /**
     * If there is an URI alias available, set the alias as the canonical URI
     *
     * @param \ACP3\Core\Application\Event\FrontControllerDispatchEvent $event
     */
    public function onFrontControllerBeforeDispatch(FrontControllerDispatchEvent $event)
    {
        if ($this->isInFrontend($event) && $this->uriAliasExists()) {
            $this->seo->setCanonicalUri($this->router->route($this->request->getQuery()));
        }
    }

    /**
     * @param \ACP3\Core\Application\Event\FrontControllerDispatchEvent $event
     *
     * @return bool
     */
    private function isInFrontend(FrontControllerDispatchEvent $event)
    {
        return $event->getControllerArea() === AreaEnum::AREA_FRONTEND
        && $this->request->getArea() === AreaEnum::AREA_FRONTEND;
    }

    /**
     * @return bool
     */
    private function uriAliasExists()
    {
        return $this->aliases->uriAliasExists($this->request->getQuery()) === true
        && $this->request->getOriginalQuery() !== $this->aliases->getUriAlias($this->request->getQuery()) . '/';
    }
}
