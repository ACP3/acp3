<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Core\View\Renderer\Smarty\Functions;

use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Helpers\Enum\LinkTargetEnum;
use ACP3\Core\Helpers\Enum\YesNoEnum;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\Smarty_Internal_Template_Fixture;
use ACP3\Modules\ACP3\Menus\Enum\PageTypeEnum;
use ACP3\Modules\ACP3\Menus\Repository\MenuItemRepository;
use ACP3\Modules\ACP3\Menus\Services\MenuServiceInterface;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase
{
    private RequestInterface&MockObject $requestMock;
    private RouterInterface&MockObject $routerMock;
    private MenuItemRepository&MockObject $menuItemRepositoryMock;
    private MenuServiceInterface&MockObject $menuServiceMock;
    private Navbar $navbar;

    /**
     * @return mixed[]
     */
    public static function menuRenderWithoutSelectedMenuItemsProvider(): array
    {
        return [
            [
                self::getMenuStructureForDataProvider(),
                <<<HTML
<ul class="navigation-foo-block navbar-nav me-auto mb-2 mb-lg-0">
  <li class="navi-1 nav-item dropdown navigation-foo-block-subnav-1-dropdown">
    <a href="" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" role="button">Foo</a>
    <ul class="navigation-foo-block-subnav-1 dropdown-menu">
      <li class="navi-2 nav-item dropdown navigation-foo-block-subnav-2-dropdown">
        <a href="" class="dropdown-item" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" role="button">Sub-Foo</a>
        <ul class="navigation-foo-block-subnav-2 dropdown-menu">
          <li class="navi-3 nav-item">
            <span class="dropdown-header">Sub-Sub-Headline</span>
          </li>
          <li class="navi-4 nav-item">
            <a href="" class="dropdown-item">Sub-Sub-Foo</a>
          </li>
        </ul>
      </li>
    </ul>
  </li>
  <li class="navi-4 nav-item">
    <a href="" class="nav-link">Bar</a>
  </li>
</ul>
HTML,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function getMenuStructureForDataProvider(): array
    {
        return [
            [
                'id' => 1,
                'block_id' => 10,
                'left_id' => 1,
                'right_id' => 6,
                'parent_id' => 0,
                'root_id' => 1,
                'level' => 0,
                'block_name' => 'foo-block',
                'block_title' => 'Foo-Block',
                'title' => 'Foo',
                'mode' => PageTypeEnum::DYNAMIC_PAGE->value,
                'uri' => 'foo/index/index/',
                'target' => LinkTargetEnum::TARGET_SELF->value,
                'display' => YesNoEnum::YES->value,
            ],
            [
                'id' => 2,
                'block_id' => 10,
                'left_id' => 2,
                'right_id' => 7,
                'parent_id' => 1,
                'root_id' => 1,
                'level' => 1,
                'block_name' => 'foo-block',
                'block_title' => 'Foo-Block',
                'title' => 'Sub-Foo',
                'mode' => PageTypeEnum::DYNAMIC_PAGE->value,
                'uri' => 'sub/foo/index/',
                'target' => LinkTargetEnum::TARGET_SELF->value,
                'display' => YesNoEnum::YES->value,
            ],
            [
                'id' => 3,
                'block_id' => 10,
                'left_id' => 3,
                'right_id' => 4,
                'parent_id' => 2,
                'root_id' => 1,
                'level' => 2,
                'block_name' => 'foo-block',
                'block_title' => 'Foo-Block',
                'title' => 'Sub-Sub-Headline',
                'mode' => PageTypeEnum::HEADLINE->value,
                'uri' => '',
                'target' => LinkTargetEnum::TARGET_SELF->value,
                'display' => YesNoEnum::YES->value,
            ],
            [
                'id' => 4,
                'block_id' => 10,
                'left_id' => 5,
                'right_id' => 6,
                'parent_id' => 2,
                'root_id' => 1,
                'level' => 2,
                'block_name' => 'foo-block',
                'block_title' => 'Foo-Block',
                'title' => 'Sub-Sub-Foo',
                'mode' => PageTypeEnum::DYNAMIC_PAGE->value,
                'uri' => 'sub/foo/index/',
                'target' => LinkTargetEnum::TARGET_SELF->value,
                'display' => YesNoEnum::YES->value,
            ],
            [
                'id' => 4,
                'block_id' => 10,
                'left_id' => 9,
                'right_id' => 10,
                'parent_id' => 0,
                'root_id' => 4,
                'level' => 0,
                'block_name' => 'foo-block',
                'block_title' => 'Foo-Block',
                'title' => 'Bar',
                'mode' => PageTypeEnum::DYNAMIC_PAGE->value,
                'uri' => 'bar/index/index/',
                'target' => LinkTargetEnum::TARGET_SELF->value,
                'display' => YesNoEnum::YES->value,
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->menuItemRepositoryMock = $this->createMock(MenuItemRepository::class);
        $this->menuServiceMock = $this->createMock(MenuServiceInterface::class);

        $this->navbar = new Navbar(
            $this->requestMock,
            $this->routerMock,
            $this->menuItemRepositoryMock,
            $this->menuServiceMock
        );
    }

    /**
     * @dataProvider menuRenderWithoutSelectedMenuItemsProvider
     *
     * @param array<string, mixed>[] $menuItems
     *
     * @throws Exception
     */
    public function testRendersMenuCorrectlyWithoutSelectedMenuItems(array $menuItems, string $expectedRenderedMenu): void
    {
        $this->requestMock
            ->expects(self::once())
            ->method('getArea')
            ->willReturn(AreaEnum::AREA_ADMIN);
        $this->menuServiceMock
            ->expects(self::once())
            ->method('getVisibleMenuItemsByMenu')
            ->willReturn($menuItems);
        $this->routerMock
            ->method('route')
            ->willReturn('');

        self::assertEquals(str_replace(['  ', "\n"], '', $expectedRenderedMenu), ($this->navbar)(
            ['block' => 'foo-block', 'class' => 'navbar-nav me-auto mb-2 mb-lg-0', 'classLink' => 'nav-link', 'itemSelectors' => 'nav-item'],
            $this->createMock(Smarty_Internal_Template_Fixture::class))
        );
    }

    /**
     * @return mixed[]
     */
    public static function menuRenderWithSelectedMenuItemsProvider(): array
    {
        return [
            [
                self::getMenuStructureForDataProvider(),
                <<<HTML
<ul class="navigation-foo-block navbar-nav me-auto mb-2 mb-lg-0">
  <li class="navi-1 nav-item dropdown navigation-foo-block-subnav-1-dropdown">
    <a href="" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" role="button">Foo</a>
    <ul class="navigation-foo-block-subnav-1 dropdown-menu">
      <li class="navi-2 nav-item dropdown navigation-foo-block-subnav-2-dropdown">
        <a href="" class="dropdown-item active" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" role="button">Sub-Foo</a>
        <ul class="navigation-foo-block-subnav-2 dropdown-menu">
          <li class="navi-3 nav-item">
            <span class="dropdown-header">Sub-Sub-Headline</span>
          </li>
          <li class="navi-4 nav-item">
            <a href="" class="dropdown-item active">Sub-Sub-Foo</a>
          </li>
        </ul>
      </li>
    </ul>
  </li>
  <li class="navi-4 nav-item">
    <a href="" class="nav-link">Bar</a>
  </li>
</ul>
HTML,
            ],
        ];
    }

    /**
     * @dataProvider menuRenderWithSelectedMenuItemsProvider
     *
     * @param array<string, mixed>[] $menuItems
     *
     * @throws Exception
     */
    public function testRendersMenuCorrectlyWithSelectedMenuItems(array $menuItems, string $expectedRenderedMenu): void
    {
        $this->requestMock
            ->expects(self::once())
            ->method('getArea')
            ->willReturn(AreaEnum::AREA_FRONTEND);
        $this->menuItemRepositoryMock
            ->expects(self::once())
            ->method('getLeftIdByUris')
            ->willReturn(5);
        $this->menuServiceMock
            ->expects(self::once())
            ->method('getVisibleMenuItemsByMenu')
            ->willReturn($menuItems);
        $this->routerMock
            ->method('route')
            ->willReturn('');

        self::assertEquals(str_replace(['  ', "\n"], '', $expectedRenderedMenu), ($this->navbar)(
            ['block' => 'foo-block', 'class' => 'navbar-nav me-auto mb-2 mb-lg-0', 'classLink' => 'nav-link', 'itemSelectors' => 'nav-item'],
            $this->createMock(Smarty_Internal_Template_Fixture::class))
        );
    }
}
