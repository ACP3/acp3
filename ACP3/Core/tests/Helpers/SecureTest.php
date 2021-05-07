<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

class SecureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    private $secureHelper;

    protected function setup(): void
    {
        $this->secureHelper = new Secure();
    }

    public function stripScriptDataProvider()
    {
        return [
            'single_line' => [
                'Foo Bar <script type="text/javascript">alert(\'huhu\')</script> Test',
                'Foo Bar  Test',
            ],
            'single_line_no_spaces' => [
                'Foo Bar<script type="text/javascript">alert(\'huhu\')</script>Test',
                'Foo BarTest',
            ],
            'multi_line' => [
                "Foo Bar\n\n<script type=\"text/javascript\">alert('huhu')</script>\nTest",
                "Foo Bar\n\n\nTest",
            ],
            'mixed_case' => [
                "Foo Bar\n\n<sCRipT type=\"text/javascript\">alert('huhu')</sCRipT>\nTest",
                "Foo Bar\n\n\nTest",
            ],
            'multiple_scripts' => [
                '<script type="text/javascript">alert(\'huhu\')</script> Foo Bar <script type="text/javascript">alert(\'huhu\')</script> Test <script type="text/javascript">alert(\'huhu\')</script>',
                ' Foo Bar  Test ',
            ],
        ];
    }

    /**
     * @dataProvider stripScriptDataProvider
     *
     * @param string $value
     * @param string $expected
     */
    public function testStrEncodeStripScriptSingleLine($value, $expected)
    {
        self::assertEquals($expected, $this->secureHelper->strEncode($value, true));
    }

    public function testSaltUniqueCharacters()
    {
        $length = 10;
        $salt = $this->secureHelper->salt($length);
        $saltArray = str_split($salt);

        self::assertEquals($length, \strlen($salt));
        self::assertCount($length, array_unique($saltArray));
    }
}
