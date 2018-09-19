<?php

namespace MageSuite\Importer\Test\Unit\Services\Command;

class CommandDispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Services\Command\Dispatcher
     */
    private $commandDispatcher;

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importRepositoryStub;

    /**
     * @var \Magento\Framework\App\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shellMock;

    public function setUp() {
        $this->importRepositoryStub = $this
            ->getMockBuilder(\MageSuite\Importer\Api\ImportRepositoryInterface::class)
            ->getMock();

        $this->shellMock = $this
            ->getMockBuilder(\Magento\Framework\App\Shell::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commandDispatcher = new \MageSuite\Importer\Services\Command\Dispatcher(
            $this->importRepositoryStub,
            $this->shellMock
        );
    }

    public function testItFinishesWorkWhenThereIsNoActiveImport() {
        $this->importRepositoryStub
            ->method('getActiveImport')
            ->willReturn($this->createImportObject(0));

        $this->assertNull($this->commandDispatcher->dispatch());
    }

    public function testItDispatchesAllCommandsThatArePossibleToRun() {
        $importId = 1;

        $importSteps = $this->createImportSteps([
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'download'],
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'download_images'],
            [\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, 'validate'],
        ]);

        $this->importRepositoryStub->method('getConfigurationById')->willReturn([
            'steps' => [
                'download' => ['type' => 'download'],
                'download_images' => ['type' => 'download_images'],
                'validate' => ['type' => 'validate', 'depends' => 'download'],
            ]
        ]);

        $this->shellMock->expects($this->exactly(2))->method('execute')->withConsecutive(
            [BP.'/bin/magento importer:import:run_step %s %s &', [1, 'download']],
            [BP.'/bin/magento importer:import:run_step %s %s &', [1, 'download_images']]
        );

        $this->importRepositoryStub
            ->method('getActiveImport')
            ->willReturn($this->createImportObject($importId));

        $this->importRepositoryStub
            ->method('getStepsByImportId')
            ->with($importId)
            ->willReturn($importSteps);

        $this->commandDispatcher->dispatch();
    }

    private function createImportObject($importId) {
        return \Magento\TestFramework\ObjectManager::getInstance()
            ->create(\MageSuite\Importer\Model\Import::class)
            ->setId($importId);
    }

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
}