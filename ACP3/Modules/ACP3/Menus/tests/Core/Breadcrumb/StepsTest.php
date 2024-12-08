<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Core\Breadcrumb;

use ACP3\Core\Controller\AreaEnum;
use ACP3\Modules\ACP3\Menus\Repository\MenuItemRepository;

class StepsTest extends \ACP3\Core\Breadcrumb\StepsTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&MenuItemRepository
     */
    private $menuItemRepositoryMock;

    protected function setup(): void
    {
        parent::setup();

        $this->initializeMockObjects();

        $this->steps = new Steps(
            $this->containerMock,
            $this->translatorMock,
            $this->requestMock,
            $this->routerMock,
            $this->eventDispatcherMock,
            $this->menuItemRepositoryMock
        );
    }

    protected function initializeMockObjects(): void
    {
        parent::initializeMockObjects();

        $this->menuItemRepositoryMock = $this->createMock(MenuItemRepository::class);
    }

    /**
     * @param array<array<string, mixed>> $dbSteps
     */
    protected function setUpMenuItemRepositoryExpectations(array $dbSteps = []): void
    {
        $this->menuItemRepositoryMock->expects(self::once())
            ->method('getMenuItemsByUri')
            ->withAnyParameters()
            ->willReturn($dbSteps);
    }

    public function testGetBreadcrumbWithSingleDbStep(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'News',
                'uri' => 'news',
                'left_id' => 1,
                'right_id' => 4,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'news',
            'index',
            'index'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $expected = [
            [
                'title' => 'News',
                'uri' => '/news/',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbWithMultipleDbSteps(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'News',
                'uri' => 'news',
                'left_id' => 1,
                'right_id' => 4,
            ],
            [
                'title' => 'Newsletter',
                'uri' => 'newsletter',
                'left_id' => 2,
                'right_id' => 3,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'newsletter',
            'index',
            'index'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $expected = [
            [
                'title' => 'News',
                'uri' => '/news/',
            ],
            [
                'title' => 'Newsletter',
                'uri' => '/newsletter/',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbWithMultipleDbStepsAndDefaultSteps(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'News',
                'uri' => 'news',
                'left_id' => 1,
                'right_id' => 4,
            ],
            [
                'title' => 'Newsletter',
                'uri' => 'newsletter',
                'left_id' => 2,
                'right_id' => 3,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'newsletter',
            'index',
            'archive'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $expected = [
            [
                'title' => 'News',
                'uri' => '/news/',
            ],
            [
                'title' => 'Newsletter',
                'uri' => '/newsletter/',
            ],
            [
                'title' => '{NEWSLETTER_FRONTEND_INDEX_ARCHIVE}',
                'uri' => '/newsletter/index/archive/',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbWithMultipleDbStepsAndCustomSteps(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'News',
                'uri' => 'news',
                'left_id' => 1,
                'right_id' => 4,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'news',
            'index',
            'details',
            'id_1'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $this->steps->append('News', 'news');
        $this->steps->append('Category', 'news/index/index/cat_1');
        $this->steps->append('News-Title');

        $expected = [
            [
                'title' => 'News',
                'uri' => '/news/',
            ],
            [
                'title' => 'Category',
                'uri' => '/news/index/index/cat_1/',
            ],
            [
                'title' => 'News-Title',
                'uri' => '',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbLastDbStepTitleShouldTakePrecedence(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'FooBar',
                'uri' => 'articles/index/details/id_1/',
                'left_id' => 1,
                'right_id' => 2,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'articles',
            'index',
            'details',
            'id_1'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $this->steps->append('Lorem Ipsum Dolor', 'articles/index/details/id_1');

        $expected = [
            [
                'title' => 'FooBar',
                'uri' => '/articles/index/details/id_1/',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbLastDbStepTitleShouldTakePrecedenceWithEmptyUri(): void
    {
        $this->setUpMenuItemRepositoryExpectations([
            [
                'title' => 'FooBar',
                'uri' => 'articles/index/details/id_1/',
                'left_id' => 1,
                'right_id' => 2,
            ],
        ]);
        $this->setUpRequestMockExpectations(
            AreaEnum::AREA_FRONTEND,
            'articles',
            'index',
            'details',
            'id_1'
        );
        $this->setUpRouterMockExpectations();
        $this->setUpTranslatorMockExpectations(0);

        $this->steps->append('Lorem Ipsum Dolor');

        $expected = [
            [
                'title' => 'FooBar',
                'uri' => '/articles/index/details/id_1/',
                'last' => true,
            ],
        ];
        self::assertEquals($expected, $this->steps->getBreadcrumb());
    }

    public function testGetBreadcrumbForFrontendControllerIndex(): void
    {
        $this->setUpMenuItemRepositoryExpectations();

        parent::testGetBreadcrumbForFrontendControllerIndex();
    }

    public function testGetBreadcrumbForFrontendController(): void
    {
        $this->setUpMenuItemRepositoryExpectations();

        parent::testGetBreadcrumbForFrontendController();
    }

    public function testGetBreadcrumbForFrontendWithExistingSteps(): void
    {
        $this->setUpMenuItemRepositoryExpectations();

        parent::testGetBreadcrumbForFrontendWithExistingSteps();
    }

    public function testAddMultipleSameSteps(): void
    {
        $this->setUpMenuItemRepositoryExpectations();

        parent::testReplaceAncestor();
    }

    public function testReplaceAncestor(): void
    {
        $this->setUpMenuItemRepositoryExpectations();

        parent::testReplaceAncestor();
    }
}
