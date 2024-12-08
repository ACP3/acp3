<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Core\Breadcrumb;

use ACP3\Core\Breadcrumb\Event\StepsBuildCacheEvent;
use ACP3\Core\Breadcrumb\Steps as CoreSteps;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;
use ACP3\Modules\ACP3\Menus\Repository\MenuItemRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Steps extends CoreSteps
{
    /**
     * @var array<array<string, mixed>>
     */
    protected array $stepsFromDb = [];

    public function __construct(
        ContainerInterface $container,
        Translator $translator,
        RequestInterface $request,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        protected readonly MenuItemRepository $menuItemRepository,
    ) {
        parent::__construct($container, $translator, $request, $router, $eventDispatcher);
    }

    public function replaceAncestor(string $title, string $path = '', bool $dbSteps = false): self
    {
        if ($dbSteps === true) {
            $this->stepsFromDb[(int) array_key_last($this->stepsFromDb)] = $this->buildStepItem($title, $path);
        }

        parent::replaceAncestor($title, $path, $dbSteps);

        return $this;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function buildBreadcrumbCacheForFrontend(): void
    {
        parent::buildBreadcrumbCacheForFrontend();

        if (empty($this->stepsFromDb)) {
            $this->prePopulate();
        }

        $this->eventDispatcher->dispatch(
            new StepsBuildCacheEvent($this),
            'menus.breadcrumb.steps.build_frontend_cache_after'
        );

        if (!empty($this->stepsFromDb)) {
            $offset = $this->findFirstMatchingStep();

            $this->breadcrumbCache = array_merge($this->stepsFromDb, \array_slice($this->steps, $offset));
        }
    }

    /**
     * Initializes and pre populates the breadcrumb.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function prePopulate(): void
    {
        $items = $this->menuItemRepository->getMenuItemsByUri($this->getPossiblyMatchingRoutes());

        $matches = $this->findRestrictionInRoutes($items);
        if (!empty($matches)) {
            [$leftId, $rightId] = $matches;

            foreach ($items as $item) {
                if ($item['left_id'] <= $leftId && $item['right_id'] >= $rightId) {
                    $this->appendFromDB($item['title'], $item['uri']);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function getPossiblyMatchingRoutes(): array
    {
        return [
            $this->request->getQuery(),
            $this->request->getUriWithoutPages(),
            $this->request->getFullPath(),
            $this->request->getModuleAndController(),
            $this->request->getModule(),
        ];
    }

    /**
     * @param array<array<string, mixed>> $items
     *
     * @return int[]
     */
    private function findRestrictionInRoutes(array $items): array
    {
        rsort($items);
        foreach ($items as $item) {
            if (\in_array($item['uri'], $this->getPossiblyMatchingRoutes(), true)) {
                return [
                    $item['left_id'],
                    $item['right_id'],
                ];
            }
        }

        return [];
    }

    /**
     * Zuweisung einer neuen Stufe zur Brotkrümelspur.
     *
     * @return static
     */
    private function appendFromDB(string $title, string $path = ''): self
    {
        $this->stepsFromDb[] = $this->buildStepItem($title, $path);

        return $this;
    }

    private function findFirstMatchingStep(): int
    {
        $steps = array_reverse($this->steps);
        $lastDbStep = end($this->stepsFromDb);

        $matched = false;
        $offset = 0;
        foreach ($steps as $index => $step) {
            if ($step['uri'] === $lastDbStep['uri']) {
                $matched = true;
                $offset = $index;

                break;
            }
        }

        return $this->hasUseIndex($matched, $lastDbStep['uri']) ? \count($steps) - $offset : 0;
    }

    private function hasUseIndex(bool $matched, string $uri): bool
    {
        return $matched === true || $uri === $this->router->route($this->request->getQuery(), true);
    }

    public function removeByPath(string $path): self
    {
        parent::removeByPath($path);

        $path = $this->router->route($path, true);

        $this->stepsFromDb = array_filter(
            $this->stepsFromDb,
            static fn (array $step) => $step['uri'] !== $path
        );

        return $this;
    }
}
