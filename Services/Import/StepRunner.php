<?php

declare(strict_types=1);

namespace MageSuite\Importer\Services\Import;

class StepRunner
{
    protected \Magento\Framework\App\State $state;
    protected \MageSuite\Importer\Services\Command\RunnerFactory $commandRunnerFactory;
    protected \MageSuite\Importer\Api\ImportRepositoryInterfaceFactory $importRepositoryFactory;
    protected \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Command\RunnerFactory $commandRunnerFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterfaceFactory $importRepositoryFactory,
        \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory
    ) {
        $this->importStepCollectionFactory = $importStepCollectionFactory;
        $this->importRepositoryFactory = $importRepositoryFactory;
        $this->commandRunnerFactory = $commandRunnerFactory;
        $this->state = $state;
    }

    /**
     * @throws \Exception
     */
    public function runAllSteps(int $importId, ?\Symfony\Component\Console\Output\OutputInterface $output = null): void
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $import = $this->importRepositoryFactory->create()->getById($importId);

        if (!$import) {
            throw new \Exception('Import not found.');
        }

        if ($import->getStatus() != \MageSuite\Importer\Model\ImportStep::STATUS_PENDING) {
            throw new \Exception('Import was already started. Command aborted.');
        }

        $commandRunnerFactory = $this->commandRunnerFactory->create();

        foreach ($this->getSteps($importId) as $step) {
            $commandRunnerFactory->runCommand($importId, $import->getImportIdentifier(), $step->getIdentifier());

            if ($output) {
                $output->writeln("{$step->getIdentifier()} ✓");
            }
        }
    }

    protected function getSteps(int $importId): array
    {
        return $this->importStepCollectionFactory
            ->create()
            ->addFieldToSelect(['identifier', 'status'])
            ->addFilter('import_id', $importId)
            ->addFilter('status', \MageSuite\Importer\Model\ImportStep::STATUS_PENDING)
            ->getItems();
    }
}
