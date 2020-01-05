<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Helpers;

use ACP3\Core;
use ACP3\Core\Helpers\TableOfContents;
use ACP3\Modules\ACP3\Seo\Helper\MetaStatements;

class PageBreaks extends \ACP3\Core\Helpers\PageBreaks
{
    /**
     * @var \ACP3\Modules\ACP3\Seo\Helper\MetaStatements
     */
    protected $metaStatements;

    /**
     * PageBreaks constructor.
     *
     * @param \ACP3\Core\Http\RequestInterface  $request
     * @param \ACP3\Core\Router\RouterInterface $router
     */
    public function __construct(
        Core\Http\RequestInterface $request,
        Core\Router\RouterInterface $router,
        TableOfContents $tableOfContents,
        MetaStatements $metaStatements
    ) {
        parent::__construct($request, $router, $tableOfContents);

        $this->metaStatements = $metaStatements;
    }

    /**
     * {@inheritdoc}
     */
    public function splitTextIntoPages($text, $baseUrlPath)
    {
        $pages = parent::splitTextIntoPages($text, $baseUrlPath);

        $this->metaStatements->setNextPage($pages['next']);
        $this->metaStatements->setPreviousPage($pages['previous']);

        return $pages;
    }
}