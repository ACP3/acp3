<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Seo\Test\Core\Router;

use ACP3\Core\Modules\Modules;
use ACP3\Modules\ACP3\Seo\Cache\SeoCacheStorage;
use ACP3\Modules\ACP3\Seo\Core\Router\Aliases;

/**
 * Class AliasesTest
 * @package ACP3\Modules\ACP3\Seo\Test\Core\Router
 */
class AliasesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ACP3\Modules\ACP3\Seo\Core\Router\Aliases
     */
    private $aliases;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $seoCacheMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $modulesMock;

    protected function setUp()
    {
        $this->seoCacheMock = $this->getMockBuilder(SeoCacheStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCache'])
            ->getMock();
        $this->modulesMock = $this->getMockBuilder(Modules::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMock();
        $this->modulesMock->expects($this->once())
            ->method('isActive')
            ->willReturn(true);

        $this->aliases = new Aliases($this->modulesMock, $this->seoCacheMock);
    }

    public function testGetUriAliasNoAliasFound()
    {
        $this->setUpSeoCacheExpectations([]);

        $path = 'foo/bar/baz';
        $this->assertEquals('foo/bar/baz/', $this->aliases->getUriAlias($path));
    }

    /**
     * @param array $expectedReturn
     */
    private function setUpSeoCacheExpectations(array $expectedReturn)
    {
        $this->seoCacheMock
            ->expects($this->once())
            ->method('getCache')
            ->willReturn($expectedReturn);
    }

    public function testGetUriAliasNoAliasFoundReturnEmpty()
    {
        $this->setUpSeoCacheExpectations([]);

        $path = 'foo/bar/baz';
        $this->assertEquals('', $this->aliases->getUriAlias($path, true));
    }

    public function testGetUriAliasAliasExists()
    {
        $this->setUpSeoCacheExpectations([
            'foo/bar/baz/' => [
                'alias' => 'lorem-ipsum-dolor'
            ]
        ]);

        $path = 'foo/bar/baz';
        $this->assertEquals('lorem-ipsum-dolor', $this->aliases->getUriAlias($path));
    }

    public function testUriAliasExistsNoAliasExists()
    {
        $this->setUpSeoCacheExpectations([]);

        $path = 'foo/bar/baz';
        $this->assertFalse($this->aliases->uriAliasExists($path));
    }

    public function testUriAliasExistsAliasExists()
    {
        $this->setUpSeoCacheExpectations([
            'foo/bar/baz/' => [
                'alias' => 'lorem-ipsum-dolor'
            ]
        ]);

        $path = 'foo/bar/baz';
        $this->assertTrue($this->aliases->uriAliasExists($path));
    }
}
