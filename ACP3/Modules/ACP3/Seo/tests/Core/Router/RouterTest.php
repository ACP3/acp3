<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Router;

class RouterTest extends \ACP3\Modules\ACP3\System\Core\Router\RouterTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Aliases
     */
    private $aliasesMock;

    protected function setup(): void
    {
        parent::setup();

        $this->initializeMockObjects();

        $this->router = new Router(
            $this->aliasesMock,
            $this->requestMock,
            $this->appPathMock,
            $this->configMock
        );
    }

    protected function initializeMockObjects(): void
    {
        $this->aliasesMock = $this->createMock(Aliases::class);

        parent::initializeMockObjects();
    }

    public function testRouteWithNoUriAlias(): void
    {
        $path = 'news/index/index/';

        $this->setUpRequestMockExpectations();
        $this->setAppPathMockExpectations(0, 1);
        $this->setUpConfigMockExpectations();
        $this->setUpAliasesMockExpectations(1, $path);

        $expected = '/index.php/news/index/index/';

        self::assertEquals($expected, $this->router->route($path));
    }

    public function testRouteWithExistingUriAlias(): void
    {
        $this->setUpRequestMockExpectations();
        $this->setAppPathMockExpectations(0, 1);
        $this->setUpConfigMockExpectations();
        $this->setUpAliasesMockExpectations(1, 'lorem-ipsum-dolor');

        $path = 'news/index/index/';
        $expected = '/index.php/lorem-ipsum-dolor/';

        self::assertEquals($expected, $this->router->route($path));
    }

    private function setUpAliasesMockExpectations(int $callCount, string $returnValue): void
    {
        $this->aliasesMock->expects(self::exactly($callCount))
            ->method('getUriAlias')
            ->willReturn($returnValue);
    }

    public function testRouteUseNoModRewrite(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testRouteUseNoModRewrite();
    }

    public function testRouteUseModRewrite(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testRouteUseModRewrite();
    }

    public function testRouteAddTrailingSlash(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testRouteAddTrailingSlash();
    }

    public function testRouteWithAdminUrl(): void
    {
        $this->setUpAliasesMockExpectations(0, 'acp/index/index/');

        parent::testRouteWithAdminUrl();
    }

    public function testRouteWithAclResourcePath(): void
    {
        $this->setUpAliasesMockExpectations(0, 'admin/news/index/index/');

        parent::testRouteWithAclResourcePath();
    }

    public function testRouteWithAdminUrlModRewriteEnabled(): void
    {
        $this->setUpAliasesMockExpectations(0, 'acp/news/index/index/');

        parent::testRouteWithAdminUrlModRewriteEnabled();
    }

    public function testAbsoluteRoute(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testAbsoluteRoute();
    }

    public function testSecureRoute(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testSecureRoute();
    }

    public function testRouteAppendControllerAndControllerAction(): void
    {
        $this->setUpAliasesMockExpectations(1, 'news/index/index/');

        parent::testRouteAppendControllerAndControllerAction();
    }

    public function testRouteCompleteDefaultAdminPanelUrl(): void
    {
        $this->setUpAliasesMockExpectations(0, 'acp/acp/index/index/');

        parent::testRouteCompleteDefaultAdminPanelUrl();
    }
}
