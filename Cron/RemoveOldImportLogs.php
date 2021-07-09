<?php

namespace MageSuite\Importer\Cron;

class RemoveOldImportLogs
{
    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    /**
     * @var \MageSuite\Importer\Model\Command\CleanLogs
     */
    protected $cleanLogs;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \MageSuite\Importer\Model\Command\CleanLogs $cleanLogs
    ) {
        $this->config = $config;
        $this->cleanLogs = $cleanLogs;
    }

    public function execute()
    {
        $clearOlderThan = $this->config->getDeleteOlderThanValue();
        $this->cleanLogs->execute($clearOlderThan);
    }
}
