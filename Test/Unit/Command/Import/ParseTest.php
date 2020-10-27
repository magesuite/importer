<?php

namespace MageSuite\Importer\Test\Unit\Command\Import;

class ParseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Command\Import\Parse
     */
    private $command;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    public function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMock();

        $this->command = new \MageSuite\Importer\Command\Import\Parse($this->objectManagerMock);
    }

    public function testItImplementsCommandInterface() {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItCreatesParserClassAndExecutesParsing() {
        $parser = $this->getMockBuilder(\MageSuite\Importer\Command\Parser::class)->getMock();

        $configuration = [
            'class' => 'Namespace\Parser',
        ];

        $this->objectManagerMock->expects($this->once())->method('create')->with('Namespace\Parser')->willReturn($parser);

        $parser->expects($this->once())->method('parse')->with($configuration);

        $this->command->execute($configuration);
    }
}
