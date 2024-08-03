<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Component;

use ACP3\Core\Component\Dto\ComponentDataDto;
use ACP3\Core\Component\Exception\ComponentNotFoundException;
use MJS\TopSort\Implementations\StringSort;

class ComponentRegistry
{
    /**
     * @var ComponentDataDto[]
     */
    private static array $components = [];

    /**
     * @var ComponentDataDto[]
     */
    private static ?array $componentsTopSorted = null;

    /**
     * Adds a new component with its name and its filesystem path to the component registry.
     */
    public static function add(ComponentDataDto $component): void
    {
        self::$components[] = $component;
    }

    /**
     * Return all currently registered components.
     *
     * @return ComponentDataDto[]
     */
    public static function all(): array
    {
        return self::$components;
    }

    /**
     * @return ComponentDataDto[]
     *
     * @throws ComponentNotFoundException
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public static function allTopSorted(): array
    {
        if (self::$componentsTopSorted !== null) {
            return self::$componentsTopSorted;
        }

        $topSort = new StringSort();

        $components = self::all();

        self::$componentsTopSorted = [];
        foreach ($components as $component) {
            $dependencies = array_map(static function (string $componentName) {
                $coreData = self::findByName($componentName);

                return $coreData ? $coreData->getPath() : null;
            }, $component->getDependencies());

            $topSort->add($component->getPath(), $dependencies);
        }

        foreach ($topSort->sort() as $componentPath) {
            self::$componentsTopSorted[] = self::findByPath($componentPath);
        }

        return self::$componentsTopSorted;
    }

    /**
     * @param ComponentDataDto[]  $components
     * @param ComponentTypeEnum[] $componentTypes
     *
     * @return ComponentDataDto[]
     */
    public static function filterByType(array $components, array $componentTypes): array
    {
        return array_filter($components, static fn (ComponentDataDto $component) => \in_array($component->getComponentType(), $componentTypes, true));
    }

    /**
     * @param ComponentDataDto[]  $components
     * @param ComponentTypeEnum[] $componentTypes
     *
     * @return ComponentDataDto[]
     */
    public static function excludeByType(array $components, array $componentTypes): array
    {
        return array_filter($components, static fn (ComponentDataDto $component) => !\in_array($component->getComponentType(), $componentTypes, true));
    }

    private static function findByName(string $componentName): ?ComponentDataDto
    {
        $componentName = strtolower($componentName);
        $filteredComponents = array_filter(self::$components, static fn (ComponentDataDto $component) => $component->getName() === $componentName);

        return reset($filteredComponents) ?: null;
    }

    private static function findByPath(string $componentPath): ?ComponentDataDto
    {
        $filteredComponents = array_filter(self::$components, static fn (ComponentDataDto $component) => $component->getPath() === $componentPath);

        return reset($filteredComponents) ?: null;
    }

    /**
     * Returns the filesystem path of the given component.
     * If the component isn't registered it throws an exception.
     *
     * @throws ComponentNotFoundException
     */
    public static function getPathByName(string $componentName): string
    {
        $component = self::findByName($componentName);

        if ($component === null) {
            throw new ComponentNotFoundException(\sprintf('Could not find the component with name "%s".', $componentName));
        }

        return $component->getPath();
    }
}
