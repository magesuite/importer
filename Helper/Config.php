<?php

namespace MageSuite\Importer\Helper;

class Config
{
    const FAILED_IMPORT_THRESHOLD_XML_PATH = 'importer/configuration/failed_import_threshold';
    const USE_CRON_TO_RUN_STEPS_XML_PATH = 'importer/configuration/use_cron_to_run_steps';
    const XML_PATH_USE_TRANSACTIONS = 'importer/configuration/use_transactions';

    const XML_PATH_LOGS_ENABLE_CLEARING = 'importer/logs/enable_clearing';
    const XML_PATH_LOGS_DELETE_OLDER_THAN = 'importer/logs/delete_older_than';

    const XML_PATH_ADMIN_NOTIFICATION_SENDER_NAME = 'trans_email/importer_email/name';
    const XML_PATH_ADMIN_NOTIFICATION_EMAILS = 'trans_email/importer_email/email';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getFailedImportThreshold()
    {
        return $this->scopeConfig->getValue(self::FAILED_IMPORT_THRESHOLD_XML_PATH);
    }

    public function shouldUseCronToRunSteps()
    {
        return $this->scopeConfig->getValue(self::USE_CRON_TO_RUN_STEPS_XML_PATH);
    }

    public function shouldUseTransactions()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_USE_TRANSACTIONS);
    }

    public function shouldLogsBeCleared()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LOGS_ENABLE_CLEARING);
    }

    public function getDeleteOlderThanValue()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LOGS_DELETE_OLDER_THAN);
    }

    public function getAdminNotificationSenderName(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ADMIN_NOTIFICATION_SENDER_NAME);
    }

    public function getAdminNotificationEmails(): ?array
    {
        $storeAdminEmails =  $this->scopeConfig->getValue(self::XML_PATH_ADMIN_NOTIFICATION_EMAILS);
        return $storeAdminEmails ? array_map('trim', explode("\n", $storeAdminEmails)) : null;
    }
}
