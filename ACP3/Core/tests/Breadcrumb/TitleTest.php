<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Breadcrumb;

use Symfony\Component\EventDispatcher\EventDispatcher;

class TitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Title
     */
    protected $title;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Steps
     */
    protected $stepsMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&EventDispatcher
     */
    protected $eventDispatcherMock;

    protected function setup(): void
    {
        parent::setup();

        $this->initializeMockObjects();

        $this->title = new Title(
            $this->stepsMock,
            $this->eventDispatcherMock
        );
    }

    protected function initializeMockObjects(): void
    {
        $this->stepsMock = $this->createMock(Steps::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
    }

    public function testGetSiteAndPageTitleWithNoCustomSiteTitle(): void
    {
        $this->setUpStepsExpectations(1);

        self::assertEquals('Foo', $this->title->getSiteAndPageTitle());
    }

    protected function setUpStepsExpectations(int $callCount): void
    {
        $steps = [
            [
                'title' => 'Foo',
                'uri' => '/foo/bar/baz/',
            ],
        ];

        $this->stepsMock->expects(self::exactly($callCount))
            ->method('getBreadcrumb')
            ->willReturn($steps);
    }

    public function testGetSiteAndPageTitleWithCustomSiteTitle(): void
    {
        $this->setUpStepsExpectations(1);

        $this->title->setSiteTitle('Lorem Ipsum');

        self::assertEquals('Foo | Lorem Ipsum', $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleWithPrefixAndPostfixAndSeparator(): void
    {
        $this->setUpStepsExpectations(1);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setPageTitlePrefix('ACP')
            ->setPageTitlePostfix('Page 1')
            ->setPageTitleSeparator('::');

        $expected = 'ACP :: Foo :: Page 1 | Lorem Ipsum';
        self::assertEquals($expected, $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleWithCustomPageTitle(): void
    {
        $this->setUpStepsExpectations(0);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setPageTitle('FooBar');

        $expected = 'FooBar | Lorem Ipsum';
        self::assertEquals($expected, $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleMetaTitleTakesPrecedenceOverPageTitle(): void
    {
        $this->setUpStepsExpectations(0);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setMetaTitle('Baz')
            ->setPageTitle('FooBar');

        $expected = 'Baz | Lorem Ipsum';
        self::assertEquals($expected, $this->title->getSiteAndPageTitle());
    }
}
