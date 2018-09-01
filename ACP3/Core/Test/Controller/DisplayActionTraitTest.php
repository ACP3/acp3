<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Test\Controller;

use ACP3\Core\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DisplayActionTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DisplayActionTraitImpl
     */
    private $displayAction;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    protected function setUp()
    {
        $this->setUpMockObjects();
        $this->displayAction = new DisplayActionTraitImpl(
            $this->responseMock,
            $this->viewMock
        );
    }

    private function setUpMockObjects()
    {
        $this->responseMock = $this->createMock(Response::class);
        $this->responseMock->headers = new ResponseHeaderBag();
        $this->viewMock = $this->createMock(View::class);
    }

    public function testDisplayWithResponseObjActionResult()
    {
        $actionResult = new Response();
        $actionResult->setContent('foo-bar-baz');

        $actual = $this->displayAction->display($actionResult);
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertSame($actionResult, $actual);
    }

    public function testDisplayWithStringActionResult()
    {
        $actionResult = 'foo-bar-baz';

        $this->setUpResponseMockExpectations($actionResult);

        $actual = $this->displayAction->display($actionResult);
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals($actionResult, $actual->getContent());
    }

    /**
     * @param string $actionResult
     */
    private function setUpResponseMockExpectations($actionResult)
    {
        $this->responseMock->expects($this->once())
            ->method('setContent')
            ->with($actionResult)
            ->willReturnSelf();
        $this->responseMock->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn($actionResult);
    }

    public function testDisplayWithVoidActionResult()
    {
        $templateOutput = 'foo-bar-baz';
        $this->setUpResponseMockExpectations($templateOutput);
        $this->setUpViewMockExpectations($templateOutput);
        $actionResult = null;

        $actual = $this->displayAction->display($actionResult);
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals($templateOutput, $actual->getContent());
    }

    /**
     * @param string $templateOutput
     * @param array  $tplVars
     */
    private function setUpViewMockExpectations($templateOutput, array $tplVars = [])
    {
        $this->viewMock->expects($this->once())
            ->method('fetchTemplate')
            ->with('Foo/Frontend/index.index.tpl')
            ->willReturn($templateOutput);
        if (!empty($tplVars)) {
            $this->viewMock->expects($this->once())
                ->method('assign')
                ->with($tplVars)
                ->willReturn($this->returnSelf());
        }
    }

    public function testDisplayWithArrayActionResult()
    {
        $templateOutput = 'foo-bar-baz-array';
        $actionResult = [
            'lorem-ispum' => 'lorem ipsum dolor',
        ];

        $this->setUpResponseMockExpectations($templateOutput);
        $this->setUpViewMockExpectations($templateOutput, $actionResult);

        $actual = $this->displayAction->display($actionResult);
        $this->assertInstanceOf(Response::class, $actual);
        $this->assertEquals($templateOutput, $actual->getContent());
    }
}
