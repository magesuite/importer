<?php

namespace MageSuite\Importer\Services\Notification;

class ImportWatcher
{
    const LOCK_NAME = 'magesuite_importer';

    const DELAY_BETWEEN_CHECKS_IN_SECONDS = 600;

    protected \MageSuite\Importer\Model\Collections\ImportStep $importStepCollection;

    protected \MageSuite\Importer\Services\Notification\LockManager $lockManager;

    protected int $lastCheckTimestamp = 0;

    public function __construct(
        \MageSuite\Importer\Model\Collections\ImportStep $importStepCollection,
        \MageSuite\Importer\Services\Notification\LockManager $lockManager
    ) {
        $this->importStepCollection = $importStepCollection;
        $this->lockManager = $lockManager;
    }

    public function wasImportKilledByEarlyOom(): bool
    {
        $stepInProgress = $this->getCurrentlyRunningImportStep();
        if (!$stepInProgress) {
            return false;
        }

        if (!$this->lockManager->canAcquireLock($stepInProgress->getId())) {
            return false;
        }

        return true;
    }

    protected function getCurrentlyRunningImportStep()
    {
        $steps = $this->importStepCollection
            ->addFilter('status', \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS)
            ->getItems();

        return current($steps);
    }
}
