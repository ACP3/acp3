<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Http;

use ACP3\Core\Router\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RedirectResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RequestInterface
     */
    private $requestMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&RouterInterface
     */
    private $routerMock;
    /**
     * @var RedirectResponse
     */
    private $redirectResponse;

    protected function setup(): void
    {
        $this->setUpMockObjects();

        $this->redirectResponse = new RedirectResponse(
            $this->requestMock,
            $this->routerMock
        );
    }

    private function setUpMockObjects(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
    }

    public function testRedirectToExternalWebsite(): void
    {
        $this->setUpRequestMockExpectations(false);

        $response = $this->redirectResponse->toNewPage('http://www.google.de');

        self::assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        self::assertEquals('http://www.google.de', $response->getTargetUrl());
    }

    private function setUpRequestMockExpectations(bool $isAjax): void
    {
        $this->requestMock->expects(self::once())
            ->method('isXmlHttpRequest')
            ->willReturn($isAjax);
    }

    public function testAjaxRedirectToExternalWebsite(): void
    {
        $this->setUpRequestMockExpectations(true);

        $response = $this->redirectResponse->toNewPage('http://www.google.de');

        self::assertInstanceOf(
            JsonResponse::class,
            $response
        );

        self::assertEquals($this->buildJsonResponseContent('http:\/\/www.google.de'), $response->getContent());
    }

    private function buildJsonResponseContent(string $url): string
    {
        return <<<JSON
{"redirect_url":"$url"}
JSON;
    }

    public function testTemporaryRedirect(): void
    {
        $this->setUpRequestMockExpectations(false);
        $this->setUpRouterMockExpectations('foo/bar/baz');

        $response = $this->redirectResponse->temporary('foo/bar/baz');

        self::assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        self::assertEquals('http://www.example.com/foo/bar/baz/', $response->getTargetUrl());
    }

    private function setUpRouterMockExpectations(string $path): void
    {
        $this->routerMock->expects(self::once())
            ->method('route')
            ->with($path, true)
            ->willReturn('http://www.example.com/' . $path . '/');
    }

    public function testAjaxTemporaryRedirect(): void
    {
        $this->setUpRequestMockExpectations(true);
        $this->setUpRouterMockExpectations('foo/bar/baz');

        $response = $this->redirectResponse->temporary('foo/bar/baz');

        self::assertInstanceOf(
            JsonResponse::class,
            $response
        );
        self::assertEquals(
            $this->buildJsonResponseContent('http:\/\/www.example.com\/foo\/bar\/baz\/'),
            $response->getContent()
        );
    }

    public function testPermanentRedirect(): void
    {
        $this->setUpRequestMockExpectations(false);
        $this->setUpRouterMockExpectations('foo/bar/baz');

        $response = $this->redirectResponse->permanent('foo/bar/baz');

        self::assertInstanceOf(
            \Symfony\Component\HttpFoundation\RedirectResponse::class,
            $response
        );
        self::assertEquals('http://www.example.com/foo/bar/baz/', $response->getTargetUrl());
    }

    public function testAjaxPermanentRedirect(): void
    {
        $this->setUpRequestMockExpectations(true);
        $this->setUpRouterMockExpectations('foo/bar/baz');

        $response = $this->redirectResponse->permanent('foo/bar/baz');

        self::assertInstanceOf(
            JsonResponse::class,
            $response
        );
        self::assertEquals(
            $this->buildJsonResponseContent('http:\/\/www.example.com\/foo\/bar\/baz\/'),
            $response->getContent()
        );
    }
}
