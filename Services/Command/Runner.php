<?php

namespace MageSuite\Importer\Services\Command;

class Runner
{
    const DEFAULT_AMOUNT_OF_RETRIES = 5;

    private $configuration;

    private $steps;

    /**
     * @var \MageSuite\Importer\Command\CommandFactory
     */
    private $commandFactory;

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    private $importRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     *
     * @param \MageSuite\Importer\Command\CommandFactory $commandFactory
     */
    public function __construct(
        \MageSuite\Importer\Command\CommandFactory $commandFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->commandFactory = $commandFactory;
        $this->importRepository = $importRepository;
        $this->eventManager = $eventManager;
    }

    public function runCommand($importId, $importIdentifier, $stepIdentifier)
    {
        $this->configuration = $this->importRepository->getConfigurationById($importIdentifier);
        $this->steps = $this->importRepository->getStepsByImportId($importId);

        if (empty($this->steps)) {
            throw new \InvalidArgumentException("Specified import has no steps defined");
        }

        /** @var \MageSuite\Importer\Model\ImportStep $step */
        foreach ($this->steps as $step) {
            if ($step->getIdentifier() == $stepIdentifier) {
                $this->runStepCommand($step);
                break;
            }
        }
    }


    /**
     * @param $configuration
     * @param $step
     */
    private function runStepCommand($step)
    {
        $stepDefinition = $this->configuration['steps'][$step->getIdentifier()];

        $commandType = $stepDefinition['type'];

        $stepConfiguration = isset($stepDefinition['configuration']) ? $stepDefinition['configuration'] : [];

        $command = $this->commandFactory->create($commandType);

        if ($command == null) {
            throw new \InvalidArgumentException(sprintf("Command with type %s does not exist.", $commandType));
        }

        $this->eventManager->dispatch('import_command_executes', ['step' => $step]);

        $attempt = 1;

        while(true) {
            try {
                $output = $command->execute($stepConfiguration);

                $this->eventManager->dispatch('import_command_done', ['step' => $step, 'output' => $output]);

                break;
            } catch (\Exception $e) {
                $this->eventManager->dispatch('import_command_error', ['step' => $step, 'error' => $e->getMessage()]);

                if($attempt == $this->getAmountOfRetries($stepConfiguration)) {
                    throw $e;
                }

                $attempt++;
            }
        }
    }

    public function getAmountOfRetries($stepConfiguration) {
        if(isset($stepConfiguration['amount_of_retries']) && is_numeric($stepConfiguration['amount_of_retries'])) {
            return $stepConfiguration['amount_of_retries'];
        }

        return self::DEFAULT_AMOUNT_OF_RETRIES;
    }
}