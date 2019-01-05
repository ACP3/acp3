<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Test\DataGrid\ColumnRenderer;

use ACP3\Core\DataGrid\ColumnRenderer\Nl2pColumnRenderer;
use ACP3\Core\Helpers\StringFormatter;
use Cocur\Slugify\Slugify;

class Nl2pColumnRendererTest extends AbstractColumnRendererTest
{
    /**
     * @var StringFormatter
     */
    protected $stringFormatter;

    protected function setUp()
    {
        $this->stringFormatter = new StringFormatter(new Slugify());

        $this->columnRenderer = new Nl2pColumnRenderer($this->stringFormatter);

        parent::setUp();
    }

    public function testValidField()
    {
        $this->columnData = \array_merge($this->columnData, [
            'fields' => ['text'],
        ]);
        $this->dbData = [
            'text' => 'Lorem Ipsum',
        ];

        $expected = '<td><p>Lorem Ipsum</p></td>';
        $this->compareResults($expected);
    }

    public function testValidFieldWithMultipleLines()
    {
        $this->columnData = \array_merge($this->columnData, [
            'fields' => ['text'],
        ]);
        $this->dbData = [
            'text' => "Lorem Ipsum\n\nDolor",
        ];

        $expected = "<td><p>Lorem Ipsum</p>\n<p>Dolor</p></td>";
        $this->compareResults($expected);
    }
}