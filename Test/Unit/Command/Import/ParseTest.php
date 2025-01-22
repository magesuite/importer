<?php

namespace MageSuite\Importer\Test\Unit\Command\Import;

class ParseTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\Import\Parse $command = null;
    protected ?\Magento\Framework\ObjectManagerInterface $objectManagerMock = null;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMock();
        $this->command = $objectManager->create(\MageSuite\Importer\Command\Import\Parse::class, [
            'objectManager' => $this->objectManagerMock,
            'outputFactory' => $objectManager->get(\MageSuite\Importer\Model\Command\OutputFactory::class),
        ]);
    }

    public function testItImplementsCommandInterface()
    {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItCreatesParserClassAndExecutesParsing()
    {
        $parser = $this->getMockBuilder(\MageSuite\Importer\Command\Parser::class)->getMock();

        $configuration = [
            'class' => 'Namespace\Parser',
        ];

        $this->objectManagerMock->expects($this->once())->method('create')->with('Namespace\Parser')->willReturn($parser);

        $parser->expects($this->once())->method('parse')->with($configuration);

        $this->command->execute($configuration);
    }
}
