<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Helpers\StringFormatter;
use ACP3\Core\Http\RequestInterface;

class PageCssClassesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageCssClasses
     */
    private $pageCssClasses;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Title
     */
    private $titleMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RequestInterface
     */
    private $requestMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&StringFormatter
     */
    private $stringFormatterMock;

    protected function setup(): void
    {
        $this->setUpMockObjects();

        $this->pageCssClasses = new PageCssClasses(
            $this->stringFormatterMock,
            $this->titleMock,
            $this->requestMock
        );
    }

    private function setUpMockObjects(): void
    {
        $this->titleMock = $this->createMock(Title::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->stringFormatterMock = $this->createMock(StringFormatter::class);
    }

    public function testGetModule(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getModule')
            ->willReturn('foo');

        self::assertEquals('foo', $this->pageCssClasses->getModule());
    }

    public function testGetControllerAction(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getModule')
            ->willReturn('foo');
        $this->requestMock->expects(self::once())
            ->method('getController')
            ->willReturn('bar');
        $this->requestMock->expects(self::once())
            ->method('getAction')
            ->willReturn('baz');

        self::assertEquals('foo-bar-baz', $this->pageCssClasses->getControllerAction());
    }

    public function testGetDetails(): void
    {
        $this->requestMock->expects(self::once())
            ->method('getModule')
            ->willReturn('foo');
        $this->requestMock->expects(self::once())
            ->method('getController')
            ->willReturn('bar');
        $this->titleMock->expects(self::once())
            ->method('getPageTitle')
            ->willReturn('speciäl-chörs_0tßst');
        $this->stringFormatterMock->expects(self::once())
            ->method('makeStringUrlSafe')
            ->with('speciäl-chörs_0tßst')
            ->willReturn('speciael-choers-0tssst');

        self::assertEquals('foo-bar-speciael-choers-0tssst', $this->pageCssClasses->getDetails());
    }
}
