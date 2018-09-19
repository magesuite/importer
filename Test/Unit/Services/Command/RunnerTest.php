<?php

namespace MageSuite\Importer\Test\Unit\Services\Command;

class RunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var
     */
    private $commandMock;

    /**
     * @var \MageSuite\Importer\Services\Command\Runner
     */
    private $commandRunner;

    /**
     * @var
     */
    private $commandFactoryStub;


    /**
     * @var
     */
    private $importRepositoryStub;

    /**
     * @var
     */
    private $eventManagerMock;

    public function setUp() {
        $this->commandFactoryStub = $this
            ->getMockBuilder(\MageSuite\Importer\Command\CommandFactory::class)
            ->getMock();

        $this->commandMock = $this->getMockBuilder(\MageSuite\Importer\Command\Command::class)->getMock();

        $this->importRepositoryStub = $this->getMockBuilder(\MageSuite\Importer\Api\ImportRepositoryInterface::class)->getMock();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)->getMock();

        $this->commandRunner = new \MageSuite\Importer\Services\Command\Runner(
            $this->commandFactoryStub,
            $this->importRepositoryStub,
            $this->eventManagerMock
        );
    }

    public function testItRunsCommand() {
        $importIdentifier = 'import_identifier';
        $importId = 'import_id';

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_DONE, 'download'],
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'validate']
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => [
                'download' => ['type' => 'download'],
                'validate' => ['type' => 'validate'],
            ]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->commandFactoryStub
            ->method('create')
            ->with('validate')
            ->willReturn($this->commandMock);

        $this->commandMock
            ->expects($this->once())
            ->method('execute');

        $this->commandRunner->runCommand($importId, $importIdentifier, 'validate');
    }



    public function testItThrowsEventWhenCommandRunningStarts() {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);

        $this->eventManagerMock
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('import_command_executes', ['step' => $importStep]);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    public function testItThrowsEventWhenCommandIsFinished() {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);


        $this->commandMock->method('execute')->willReturn('output');

        $this->eventManagerMock
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('import_command_done', ['step' => $importStep, 'output' => 'output']);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    /**
     * @expectedException \Exception
     */
    public function testItThrowsEventWhenCommandFailed() {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);

        $exceptionThrown = new \Exception('exception');

        $this->commandMock
            ->method('execute')
            ->will($this->throwException($exceptionThrown));

        $this->eventManagerMock
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('import_command_error', ['step' => $importStep, 'error' => 'exception']);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenThereAreNoStepsToRun() {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importSteps = [];

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => ''
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItThrowsExceptionWhenCommandDoesNotExist() {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'validate']
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => ['validate' => ['type' => 'not_existing_command']]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->commandFactoryStub
            ->method('create')
            ->willReturn(null);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'validate');
    }

    /**
     * @return \MageSuite\Importer\Model\ImportStep
     */
    private function createImportStepObject($status, $identifier) {
        return \Magento\TestFramework\ObjectManager::getInstance()
            ->create(\MageSuite\Importer\Model\ImportStep::class)
            ->setStatus($status)
            ->setIdentifier($identifier);
    }

    private function createImportSteps($steps) {
        $importSteps = [];

        foreach($steps as $step) {
            $importSteps[] = $this->createImportStepObject($step[0], $step[1]);
        }

        return $importSteps;
    }

    /**
     * @param $importId
     * @return \MageSuite\Importer\Model\ImportStep
     */
    private function prepareDoublesForEventTest($importId, $importIdentifier)
    {
        $importStep = $this->createImportStepObject(\MageSuite\Importer\Model\ImportStep::STATUS_PENDING,
            'download');

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => [
                'download' => ['type' => 'download'],
            ]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn([$importStep]);

        $this->commandFactoryStub
            ->method('create')
            ->willReturn($this->commandMock);

        return $importStep;
    }
}