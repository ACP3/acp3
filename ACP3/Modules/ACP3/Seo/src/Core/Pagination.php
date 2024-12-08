<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Seo\Helper\Enum\IndexPaginatedContentEnum;
use ACP3\Modules\ACP3\Seo\Installer\Schema;

class Pagination extends \ACP3\Core\Pagination
{
    public function __construct(
        Title $title,
        Translator $translator,
        RequestInterface $request,
        RouterInterface $router,
        private readonly MetaStatementsServiceInterface $metaStatements,
        private readonly SettingsInterface $settings,
    ) {
        parent::__construct($title, $translator, $request, $router);
    }

    protected function setMetaStatements(): void
    {
        parent::setMetaStatements();

        if ($this->request->getArea() !== AreaEnum::AREA_ADMIN) {
            if ($this->currentPage - 1 > 0) {
                $this->modifyMetaDescription();
                $this->setMetaPreviousPage();
                $this->preventIndexing();
            }
            $this->setMetaNextPage();
            $this->setMetaCanonicalUri();
        }
    }

    private function getRoute(): string
    {
        $path = ($this->request->getArea() === AreaEnum::AREA_ADMIN ? 'acp/' : '') . $this->request->getUriWithoutPages();

        return $this->router->route($path, true);
    }

    /**
     * Seitenangabe in der Seitenbeschreibung ab Seite 2 angeben.
     */
    private function modifyMetaDescription(): void
    {
        $this->metaStatements->setDescriptionPostfix(
            $this->translator->t(
                'system',
                'page_x',
                ['%page%' => $this->currentPage]
            )
        );
    }

    private function setMetaPreviousPage(): void
    {
        $this->metaStatements->setPreviousPage($this->getRoute() . 'page_' . ($this->currentPage - 1) . '/');
    }

    private function preventIndexing(): void
    {
        $seoSettings = $this->settings->getSettings(Schema::MODULE_NAME);

        if (IndexPaginatedContentEnum::from($seoSettings['index_paginated_content']) === IndexPaginatedContentEnum::INDEX_FIST_PAGE_ONLY) {
            $this->metaStatements->setPageRobotsSettings(MetaStatementsServiceInterface::NOINDEX_FOLLOW);
        }
    }

    private function setMetaNextPage(): void
    {
        if ($this->currentPage + 1 <= $this->totalPages) {
            $this->metaStatements->setNextPage($this->getRoute() . 'page_' . ($this->currentPage + 1) . '/');
        }
    }

    private function setMetaCanonicalUri(): void
    {
        if ($this->request->getParameters()->get('page', 0) === 1) {
            $this->metaStatements->setCanonicalUri($this->getRoute());
        }
    }
}
