<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Helpers;

use ACP3\Core\SEO\MetaStatementsServiceInterface;

class PageBreaksTest extends \ACP3\Core\Helpers\PageBreaksTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&MetaStatementsServiceInterface
     */
    protected $metaStatements;

    protected function setup(): void
    {
        parent::setup();

        $this->initializeMockObjects();

        $this->pageBreaks = new PageBreaks(
            $this->requestMock,
            $this->routerMock,
            $this->tocMock,
            $this->metaStatements
        );
    }

    protected function initializeMockObjects(): void
    {
        parent::initializeMockObjects();

        $this->metaStatements = $this->createMock(MetaStatementsServiceInterface::class);
    }

    /**
     * @dataProvider splitTextIntoPagesDataProvider
     */
    public function testSplitTextIntoPages(
        string $sourceText,
        int $currentPage,
        string $currentPageText,
        string $baseUrlPath,
        string $nextPageUrl,
        string $prevPageUrl,
    ): void {
        $this->setUpMetaStatementsMockExpectations($nextPageUrl, $prevPageUrl);

        parent::testSplitTextIntoPages(
            $sourceText,
            $currentPage,
            $currentPageText,
            $baseUrlPath,
            $nextPageUrl,
            $prevPageUrl
        );
    }

    private function setUpMetaStatementsMockExpectations(string $nextPageUrl, string $prevPageUrl): void
    {
        $this->metaStatements->expects(self::once())
            ->method('setNextPage')
            ->with($nextPageUrl)
            ->willReturnSelf();
        $this->metaStatements->expects(self::once())
            ->method('setPreviousPage')
            ->with($prevPageUrl)
            ->willReturnSelf();
    }
}
