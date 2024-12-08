<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets\Renderer\Strategies;

use ACP3\Core\Assets;
use ACP3\Core\Assets\Entity\LibraryEntity;
use ACP3\Core\Assets\FileResolver;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Environment\ThemePathInterface;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Modules;
use Psr\Cache\CacheItemPoolInterface;
use tubalmartin\CssMin\Minifier;

class ConcatCSSRendererStrategy extends AbstractConcatRendererStrategy implements CSSRendererStrategyInterface
{
    protected const ASSETS_PATH_CSS = 'Assets/css';

    /**
     * @var string[]
     */
    protected array $stylesheets = [];

    public function __construct(
        private readonly RequestInterface $request,
        private readonly Minifier $minifier,
        UserModelInterface $userModel,
        Assets $assets,
        Assets\Libraries $libraries,
        ApplicationPath $appPath,
        CacheItemPoolInterface $coreCachePool,
        Modules $modules,
        FileResolver $fileResolver,
        ThemePathInterface $themePath,
    ) {
        parent::__construct($request, $userModel, $assets, $libraries, $appPath, $coreCachePool, $modules, $fileResolver, $themePath);
    }

    protected function getAssetGroup(): string
    {
        return 'css';
    }

    protected function getFileExtension(): string
    {
        return 'css';
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    protected function getEnabledLibrariesAsString(): string
    {
        return implode(',', array_map(static fn (LibraryEntity $library) => $library->getLibraryIdentifier(), $this->getEnabledLibraries()));
    }

    /**
     * @return LibraryEntity[]
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    private function getEnabledLibraries(): array
    {
        return array_filter($this->libraries->getEnabledLibraries(), static fn (LibraryEntity $library) => $library->getCss() && !$library->isDeferrableCss());
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    protected function processLibraries(): array
    {
        $cacheId = $this->buildCacheId();
        $cacheItem = $this->coreCachePool->getItem($cacheId);

        if (!$cacheItem->isHit()) {
            $this->fetchLibraries();
            $this->fetchThemeStylesheets();
            $this->fetchModuleStylesheets();

            $cacheItem->set($this->stylesheets);
            $this->coreCachePool->saveDeferred($cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * Fetch all stylesheets of the enabled frontend frameworks/libraries.
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    private function fetchLibraries(): void
    {
        foreach ($this->getEnabledLibraries() as $library) {
            foreach ($library->getCss() as $stylesheet) {
                $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
                    $library->getModuleName(),
                    static::ASSETS_PATH_CSS,
                    $stylesheet
                );
            }
        }
    }

    /**
     * Fetches the theme stylesheets.
     */
    private function fetchThemeStylesheets(): void
    {
        foreach ($this->assets->fetchAdditionalThemeCssFiles() as $file) {
            $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
                'System',
                static::ASSETS_PATH_CSS,
                trim($file)
            );
        }

        $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
            'System',
            static::ASSETS_PATH_CSS,
            'layout.css'
        );
    }

    /**
     * Fetches the stylesheets of all currently enabled modules.
     */
    private function fetchModuleStylesheets(): void
    {
        $area = $this->request->getArea();

        foreach ($this->modules->getInstalledModules() as $module) {
            $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
                $module['name'],
                static::ASSETS_PATH_CSS,
                'style.css'
            );

            if ($area === AreaEnum::AREA_ADMIN) {
                $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
                    $module['name'],
                    static::ASSETS_PATH_CSS,
                    'admin.css'
                );
            }

            // Append custom styles to the default module styling
            $this->stylesheets[] = $this->fileResolver->getStaticAssetPath(
                $module['name'],
                static::ASSETS_PATH_CSS,
                'append.css'
            );
        }
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function renderHtmlElement(): string
    {
        return '<link rel="stylesheet" type="text/css" href="' . $this->getURI() . '">' . "\n";
    }

    protected function compress(string $assetContent): string
    {
        return $this->minifier->run($assetContent);
    }
}
