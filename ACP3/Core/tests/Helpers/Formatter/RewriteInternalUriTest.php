<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers\Formatter;

use ACP3\Core\Controller\Helper\ControllerActionExists;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\Validation\ValidationRules\InternalUriValidationRule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ServerBag;

class RewriteInternalUriTest extends TestCase
{
    /**
     * @var RewriteInternalUri
     */
    private $rewriteInternalUri;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ApplicationPath
     */
    private $appPathMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ControllerActionExists
     */
    private $controllerActionExistsMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RequestInterface
     */
    private $requestMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RouterInterface
     */
    private $routerMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&InternalUriValidationRule
     */
    private $internalUriValidationRule;

    protected function setup(): void
    {
        $this->initializeMockObjects();

        $this->rewriteInternalUri = new RewriteInternalUri(
            $this->appPathMock,
            $this->controllerActionExistsMock,
            $this->requestMock,
            $this->routerMock,
            $this->internalUriValidationRule
        );
    }

    private function initializeMockObjects(): void
    {
        $this->appPathMock = $this->createMock(ApplicationPath::class);
        $this->controllerActionExistsMock = $this->createMock(ControllerActionExists::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->internalUriValidationRule = $this->createMock(InternalUriValidationRule::class);
    }

    public function testRewriteInternalUriWithNotMatchingUrl(): void
    {
        $this->setUpAppPathExpectations();
        $this->setUpRequestMockExpectations();
        $this->setUpValidationRuleMockExpectations(0, '', false);
        $this->setUpControllerActionExistsMockExpectations(0, '', true);
        $this->setUpRouterMockExpectations(0, '', '');

        $content = <<<HTML
<p>Test</p>
<p><a href="http://google.com">www.google.com</a></p>
HTML;

        self::assertEquals($content, $this->rewriteInternalUri->rewriteInternalUri($content));
    }

    public function testRewriteInternalUriWithMatchingExternalUri(): void
    {
        $this->setUpAppPathExpectations();
        $this->setUpRequestMockExpectations();
        $this->setUpValidationRuleMockExpectations(1, 'foo/bar/baz/', true);
        $this->setUpControllerActionExistsMockExpectations(1, 'frontend/foo/bar/baz', false);
        $this->setUpRouterMockExpectations(0, '', '');

        $content = <<<HTML
<p>Test</p>
<p><a href="http://example.com/foo/bar/baz/">www.example.com</a></p>
HTML;

        self::assertEquals($content, $this->rewriteInternalUri->rewriteInternalUri($content));
    }

    public function testRewriteInternalUriWithMatchingUriAndExistingAlias(): void
    {
        $this->setUpAppPathExpectations();
        $this->setUpRequestMockExpectations();
        $this->setUpValidationRuleMockExpectations(1, 'foo/bar/baz/', true);
        $this->setUpControllerActionExistsMockExpectations(1, 'frontend/foo/bar/baz', true);
        $this->setUpRouterMockExpectations(1, 'foo/bar/baz/', '/foo-bar/');

        $content = <<<HTML
<p>Test</p>
<p><a href="http://example.com/foo/bar/baz/">www.example.com</a></p>
HTML;

        $expected = <<<HTML
<p>Test</p>
<p><a href="/foo-bar/">www.example.com</a></p>
HTML;

        self::assertEquals($expected, $this->rewriteInternalUri->rewriteInternalUri($content));
    }

    private function setUpAppPathExpectations(): void
    {
        $this->appPathMock->expects(self::once())
            ->method('getWebRoot')
            ->willReturn('/');
    }

    private function setUpRequestMockExpectations(string $httpHost = 'example.com'): void
    {
        $this->requestMock->expects(self::once())
            ->method('getServer')
            ->willReturn(new ServerBag([
                'HTTP_HOST' => $httpHost,
            ]));
    }

    private function setUpValidationRuleMockExpectations(int $callCount, string $uri, bool $isValid): void
    {
        $this->internalUriValidationRule->expects(self::exactly($callCount))
            ->method('isValid')
            ->with($uri)
            ->willReturn($isValid);
    }

    private function setUpControllerActionExistsMockExpectations(int $callCount, string $path, bool $isControllerAction): void
    {
        $this->controllerActionExistsMock->expects(self::exactly($callCount))
            ->method('controllerActionExists')
            ->with($path)
            ->willReturn($isControllerAction);
    }

    private function setUpRouterMockExpectations(int $callCount, string $route, string $alias): void
    {
        $this->routerMock->expects(self::exactly($callCount))
            ->method('route')
            ->with($route)
            ->willReturn($alias);
    }

    public function testRewriteInternalUriWithMatchingInlineUri(): void
    {
        $this->setUpAppPathExpectations();
        $this->setUpRequestMockExpectations();
        $this->setUpValidationRuleMockExpectations(1, 'foo/bar/baz/', true);
        $this->setUpControllerActionExistsMockExpectations(1, 'frontend/foo/bar/baz', false);
        $this->setUpRouterMockExpectations(0, '', '');

        $content = <<<HTML
http://example.com/foo/bar/baz/
HTML;

        self::assertEquals($content, $this->rewriteInternalUri->rewriteInternalUri($content));
    }

    public function testRewriteInternalUriWithMatchingInlineUriAndExistingAlias(): void
    {
        $this->setUpAppPathExpectations();
        $this->setUpRequestMockExpectations();
        $this->setUpValidationRuleMockExpectations(1, 'foo/bar/baz/', true);
        $this->setUpControllerActionExistsMockExpectations(1, 'frontend/foo/bar/baz', true);
        $this->setUpRouterMockExpectations(1, 'foo/bar/baz/', '/foo-bar/');

        $content = <<<HTML
http://example.com/foo/bar/baz/
HTML;

        $expected = <<<HTML
/foo-bar/
HTML;

        self::assertEquals($expected, $this->rewriteInternalUri->rewriteInternalUri($content));
    }
}
