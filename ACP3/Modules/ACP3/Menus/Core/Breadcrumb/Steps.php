<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Core\Breadcrumb;

use ACP3\Core;
use ACP3\Core\Http\RequestInterface;
use ACP3\Modules\ACP3\Menus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Steps extends Core\Breadcrumb\Steps
{
    /**
     * @var \ACP3\Modules\ACP3\Menus\Model\Repository\MenuItemRepository
     */
    protected $menuItemRepository;
    /**
     * @var array
     */
    protected $stepsFromDb = [];

    /**
     * Breadcrumb constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface    $container
     * @param \ACP3\Core\I18n\Translator                                   $translator
     * @param \ACP3\Core\Http\RequestInterface                             $request
     * @param \ACP3\Core\Router\RouterInterface                            $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface  $eventDispatcher
     * @param \ACP3\Modules\ACP3\Menus\Model\Repository\MenuItemRepository $menuItemRepository
     */
    public function __construct(
        ContainerInterface $container,
        Core\I18n\Translator $translator,
        RequestInterface $request,
        Core\Router\RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        Menus\Model\Repository\MenuItemRepository $menuItemRepository
    ) {
        parent::__construct($container, $translator, $request, $router, $eventDispatcher);

        $this->menuItemRepository = $menuItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceAncestor($title, $path = '', $dbSteps = false)
    {
        if ($dbSteps === true) {
            \end($this->stepsFromDb);
            $this->stepsFromDb[(int) \key($this->stepsFromDb)] = $this->buildStepItem($title, $path);
        }

        return parent::replaceAncestor($title, $path, $dbSteps);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildBreadcrumbCacheForFrontend()
    {
        parent::buildBreadcrumbCacheForFrontend();

        if (empty($this->stepsFromDb)) {
            $this->prePopulate();
        }

        if (!empty($this->stepsFromDb)) {
            $offset = $this->findFirstMatchingStep();

            $this->breadcrumbCache = \array_merge($this->stepsFromDb, \array_slice($this->steps, $offset));
        }
    }

    /**
     * Initializes and pre populates the breadcrumb.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function prePopulate()
    {
        $items = $this->menuItemRepository->getMenuItemsByUri($this->getPossiblyMatchingRoutes());

        $matches = $this->findRestrictionInRoutes($items);
        if (!empty($matches)) {
            list($leftId, $rightId) = $matches;

            foreach ($items as $item) {
                if ($item['left_id'] <= $leftId && $item['right_id'] >= $rightId) {
                    $this->appendFromDB($item['title'], $item['uri']);
                }
            }
        }
    }

    private function getPossiblyMatchingRoutes()
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
     * @param array $items
     *
     * @return array
     */
    private function findRestrictionInRoutes(array $items)
    {
        \rsort($items);
        foreach ($items as $index => $item) {
            if (\in_array($item['uri'], $this->getPossiblyMatchingRoutes())) {
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
     * @param string $title
     * @param string $path
     *
     * @return $this
     */
    private function appendFromDB($title, $path = '')
    {
        $this->stepsFromDb[] = $this->buildStepItem($title, $path);

        return $this;
    }

    /**
     * @return int
     */
    private function findFirstMatchingStep(): int
    {
        $steps = \array_reverse($this->steps);
        $lastDbStep = \end($this->stepsFromDb);

        $offset = 0;
        foreach ($steps as $index => $step) {
            if ($step['uri'] === $lastDbStep['uri']) {
                $offset = $index;

                break;
            }
        }

        return \count($steps) - $offset;
    }
}
