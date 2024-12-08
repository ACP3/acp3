<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Search\ViewProviders;

use ACP3\Core\Breadcrumb\Steps;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;

class SearchResultsViewProvider
{
    public function __construct(private readonly RequestInterface $request, private readonly Steps $breadcrumb, private readonly Translator $translator)
    {
    }

    /**
     * @param array<string, array<string, mixed>[]> $searchResults
     *
     * @return array<string, mixed>
     */
    public function __invoke(
        array $searchResults,
        string $searchTerm,
    ): array {
        $this->breadcrumb
            ->append($this->translator->t('search', 'search'), 'search')
            ->append(
                $this->translator->t('search', 'search_results'),
                $this->request->getQuery()
            );

        return [
            'results_mods' => $searchResults,
            'search_term' => $searchTerm,
        ];
    }
}
