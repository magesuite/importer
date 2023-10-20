<?php

namespace MageSuite\Importer\Helper;

class Config
{
    public const FAILED_IMPORT_THRESHOLD_XML_PATH = 'importer/configuration/failed_import_threshold';
    public const USE_CRON_TO_RUN_STEPS_XML_PATH = 'importer/configuration/use_cron_to_run_steps';
    public const XML_PATH_USE_TRANSACTIONS = 'importer/configuration/use_transactions';

    public const XML_PATH_LOGS_ENABLE_CLEARING = 'importer/logs/enable_clearing';
    public const XML_PATH_LOGS_DELETE_OLDER_THAN = 'importer/logs/delete_older_than';

    public const XML_PATH_ADMIN_NOTIFICATION_SENDER_NAME = 'trans_email/importer_email/name';
    public const XML_PATH_ADMIN_NOTIFICATION_EMAILS = 'trans_email/importer_email/email';

    public const INDEXER_ENABLED_XML_SECTION = 'indexer/indexing';
    public const INDEXER_ENABLED_XML_PATH = 'indexer/indexing/enabled';

    protected \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory;
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->configCollectionFactory = $configCollectionFactory;
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

    public function isIndexerEnabled()
    {
        $indexerConfig = $this->getIndexerConfigFromDatabase();
        return empty($indexerConfig) ? false : $indexerConfig->getValue() === '1';
    }

    public function getIndexerConfigFromDatabase()
    {
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addScopeFilter(
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0,
            self::INDEXER_ENABLED_XML_SECTION
        )->addFieldToFilter('path', ['eq' => self::INDEXER_ENABLED_XML_PATH]);

        return current($configCollection->getItems());
    }
}
