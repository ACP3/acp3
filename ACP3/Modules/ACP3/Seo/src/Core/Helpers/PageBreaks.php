<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Helpers;

use ACP3\Core;
use ACP3\Core\Helpers\TableOfContents;
use ACP3\Core\SEO\MetaStatementsServiceInterface;

class PageBreaks extends Core\Helpers\PageBreaks
{
    public function __construct(
        Core\Http\RequestInterface $request,
        Core\Router\RouterInterface $router,
        TableOfContents $tableOfContents,
        private readonly MetaStatementsServiceInterface $metaStatements,
    ) {
        parent::__construct($request, $router, $tableOfContents);
    }

    public function splitTextIntoPages(string $text, string $baseUrlPath): array
    {
        $pages = parent::splitTextIntoPages($text, $baseUrlPath);

        $this->metaStatements->setNextPage($pages['next']);
        $this->metaStatements->setPreviousPage($pages['previous']);

        return $pages;
    }
}
