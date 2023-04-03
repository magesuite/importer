<?php

namespace MageSuite\Importer\Test\Integration\Observer\Command;

class CommandErrorObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Observer\Command\CommandErrorObserver
     */
    protected $event;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->event = $this->objectManager->create(\MageSuite\Importer\Observer\Command\CommandErrorObserver::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadInProgressImport
     */
    public function testStepIsReturnedToPendingStateOnErrorForRetry()
    {
        $observer = $this->objectManager->create(\Magento\Framework\Event\Observer::class);

        $import = $this->getImport();
        $step = $this->getStep();

        $this->assertEquals(0, $step->getRetriesCount());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS, $step->getStatus());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS, $import->getStatus());

        $observer->setData('step', $step);
        $observer->setData('attempt', 1);
        $observer->setData('error', 'Error');
        $observer->setData('was_final_attempt', false);

        $this->event->execute($observer);

        $import = $this->getImport();
        $step = $this->getStep();

        $this->assertEquals(1, $step->getRetriesCount());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, $step->getStatus());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_PENDING, $import->getStatus());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadInProgressImport
     */
    public function testStepIsSetToErrorStateAfterFinalAttempt()
    {
        $observer = $this->objectManager->create(\Magento\Framework\Event\Observer::class);

        $import = $this->getImport();
        $step = $this->getStep();

        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS, $step->getStatus());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS, $import->getStatus());

        $observer->setData('step', $step);
        $observer->setData('attempt', 5);
        $observer->setData('error', 'Error');
        $observer->setData('was_final_attempt', true);

        $this->event->execute($observer);

        $import = $this->getImport();
        $step = $this->getStep();

        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR, $step->getStatus());
        $this->assertEquals(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR, $import->getStatus());
    }

    protected function getStep()
    {
        $import = $this->getImport();

        $importStep = $this->objectManager->get(\MageSuite\Importer\Model\ImportStep::class);
        $importStep->load($import->getId(), 'import_id');

        return $importStep;
    }

    protected function getImport()
    {
        $import = $this->objectManager->create(\MageSuite\Importer\Model\Import::class);
        $import->load('in_progress', 'hash');

        return $import;
    }

    public static function loadInProgressImport()
    {
        require __DIR__ . '/../../_files/in_progress_import.php';
    }
}
