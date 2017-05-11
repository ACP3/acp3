<?php
namespace ACP3\Core\Test\Helpers;

use ACP3\Core\ACL;
use ACP3\Core\Helpers\DataGrid;
use ACP3\Core\Helpers\Formatter\MarkEntries;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;

class DataGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataGrid;
    /**
     * @var ACL|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclMock;
    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $langMock;
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;
    /**
     * @var DataGrid\ConfigProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProcessorMock;

    protected function setUp()
    {
        $this->aclMock = $this->getMockBuilder(ACL::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasPermission'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->langMock = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->setMethods(['t'])
            ->getMock();

        $this->configProcessorMock = $this->getMockBuilder(DataGrid\ConfigProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGrid = new DataGrid(
            $this->aclMock,
            $this->requestMock,
            $this->langMock,
            $this->configProcessorMock
        );

        $options = (new DataGrid\Options())
            ->setIdentifier('#data-grid');

        $this->dataGrid->setOptions($options);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getDefaultExpected()
    {
        return [
            'can_edit' => false,
            'can_delete' => false,
            'identifier' => 'data-grid',
            'header' => '',
            'config' => [],
            'results' => '',
            'num_results' => 0
        ];
    }

    public function testRenderWithDefaults()
    {
        $this->aclMock
            ->expects($this->exactly(2))
            ->method('hasPermission')
            ->willReturn(false);

        $expected = $this->getDefaultExpected();

        $this->assertEquals($expected, $this->dataGrid->render());
    }

    public function testRenderWithOneTextColumn()
    {
        $this->aclMock
            ->expects($this->exactly(2))
            ->method('hasPermission')
            ->willReturn(false);

        /** @var MarkEntries|\PHPUnit_Framework_MockObject_MockObject $markEntriesMock */
        $markEntriesMock = $this->getMockBuilder(MarkEntries::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGrid->registerColumnRenderer(new DataGrid\ColumnRenderer\HeaderColumnRenderer($markEntriesMock));
        $this->dataGrid->registerColumnRenderer(new DataGrid\ColumnRenderer\TextColumnRenderer());

        $this->dataGrid->addColumn([
            'label' => 'Foo',
            'fields' => ['title'],
            'type' => DataGrid\ColumnRenderer\TextColumnRenderer::class
        ], 10);

        $expected = array_merge(
            $this->getDefaultExpected(),
            [
                'header' => '<th>Foo</th>',
                'config' => [],
            ]
        );

        $this->assertEquals($expected, $this->dataGrid->render());
    }

    public function testRenderWithOneTextColumnAndData()
    {
        $data = [
            [
                'title' => 'Lorem Ipsum'
            ],
            [
                'title' => 'Lorem Ipsum Dolor'
            ]
        ];
        $this->aclMock
            ->expects($this->exactly(2))
            ->method('hasPermission')
            ->willReturn(false);

        /** @var MarkEntries|\PHPUnit_Framework_MockObject_MockObject $markEntriesMock */
        $markEntriesMock = $this->getMockBuilder(MarkEntries::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGrid->registerColumnRenderer(new DataGrid\ColumnRenderer\HeaderColumnRenderer($markEntriesMock));
        $this->dataGrid->registerColumnRenderer(new DataGrid\ColumnRenderer\TextColumnRenderer());

        $this->dataGrid->addColumn([
            'label' => 'Foo',
            'fields' => ['title'],
            'type' => DataGrid\ColumnRenderer\TextColumnRenderer::class
        ], 10);
        $this->dataGrid->setResults($data);

        $expected = array_merge(
            $this->getDefaultExpected(),
            [
                'header' => '<th>Foo</th>',
                'config' => [],
                'results' => "<tr><td>Lorem Ipsum</td></tr>\n<tr><td>Lorem Ipsum Dolor</td></tr>\n",
                'num_results' => 2
            ]
        );

        $this->assertEquals($expected, $this->dataGrid->render());
    }
}
