<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model;

use ACP3\Core\Model\DataProcessor\ColumnType\RawColumnType;
use Psr\Container\ContainerInterface;

class DataProcessorTest extends \PHPUnit\Framework\TestCase
{
    use CreateRawColumnTypeMockTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ContainerInterface
     */
    private $columnTypeStrategyLocator;
    /**
     * @var DataProcessor
     */
    private $dataProcessor;

    protected function setup(): void
    {
        $this->columnTypeStrategyLocator = $this->createMock(ContainerInterface::class);
        $this->dataProcessor = new DataProcessor($this->columnTypeStrategyLocator);
    }

    public function testProcessColumnData(): void
    {
        $columnData = [
            'foo' => 'Lorem',
            'bar' => 'Ipsum',
            'baz' => 'Dolor',
        ];

        $columnConstraints = [
            'foo' => RawColumnType::class,
            'bar' => RawColumnType::class,
        ];

        $this->setUpColumnTypeStrategyFactoryExpectations();

        $expected = [
            'foo' => 'Lorem',
            'bar' => 'Ipsum',
        ];
        self::assertEquals($expected, $this->dataProcessor->escape($columnData, $columnConstraints));
    }

    private function setUpColumnTypeStrategyFactoryExpectations(): void
    {
        $columnTypeMock = $this->getRawColumnTypeInstance($this);

        $columnTypeMock
            ->expects(self::exactly(2))
            ->method('doEscape')
            ->with(self::logicalOr('Lorem', 'Ipsum'))
            ->willReturnOnConsecutiveCalls('Lorem', 'Ipsum');

        $this->columnTypeStrategyLocator
            ->expects(self::exactly(2))
            ->method('get')
            ->with(RawColumnType::class)
            ->willReturn($columnTypeMock);
    }
}
