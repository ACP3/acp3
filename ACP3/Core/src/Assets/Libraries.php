<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets;

use ACP3\Core\Assets\Entity\LibraryEntity;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\StringSort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Libraries
{
    /**
     * @var array<string, LibraryEntity>
     */
    private array $libraries = [];

    public function __construct(private readonly RequestStack $requestStack, private readonly LibrariesCache $librariesCache)
    {
    }

    /**
     * @return array<string, LibraryEntity>
     *
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    public function getLibraries(): array
    {
        if (!$this->libraries) {
            return [];
        }

        $topSort = new StringSort();
        foreach ($this->libraries as $libraryName => $options) {
            $topSort->add($libraryName, $options->getDependencies());
        }

        $librariesTopSorted = [];
        foreach ($topSort->sort() as $libraryName) {
            $librariesTopSorted[$libraryName] = $this->libraries[$libraryName];
        }

        return $librariesTopSorted;
    }

    /**
     * @param array{enabled_for_ajax?: bool, dependencies?: string[], css?: string[], js?: string[], module?: string}|null $options
     *
     * @return $this
     */
    public function addLibrary(LibraryEntity|string $library, ?array $options = null): self
    {
        // @deprecated since ACP3 version 6.14.0, to be removed with version 7.0.0.
        if (\is_string($library)) {
            if (empty($options)) {
                throw new \InvalidArgumentException(\sprintf('You need to pass a valid options array for this asset library %s', $library));
            }

            $library = new LibraryEntity(
                $library,
                $options['enabled_for_ajax'] ?? false,
                $options['dependencies'] ?? [],
                $options['css'] ?? [],
                $options['js'] ?? [],
                $options['module'] ?? null
            );
        }

        if (!isset($this->libraries[$library->getLibraryIdentifier()])) {
            $this->libraries[$library->getLibraryIdentifier()] = $library;
        }

        return $this;
    }

    /**
     * Activates frontend libraries.
     *
     * @param string[] $libraries
     */
    public function enableLibraries(array $libraries): self
    {
        foreach ($libraries as $libraryIdentifier) {
            if (\array_key_exists($libraryIdentifier, $this->libraries) === false) {
                throw new \InvalidArgumentException(\sprintf('Could not find library %s', $libraryIdentifier));
            }

            // Resolve javascript library dependencies recursively
            if (!empty($this->libraries[$libraryIdentifier]->getDependencies())) {
                $this->enableLibraries($this->libraries[$libraryIdentifier]->getDependencies());
            }

            // Enable the javascript library
            $this->libraries[$libraryIdentifier] = $this->libraries[$libraryIdentifier]->enable();

            if ($this->requestStack->getCurrentRequest()) {
                $this->librariesCache->scheduleStoreEnabledLibraryInCache($this->requestStack->getCurrentRequest(), $libraryIdentifier);
            }
        }

        return $this;
    }

    /**
     * @return array<string, LibraryEntity>
     *
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    public function getEnabledLibraries(): array
    {
        $enabledLibraries = [];
        foreach ($this->getLibraries() as $libraryName => $options) {
            if ($this->includeInXmlHttpRequest($options)) {
                continue;
            }
            if ($options->isEnabled() === false) {
                continue;
            }

            $enabledLibraries[$libraryName] = $options;
        }

        return $enabledLibraries;
    }

    /**
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    public function getEnabledLibrariesAsString(): string
    {
        return implode(',', array_keys($this->getEnabledLibraries()));
    }

    private function getMainRequest(): Request
    {
        return $this->requestStack->getMainRequest();
    }

    private function includeInXmlHttpRequest(LibraryEntity $library): bool
    {
        return $this->getMainRequest()->isXmlHttpRequest()
            && $library->isEnabledForAjax() === false;
    }
}
