<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Search\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Search;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class IndexPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Core\ACL $acl,
        private readonly Core\Helpers\Secure $secureHelper,
        private readonly Search\Helpers $searchHelpers,
        private readonly Search\Validation\FormValidation $searchValidator,
        private readonly Search\Utility\SearchAvailabilityRegistrar $availableModulesRegistrar,
        private readonly Search\ViewProviders\SearchResultsViewProvider $searchResultsViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(?string $searchTerm = ''): array|string|Response
    {
        return $this->actionHelper->handlePostAction(
            function () use ($searchTerm) {
                $formData = $this->prepareFormData(array_merge(['search_term' => $searchTerm], $this->request->getPost()->all()));

                $this->searchValidator->validate($formData);

                $this->setTemplate('Search/Frontend/index.results.tpl');

                $searchTerm = $this->secureHelper->strEncode($formData['search_term']);

                return ($this->searchResultsViewProvider)(
                    $this->processSearchResults(
                        $formData['mods'],
                        $searchTerm,
                        Search\Enum\SearchAreaEnum::tryFrom($formData['area']),
                        Search\Enum\SortDirectionEnum::tryFrom($formData['sort'])
                    ),
                    $searchTerm
                );
            }
        );
    }

    /**
     * @param array<string, mixed> $formData
     *
     * @return array<string, mixed>
     */
    private function prepareFormData(array $formData): array
    {
        if (isset($formData['search_term']) === true) {
            if (isset($formData['mods']) === false) {
                $modules = $this->searchHelpers->getModules();

                $formData['mods'] = [];
                foreach ($modules as $row) {
                    $formData['mods'][] = $row['name'];
                }
            }
            if (isset($formData['area']) === false) {
                $formData['area'] = 'title_content';
            }
            if (isset($formData['sort']) === false) {
                $formData['sort'] = 'asc';
            }
        }

        return $formData;
    }

    /**
     * @param string[] $modules
     *
     * @return array<string, array<string, mixed>[]>
     */
    private function processSearchResults(array $modules, string $searchTerm, Search\Enum\SearchAreaEnum $area, Search\Enum\SortDirectionEnum $sort): array
    {
        $searchResults = [];
        foreach ($this->availableModulesRegistrar->getAvailableModules() as $moduleName => $searchAvailability) {
            if (\in_array($moduleName, $modules, true) && $this->acl->hasPermission('frontend/' . $moduleName)) {
                $results = $searchAvailability->fetchSearchResults($searchTerm, $area, $sort);

                if (!empty($results)) {
                    $searchResults[$moduleName] = $results;
                }
            }
        }
        ksort($searchResults);

        return $searchResults;
    }
}
