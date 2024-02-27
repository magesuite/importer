<?php

namespace MageSuite\Importer\Test\Unit\Services\Command;

class RunnerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\PHPUnit\Framework\MockObject\MockObject $commandMock = null;
    protected ?\MageSuite\Importer\Services\Command\Runner $commandRunner = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $commandFactoryStub = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $importRepositoryStub = null;
    protected ?\MageSuite\Importer\Test\Unit\Services\Command\EventManagerFake $eventManagerFake = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $lockManagerMock = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $loggerMock = null;

    public function setUp(): void
    {
        $this->commandFactoryStub = $this->getMockBuilder(\MageSuite\Importer\Command\CommandFactory::class)->getMock();
        $this->commandMock = $this->getMockBuilder(\MageSuite\Importer\Command\Command::class)->getMock();
        $this->importRepositoryStub = $this->getMockBuilder(\MageSuite\Importer\Api\ImportRepositoryInterface::class)->getMock();
        $this->eventManagerFake = new \MageSuite\Importer\Test\Unit\Services\Command\EventManagerFake();
        $lockManager = $this->getMockBuilder(\Magento\Framework\Lock\LockManagerInterface::class)->getMock();
        $this->lockManagerMock = $this->getMockBuilder(\MageSuite\Importer\Services\Notification\LockManager::class)
            ->setConstructorArgs([$lockManager])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->commandRunner = new \MageSuite\Importer\Services\Command\Runner(
            $this->commandFactoryStub,
            $this->importRepositoryStub,
            $this->eventManagerFake,
            $this->lockManagerMock,
            $this->loggerMock
        );
    }

    public function testItRunsCommand()
    {
        $importIdentifier = 'import_identifier';
        $importId = 'import_id';

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_DONE, 'download', 1],
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'validate', 2]
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => [
                'download' => ['type' => 'download'],
                'validate' => ['type' => 'validate'],
            ]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);
        $this->lockManagerMock->method('canAcquireLock')->with(2)->willReturn(true);

        $this->commandFactoryStub
            ->method('create')
            ->with('validate')
            ->willReturn($this->commandMock);

        $this->commandMock
            ->expects($this->once())
            ->method('execute');

        $this->commandRunner->runCommand($importId, $importIdentifier, 'validate');
    }

    public function testItDoesNotRunCommandWhenItsLocked()
    {
        $importIdentifier = 'import_identifier';
        $importId = 'import_id';

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_DONE, 'download', 1],
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => [
                'download' => ['type' => 'download'],
            ]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->lockManagerMock->method('canAcquireLock')->with(1)->willReturn(false);

        $this->loggerMock->expects($this->exactly(1))
            ->method('debug')
            ->with('Import step download tried to execute concurrently.');

        $this->commandFactoryStub
            ->method('create')
            ->with('download')
            ->willReturn($this->commandMock);

        $this->commandMock
            ->expects($this->exactly(0))
            ->method('execute');

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    public function testItThrowsEventWhenCommandRunningStarts()
    {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);

        $this->lockManagerMock->method('canAcquireLock')->with(1)->willReturn(true);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');

        $dispatchedEvents = $this->eventManagerFake->getDispatchedEvents();

        $this->assertEquals(
            [
                'eventName' => 'import_command_executes',
                'data' => [
                    'step' => $importStep,
                    'attempt' => 1
                ]
            ],
            $dispatchedEvents[0]
        );
    }

    public function testItThrowsEventWhenCommandIsFinished()
    {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';
        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);

        $this->commandMock->method('execute')->willReturn('output');
        $this->lockManagerMock->method('canAcquireLock')->with(1)->willReturn(true);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');

        $dispatchedEvents = $this->eventManagerFake->getDispatchedEvents();

        $this->assertEquals(
            [
                'eventName' => 'import_command_done',
                'data' => [
                    'step' => $importStep,
                    'output' => 'output'
                ]
            ],
            $dispatchedEvents[1]
        );
    }

    public function testItThrowsEventWhenCommandFailed()
    {
        $importId = 'import_id';
        $importIdentifier = 'import_identifier';
        $importStep = $this->prepareDoublesForEventTest($importId, $importIdentifier);
        $exceptionThrown = new \Exception('exception'); // phpcs:ignore
        $this->lockManagerMock->method('canAcquireLock')->with(1)->willReturn(true);

        $this->commandMock
            ->method('execute')
            ->will($this->throwException($exceptionThrown));

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');

        $dispatchedEvents = $this->eventManagerFake->getDispatchedEvents();

        $this->assertEquals(
            [
                'eventName' => 'import_command_error',
                'data' => [
                    'step' => $importStep,
                    'error' => 'exception',
                    'was_final_attempt' => false,
                    'attempt' => 1
                ]
            ],
            $dispatchedEvents[1]
        );
    }

    public function testItThrowsExceptionWhenThereAreNoStepsToRun()
    {
        $this->expectException(\InvalidArgumentException::class);

        $importId = 'import_id';
        $importIdentifier = 'import_identifier';
        $importSteps = [];

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => ''
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'download');
    }

    public function testItThrowsExceptionWhenCommandDoesNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);

        $importId = 'import_id';
        $importIdentifier = 'import_identifier';

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'validate', 1]
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->with($importIdentifier)->willReturn([
            'steps' => ['validate' => ['type' => 'not_existing_command']]
        ]);

        $this->importRepositoryStub->method('getStepsByImportId')->with($importId)->willReturn($importSteps);

        $this->lockManagerMock->method('canAcquireLock')->with(1)->willReturn(true);

        $this->commandFactoryStub
            ->method('create')
            ->willReturn(null);

        $this->commandRunner->runCommand($importId, $importIdentifier, 'validate');
    }

    /**
     * @return \MageSuite\Importer\Model\ImportStep
     */
    protected function createImportStepObject($status, $identifier, $id)
    {
        return \Magento\TestFramework\ObjectManager::getInstance()
            ->create(\MageSuite\Importer\Model\ImportStep::class)
            ->setId($id)
            ->setStatus($status)
            ->setIdentifier($identifier);
    }

    protected function createImportSteps($steps)
    {
        $importSteps = [];

        foreach ($steps as $step) {
            $importSteps[] = $this->createImportStepObject($step[0], $step[1], $step[2]);
        }

        return $importSteps;
    }

    /**
     * @param $importId
     * @return \MageSuite\Importer\Model\ImportStep
     */
    protected function prepareDoublesForEventTest($importId, $importIdentifier)
    {
        $importStep = $this->createImportStepObject(
            \MageSuite\Importer\Model\ImportStep::STATUS_PENDING,
            'download',
            1
        );

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
