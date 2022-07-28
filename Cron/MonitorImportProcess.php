<?php

namespace MageSuite\Importer\Cron;


class MonitorImportProcess
{
    protected \MageSuite\Importer\Services\Notification\ImportWatcher $importWatcher;

    protected \MageSuite\Importer\Services\Notification\EmailSender $emailNotifier;

    public function __construct(
        \MageSuite\Importer\Services\Notification\ImportWatcher $importWatcher,
        \MageSuite\Importer\Services\Notification\EmailSender $emailNotifier
    ) {
        $this->importWatcher = $importWatcher;
        $this->emailNotifier = $emailNotifier;
    }

    public function execute()
    {
        if ($this->importWatcher->wasImportKilledByEarlyOom()) {
            $this->emailNotifier->notify('Import has been killed by early OOM');
        }
    }
}
