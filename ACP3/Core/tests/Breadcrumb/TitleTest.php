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
     * @var \ACP3\Core\Breadcrumb\Title
     */
    protected $title;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stepsMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventDispatcherMock;

    protected function setup(): void
    {
        $this->initializeMockObjects();

        $this->title = new Title(
            $this->stepsMock,
            $this->eventDispatcherMock
        );
    }

    protected function initializeMockObjects()
    {
        $this->stepsMock = $this->createMock(Steps::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
    }

    public function testGetSiteAndPageTitleWithNoCustomSiteTitle()
    {
        $this->setUpStepsExpectations(1);

        $this->assertEquals('Foo', $this->title->getSiteAndPageTitle());
    }

    protected function setUpStepsExpectations(int $callCount)
    {
        $steps = [
            [
                'title' => 'Foo',
                'uri' => '/foo/bar/baz/',
            ],
        ];

        $this->stepsMock->expects($this->exactly($callCount))
            ->method('getBreadcrumb')
            ->willReturn($steps);
    }

    public function testGetSiteAndPageTitleWithCustomSiteTitle()
    {
        $this->setUpStepsExpectations(1);

        $this->title->setSiteTitle('Lorem Ipsum');

        $this->assertEquals('Foo | Lorem Ipsum', $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleWithPrefixAndPostfixAndSeparator()
    {
        $this->setUpStepsExpectations(1);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setPageTitlePrefix('ACP')
            ->setPageTitlePostfix('Page 1')
            ->setPageTitleSeparator('::');

        $expected = 'ACP :: Foo :: Page 1 | Lorem Ipsum';
        $this->assertEquals($expected, $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleWithCustomPageTitle()
    {
        $this->setUpStepsExpectations(0);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setPageTitle('FooBar');

        $expected = 'FooBar | Lorem Ipsum';
        $this->assertEquals($expected, $this->title->getSiteAndPageTitle());
    }

    public function testGetSiteAndPageTitleMetaTitleTakesPrecedenceOverPageTitle()
    {
        $this->setUpStepsExpectations(0);

        $this->title
            ->setSiteTitle('Lorem Ipsum')
            ->setMetaTitle('Baz')
            ->setPageTitle('FooBar');

        $expected = 'Baz | Lorem Ipsum';
        $this->assertEquals($expected, $this->title->getSiteAndPageTitle());
    }
}
