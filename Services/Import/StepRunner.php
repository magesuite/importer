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
    public function runAllSteps(int $importId, \Symfony\Component\Console\Output\OutputInterface $output): void
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $import = $this->importRepositoryFactory->create()->getById($importId);

        if (!$import) {
            throw new \Exception('Import not found.');
        }

        if ($import->getStatus() != \MageSuite\Importer\Model\ImportStep::STATUS_PENDING) {
            throw new \Exception('Import was already started. Command aborted.');
        }

        $commandRunner = $this->commandRunnerFactory->create();
        $steps = $this->getSteps($importId);
        $output->writeln('Running ' . count($steps) . ' steps for import ' . $import->getId());

        foreach ($steps as $step) {
            /**
             * @var \MageSuite\Importer\Services\Command\Runner $commandRunner
             * @var \MageSuite\Importer\Model\Command\Output $commandOutput
             */
            $output->write($step->getIdentifier());
            $commandOutput = $commandRunner->runCommand($importId, $import->getImportIdentifier(), $step->getIdentifier());

            if (
                is_object($commandOutput) && !in_array($commandOutput->getStatus(), [
                    \MageSuite\Importer\Model\ImportStep::STATUS_DONE,
                    \MageSuite\Importer\Model\ImportStep::STATUS_WARNING
                ])
            ) {
                $output->write(" ✗\n");
                $output->writeln($commandOutput->getMessage());
                break;
            }

            $output->write(" ✓\n");
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
