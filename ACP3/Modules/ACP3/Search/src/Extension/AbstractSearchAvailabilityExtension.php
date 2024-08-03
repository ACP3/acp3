<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Search\Extension;

use ACP3\Core\Router\RouterInterface;
use ACP3\Core\View;
use ACP3\Modules\ACP3\Search\Enum\SearchAreaEnum;
use ACP3\Modules\ACP3\Search\Enum\SortDirectionEnum;
use ACP3\Modules\ACP3\Search\Repository\SearchResultsAwareRepositoryInterface;

abstract class AbstractSearchAvailabilityExtension implements SearchAvailabilityExtensionInterface
{
    public function __construct(protected RouterInterface $router, protected SearchResultsAwareRepositoryInterface $repository, private readonly View $view)
    {
    }

    public function fetchSearchResults(string $searchTerm, SearchAreaEnum $areas, SortDirectionEnum $sortDirection): array
    {
        $results = $this->repository->getAllSearchResults(
            $areas,
            $searchTerm,
            $sortDirection
        );

        foreach ($results as $i => $iValue) {
            $results[$i]['hyperlink'] = $this->router->route(\sprintf($this->getRouteName(), $iValue['id']));
            $results[$i]['text'] = $this->view->fetchStringAsTemplate($results[$i]['text']);
        }

        return $results;
    }

    abstract protected function getRouteName(): string;
}
