<?php

declare(strict_types=1);

namespace MageSuite\Importer\Observer\Command;

class CommandDoneObserver extends AbstractCommandResultObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\ImportStatus $importStatus,
        \Magento\Framework\Event\ManagerInterface $eventManager,
    ) {
        parent::__construct($importRepository, $importStatus);
        $this->eventManager = $eventManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $output = $observer->getData('output');

        if (!$output instanceof \MageSuite\Importer\Model\Command\Output) {
            $output = new \MageSuite\Importer\Model\Command\Output(['message' => $output]);
        }

        $step->setStatus($output->getStatus());
        $step->setOutput($output->getMessage());
        $step->setFinishedAt(time());

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());

        if ($output->getStatus() === \MageSuite\Importer\Model\ImportStep::STATUS_WARNING) {
            $this->eventManager->dispatch('import_command_warning', ['step' => $step, 'output' => $output, 'was_final_attempt' => true]);
        }
    }
}
