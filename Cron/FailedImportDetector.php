<?php

namespace MageSuite\Importer\Cron;

class FailedImportDetector
{
    /**
     * @var \MageSuite\Importer\Services\Import\FailedImportDetector
     */
    protected $failedImportDetector;

    public function __construct(\MageSuite\Importer\Services\Import\FailedImportDetector $failedImportDetector)
    {
        $this->failedImportDetector = $failedImportDetector;
    }

    public function execute()
    {
        $this->failedImportDetector->markFailedImports();
    }
}
