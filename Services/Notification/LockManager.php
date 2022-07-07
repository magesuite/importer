<?php

namespace MageSuite\Importer\Services\Notification;

class LockManager
{
    const LOCK_NAME = 'import_step_%s';

    const LOCK_TIMEOUT = '20';

    protected \Magento\Framework\Lock\LockManagerInterface $lockProxy;

    public function __construct(\Magento\Framework\Lock\LockManagerInterface $lockProxy)
    {
        $this->lockProxy = $lockProxy;
    }

    public function canAcquireLock($stepId): bool
    {
        $lockName = $this->getLockName($stepId);
        $isLocked = $this->lockProxy->isLocked($lockName);

        return !$isLocked;
    }

    public function lock($stepId)
    {
        $lockName = $this->getLockName($stepId);
        $this->lockProxy->lock($lockName);
    }

    public function unlock($stepId)
    {
        $lockName = $this->getLockName($stepId);
        $this->lockProxy->lock($lockName);
    }

    protected function getLockName($stepId)
    {
        return sprintf(self::LOCK_NAME, $stepId);
    }
}
