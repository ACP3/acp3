<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Pagination\Exception\InvalidPageException;
use ACP3\Core\Router\RouterInterface;

class Pagination
{
    private int $resultsPerPage = 0;

    private int $totalResults = 0;

    private string $urlFragment = '';

    private int $showFirstLast = 5;

    private int $showPreviousNext = 1;

    private int $pagesToDisplay = 3;

    protected int $totalPages = 1;

    protected int $currentPage = 1;
    /**
     * @var array<string, mixed>[]
     */
    private array $pagination = [];

    public function __construct(protected Title $title, protected Translator $translator, protected RequestInterface $request, protected RouterInterface $router)
    {
    }

    /**
     * @return static
     */
    public function setResultsPerPage(int $results): self
    {
        $this->resultsPerPage = $results;

        return $this;
    }

    /**
     * @return static
     */
    public function setTotalResults(int $results): self
    {
        $this->totalResults = $results;

        return $this;
    }

    /**
     * @return static
     */
    public function setUrlFragment(string $fragment): self
    {
        $this->urlFragment = $fragment;

        return $this;
    }

    /**
     * @return static
     */
    public function setPagesToDisplay(int $pagesToDisplay): self
    {
        $this->pagesToDisplay = $pagesToDisplay;

        return $this;
    }

    private function getPagesToDisplay(): int
    {
        $pagesToDisplay = $this->pagesToDisplay;

        $map = [
            $this->canShowNextPageLink(),
            $this->canShowPreviousPageLink(),
        ];

        foreach ($map as $result) {
            if (!$result) {
                ++$pagesToDisplay;
            }
        }

        return $pagesToDisplay;
    }

    /**
     * @return static
     */
    public function setShowFirstLast(int $showFirstLast): self
    {
        $this->showFirstLast = $showFirstLast;

        return $this;
    }

    /**
     * @return $this
     */
    public function setShowPreviousNext(int $showPreviousNext): self
    {
        $this->showPreviousNext = $showPreviousNext;

        return $this;
    }

    public function getResultsStartOffset(): int
    {
        return (int) $this->request->getParameters()->get('page') >= 1
            ? ($this->request->getParameters()->get('page') - 1) * $this->resultsPerPage
            : 0;
    }

    /**
     * @return array<string, mixed>[]
     *
     * @throws InvalidPageException
     */
    public function render(): array
    {
        if ($this->getResultsStartOffset() > $this->totalResults) {
            throw new InvalidPageException(\sprintf('Could not find any entries for page %d', $this->currentPage));
        }

        if ($this->totalResults > $this->resultsPerPage) {
            $areaPrefix = $this->request->getArea() === AreaEnum::AREA_ADMIN ? 'acp/' : '';
            $link = $areaPrefix . $this->request->getUriWithoutPages();

            $this->currentPage = (int) $this->request->getParameters()->get('page', 1);
            $this->totalPages = (int) ceil($this->totalResults / $this->resultsPerPage);

            $this->setMetaStatements();
            [$rangeStart, $rangeEnd] = $this->calculateRange();

            $this->addFirstPageLink($link, $rangeStart);
            $this->addPreviousPageLink($link);

            for ($pageNumber = $rangeStart; $pageNumber <= $rangeEnd; ++$pageNumber) {
                $this->addPageNumber(
                    $pageNumber,
                    $link . ($pageNumber > 1 ? 'page_' . $pageNumber . '/' : ''),
                    '',
                    $this->currentPage === $pageNumber
                );
            }

            $this->addNextPageLink($link);
            $this->addLastPageLink($link, $rangeEnd);
        }

        return $this->pagination;
    }

    protected function setMetaStatements(): void
    {
        if ($this->currentPage > 1) {
            $postfix = $this->translator->t('system', 'page_x', ['%page%' => $this->currentPage]);
            $this->title->setPageTitlePostfix($postfix);
        }
    }

    /**
     * @return int[]
     */
    private function calculateRange(): array
    {
        $rangeStart = 1;
        $rangeEnd = $this->totalPages;
        $pagesToDisplay = $this->getPagesToDisplay();

        if ($this->totalPages > $pagesToDisplay) {
            $center = floor($pagesToDisplay / 2);
            $rangeStart = max(1, $this->currentPage - $center);
            $rangeEnd = min($this->totalPages, $rangeStart + $pagesToDisplay - 1);

            // Anzuzeigende Seiten immer auf dem Wert von $pagesToDisplay halten
            if ($rangeEnd === $this->totalPages) {
                $rangeStart = min($rangeStart, $rangeEnd - $pagesToDisplay + 1);
            }
        }

        return [
            (int) $rangeStart,
            (int) $rangeEnd,
        ];
    }

    private function addFirstPageLink(string $link, int $rangeStart): void
    {
        if ($this->totalPages > $this->showFirstLast && $rangeStart > 1) {
            $this->addPageNumber(
                '&laquo;',
                $link,
                $this->translator->t('system', 'first_page'),
                false,
                'pagination__first-page'
            );
        }
    }

    /**
     * @return static
     */
    private function addPageNumber(
        int|string $pageNumber,
        string $uri,
        string $title = '',
        bool $selected = false,
        string $selector = '',
    ): self {
        $this->pagination[] = $this->buildPageNumber($pageNumber, $uri, $title, $selected, $selector);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPageNumber(
        int|string $pageNumber,
        string $uri,
        string $title = '',
        bool $selected = false,
        string $selector = '',
    ): array {
        return [
            'page' => $pageNumber,
            'uri' => $this->router->route($uri) . $this->urlFragment,
            'title' => $title,
            'selected' => $selected,
            'selector' => $selector,
        ];
    }

    private function addPreviousPageLink(string $link): void
    {
        if ($this->canShowPreviousPageLink()) {
            $this->addPageNumber(
                '&lsaquo;',
                $link . ($this->currentPage - 1 > 1 ? 'page_' . ($this->currentPage - 1) . '/' : ''),
                $this->translator->t('system', 'previous_page'),
                false,
                'pagination__previous-page'
            );
        }
    }

    private function canShowPreviousPageLink(): bool
    {
        return $this->totalPages > $this->showPreviousNext && $this->currentPage !== 1;
    }

    private function addNextPageLink(string $link): void
    {
        if ($this->canShowNextPageLink()) {
            $this->addPageNumber(
                '&rsaquo;',
                $link . 'page_' . ($this->currentPage + 1),
                $this->translator->t('system', 'next_page'),
                false,
                'pagination__next-page'
            );
        }
    }

    private function canShowNextPageLink(): bool
    {
        return $this->totalPages > $this->showPreviousNext && $this->currentPage !== $this->totalPages;
    }

    private function addLastPageLink(string $link, int $rangeEnd): void
    {
        if ($this->totalPages > $this->showFirstLast && $this->totalPages !== $rangeEnd) {
            $this->addPageNumber(
                '&raquo;',
                $link . 'page_' . $this->totalPages,
                $this->translator->t('system', 'last_page'),
                false,
                'pagination__last-page'
            );
        }
    }
}
