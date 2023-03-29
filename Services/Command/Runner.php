<?php

namespace MageSuite\Importer\Services\Command;

class Runner
{
    const DEFAULT_AMOUNT_OF_RETRIES = 5;

    /**
     * @var \MageSuite\Importer\Services\Notification\LockManager
     */
    protected $lockManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $configuration;

    protected $steps;

    /**
     * @var \MageSuite\Importer\Command\CommandFactory
     */
    protected $commandFactory;

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    protected $importRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @param \MageSuite\Importer\Command\CommandFactory $commandFactory
     */
    public function __construct(
        \MageSuite\Importer\Command\CommandFactory $commandFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageSuite\Importer\Services\Notification\LockManager $lockManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->commandFactory = $commandFactory;
        $this->importRepository = $importRepository;
        $this->eventManager = $eventManager;
        $this->lockManager = $lockManager;
        $this->logger = $logger;
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
        if (!$this->lockManager->canAcquireLock($step->getId())) {
            $this->logger->debug(sprintf('Import step %s tried to execute concurrently.', $step->getIdentifier()));
            return;
        }

        $this->lockManager->lock($step->getId());

        $stepDefinition = $this->configuration['steps'][$step->getIdentifier()];

        $commandType = $stepDefinition['type'];

        $stepConfiguration = isset($stepDefinition['configuration']) ? $stepDefinition['configuration'] : [];

        $command = $this->commandFactory->create($commandType);

        if ($command == null) {
            throw new \InvalidArgumentException(sprintf("Command with type %s does not exist.", $commandType));
        }

        $attempt = $step->getRetriesCount()+1;

        $this->eventManager->dispatch('import_command_executes', ['step' => $step, 'attempt' => $attempt]);

        try {
            $output = $command->execute($stepConfiguration);
            $this->eventManager->dispatch('import_command_done', ['step' => $step, 'output' => $output]);
        } catch (\Exception $e) {
            $wasFinalAttempt = (bool)($attempt == $this->getAmountOfRetries($stepConfiguration));
            $this->eventManager->dispatch('import_command_error', ['attempt' => $attempt, 'step' => $step, 'error' => $e->getMessage(), 'was_final_attempt' => $wasFinalAttempt]);
        }

        $this->lockManager->unlock($step->getId());
    }

    public function getAmountOfRetries($stepConfiguration)
    {
        if (isset($stepConfiguration['amount_of_retries']) && is_numeric($stepConfiguration['amount_of_retries'])) {
            return $stepConfiguration['amount_of_retries'];
        }

        return self::DEFAULT_AMOUNT_OF_RETRIES;
    }
}
