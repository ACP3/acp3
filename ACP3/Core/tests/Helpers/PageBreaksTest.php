<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Router\RouterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class PageBreaksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageBreaks
     */
    protected $pageBreaks;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RequestInterface
     */
    protected $requestMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RouterInterface
     */
    protected $routerMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&TableOfContents
     */
    protected $tocMock;

    protected function setup(): void
    {
        $this->initializeMockObjects();

        $this->pageBreaks = new PageBreaks(
            $this->requestMock,
            $this->routerMock,
            $this->tocMock
        );
    }

    protected function initializeMockObjects(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->tocMock = $this->createMock(TableOfContents::class);
    }

    /**
     * @return mixed[]
     */
    public static function splitTextIntoPagesDataProvider(): array
    {
        $baseUrlPath = 'lorem/ipsum/dolor/id_1/';

        return [
            'single_page' => [
                'Foo Bar Baz',
                1,
                'Foo Bar Baz',
                $baseUrlPath,
                '',
                '',
            ],
            'two_pages_first_page_visible' => [
                'Foo Bar Baz<hr class="page-break">Lorem Ipsum Dolor<hr class="page-break">',
                1,
                'Foo Bar Baz',
                $baseUrlPath,
                '/' . $baseUrlPath . 'page_2/',
                '',
            ],
            'two_pages_last_page_visible' => [
                'Foo Bar Baz<hr class="page-break">Lorem Ipsum Dolor<hr class="page-break">',
                2,
                'Lorem Ipsum Dolor',
                $baseUrlPath,
                '',
                '/' . $baseUrlPath,
            ],
        ];
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
        $this->setUpExpectations($currentPage, $baseUrlPath);

        $expected = [
            'toc' => '',
            'text' => $currentPageText,
            'next' => $nextPageUrl,
            'previous' => $prevPageUrl,
        ];

        self::assertEquals($expected, $this->pageBreaks->splitTextIntoPages($sourceText, $baseUrlPath));
    }

    private function setUpExpectations(int $currentPage, string $baseUrlPath): void
    {
        $this->requestMock

            ->method('getParameters')
            ->willReturn(new ParameterBag(['page' => $currentPage]));

        $this->routerMock

            ->method('route')
            ->with($baseUrlPath)
            ->willReturn('/' . $baseUrlPath);

        $this->tocMock
            ->expects(self::once())
            ->method('generateTOC')
            ->willReturn('');
    }
}
