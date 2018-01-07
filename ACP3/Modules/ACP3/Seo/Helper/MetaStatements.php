<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Helper;

use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Modules\Modules;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage as SeoCache;
use ACP3\Modules\ACP3\Seo\Installer\Schema;

class MetaStatements
{
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var \ACP3\Core\Settings\SettingsInterface
     */
    protected $config;
    /**
     * @var \ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage
     */
    protected $seoCache;

    /**
     * @var null|array
     */
    protected $aliasesCache;
    /**
     * @var string
     */
    protected $nextPage = '';
    /**
     * @var string
     */
    protected $previousPage = '';
    /**
     * @var string
     */
    protected $canonicalUrl = '';
    /**
     * @var string
     */
    protected $metaDescriptionPostfix = '';
    /**
     * @var string
     */
    private $metaRobots = '';
    /**
     * @var bool
     */
    private $isActive;

    /**
     * MetaStatements constructor.
     *
     * @param \ACP3\Core\Http\RequestInterface             $request
     * @param RouterInterface                              $router
     * @param Modules                                      $modules
     * @param \ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage $seoCache
     * @param \ACP3\Core\Settings\SettingsInterface        $config
     */
    public function __construct(
        RequestInterface $request,
        RouterInterface $router,
        Modules $modules,
        SeoCache $seoCache,
        SettingsInterface $config
    ) {
        $this->request = $request;
        $this->seoCache = $seoCache;
        $this->config = $config;
        $this->isActive = $modules->isActive(Schema::MODULE_NAME);
        $this->router = $router;
    }

    /**
     * @param string $metaRobots
     *
     * @return $this
     */
    public function setPageRobotsSettings($metaRobots)
    {
        $this->metaRobots = $metaRobots;

        return $this;
    }

    /**
     * Returns the meta tags of the current page.
     *
     * @return array
     */
    public function getMetaTags()
    {
        if ($this->isActive) {
            if (!$this->isInAdmin() && empty($this->canonicalUrl)) {
                $this->canonicalUrl = $this->router->route($this->request->getQuery());
            }

            return [
                'description' => $this->isInAdmin() ? '' : $this->getPageDescription(),
                'keywords' => $this->isInAdmin() ? '' : $this->getPageKeywords(),
                'robots' => $this->isInAdmin() ? 'noindex,nofollow' : $this->getPageRobotsSetting(),
                'previous_page' => $this->previousPage,
                'next_page' => $this->nextPage,
                'canonical' => $this->canonicalUrl,
            ];
        }

        return [];
    }

    /**
     * @return bool
     */
    private function isInAdmin()
    {
        return $this->request->getArea() === AreaEnum::AREA_ADMIN;
    }

    /**
     * Returns the SEO description of the current page.
     *
     * @return string
     */
    public function getPageDescription()
    {
        $description = $this->getDescription($this->request->getUriWithoutPages());
        if (empty($description)) {
            $description = $this->getDescription($this->request->getFullPath());
        }
        if (empty($description)) {
            $description = $this->getDescription($this->request->getModule());
        }
        if (empty($description)) {
            $description = $this->getSeoSettings()['meta_description'];
        }

        $postfix = '';
        if (!empty($description) && !empty($this->metaDescriptionPostfix)) {
            $postfix .= ' - ' . $this->metaDescriptionPostfix;
        }

        return $description . $postfix;
    }

    /**
     * @return array
     */
    protected function getSeoSettings()
    {
        return $this->config->getSettings(Schema::MODULE_NAME);
    }

    /**
     * Returns the SEO description of the given page.
     *
     * @param string $path
     *
     * @return string
     */
    public function getDescription($path)
    {
        return $this->getSeoInformation($path, 'description');
    }

    /**
     * Returns the SEO keywords of the current page.
     *
     * @return string
     */
    public function getPageKeywords()
    {
        $keywords = $this->getKeywords($this->request->getUriWithoutPages());
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->getFullPath());
        }
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->getModule());
        }

        return \strtolower(!empty($keywords) ? $keywords : $this->getSeoSettings()['meta_keywords']);
    }

    /**
     * Returns the SEO keywords of the given page.
     *
     * @param string $path
     *
     * @return string
     */
    public function getKeywords($path)
    {
        return $this->getSeoInformation($path, 'keywords');
    }

    /**
     * Returns the meta title of the given page.
     *
     * @param string $path
     *
     * @return string
     */
    public function getTitle($path)
    {
        return $this->getSeoInformation($path, 'title');
    }

    /**
     * @param string $path
     * @param string $key
     * @param string $defaultValue
     *
     * @return string
     */
    public function getSeoInformation($path, $key, $defaultValue = '')
    {
        if (!$this->isActive) {
            return '';
        }

        // Lazy load the cache
        if ($this->aliasesCache === null) {
            $this->aliasesCache = $this->seoCache->getCache();
        }

        $path .= !\preg_match('/\/$/', $path) ? '/' : '';

        return !empty($this->aliasesCache[$path][$key]) ? $this->aliasesCache[$path][$key] : $defaultValue;
    }

    /**
     * Returns the SEO robots setting for the current page.
     *
     * @return string
     */
    public function getPageRobotsSetting()
    {
        if (!empty($this->metaRobots)) {
            return $this->metaRobots;
        }

        $robots = $this->getRobotsSetting($this->request->getUriWithoutPages());
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->getFullPath());
        }
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->getModule());
        }

        return \strtolower(!empty($robots) ? $robots : $this->getRobotsSetting());
    }

    /**
     * Returns the SEO robots settings for the given page.
     *
     * @param string $path
     *
     * @return string
     */
    public function getRobotsSetting($path = '')
    {
        $replace = [
            1 => 'index,follow',
            2 => 'index,nofollow',
            3 => 'noindex,follow',
            4 => 'noindex,nofollow',
        ];

        if ($path === '') {
            return \strtr($this->getSeoSettings()['robots'], $replace);
        }

        $robot = $this->getSeoInformation($path, 'robots', 0);

        if ($robot == 0) {
            $robot = $this->getSeoSettings()['robots'];
        }

        return \strtr($robot, $replace);
    }

    /**
     * Sets a SEO description postfix for te current page.
     *
     * @param string $string
     *
     * @return $this
     */
    public function setDescriptionPostfix($string)
    {
        $this->metaDescriptionPostfix = $string;

        return $this;
    }

    /**
     * Sets the canonical URL for the current page.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setCanonicalUri($path)
    {
        $this->canonicalUrl = $path;

        return $this;
    }

    /**
     * Sets the next page (useful for pagination).
     *
     * @param string $path
     *
     * @return $this
     */
    public function setNextPage($path)
    {
        $this->nextPage = $path;

        return $this;
    }

    /**
     * Sets the previous page (useful for pagination).
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPreviousPage($path)
    {
        $this->previousPage = $path;

        return $this;
    }
}
