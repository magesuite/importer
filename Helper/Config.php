<?php

namespace MageSuite\Importer\Helper;

class Config
{
    const FAILED_IMPORT_THRESHOLD_XML_PATH = 'importer/configuration/failed_import_threshold';
    const USE_CRON_TO_RUN_STEPS_XML_PATH = 'importer/configuration/use_cron_to_run_steps';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getFailedImportThreshold() {
        return $this->scopeConfig->getValue(self::FAILED_IMPORT_THRESHOLD_XML_PATH);
    }

    public function shouldUseCronToRunSteps() {
        return $this->scopeConfig->getValue(self::USE_CRON_TO_RUN_STEPS_XML_PATH);
    }
}