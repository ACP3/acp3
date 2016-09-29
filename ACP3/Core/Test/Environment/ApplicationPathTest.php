<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Test\Environment;


use ACP3\Core\Environment\ApplicationMode;
use ACP3\Core\Environment\ApplicationPath;

class ApplicationPathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationPath
     */
    private $appPath;

    protected function setUp()
    {
        $this->appPath = new ApplicationPath(ApplicationMode::DEVELOPMENT);
    }

    public function testSetDesignPathAbsolute()
    {
        $designPathAbsolute = 'http://example.com/test';

        $this->appPath->setDesignPathAbsolute($designPathAbsolute);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSetDesignPathAbsolute()
    {
        $invalidDesignPathAbsolute = 'http//example.com/test';

        $this->appPath->setDesignPathAbsolute($invalidDesignPathAbsolute);
    }
}
