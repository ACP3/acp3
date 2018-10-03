<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Test\View\Renderer\Smarty\Modifiers;

use ACP3\Core\Test\View\Renderer\Smarty\AbstractPluginTest;
use ACP3\Core\View\Renderer\Smarty\Modifiers\PrefixUri;

class PrefixUriTest extends AbstractPluginTest
{
    /**
     * @var PrefixUri
     */
    protected $plugin;

    protected function setUp()
    {
        $this->plugin = new PrefixUri();
    }

    public function testAddUriPrefix()
    {
        $value = 'www.example.com';
        $expected = 'http://www.example.com';
        $this->assertEquals($expected, $this->plugin->__invoke($value));
    }

    public function testAddUriPrefixNotNeeded()
    {
        $value = 'http://www.example.com';
        $expected = 'http://www.example.com';
        $this->assertEquals($expected, $this->plugin->__invoke($value));
    }

    /**
     * @return string
     */
    protected function getExpectedExtensionName()
    {
        return 'prefix_uri';
    }
}
