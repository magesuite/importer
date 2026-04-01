<?php

declare(strict_types=1);

namespace MageSuite\Importer\Model\Command;

class CleanLogs
{
    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    /**
     * @var \MageSuite\Importer\Model\ResourceModel\ImportStep
     */
    protected $importStep;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $connection;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \MageSuite\Importer\Model\ResourceModel\ImportStep $importStep,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->config = $config;
        $this->importStep = $importStep;
        $this->connection = $resource->getConnection();
    }

    public function execute(int $loggingRetentionPeriod): void
    {
        $importRow = $this->importStep->getFirstRowOlderThan($loggingRetentionPeriod);

        if ($importRow['import_id']) {
            $tableName = $this->connection->getTableName('import_log');
            $this->connection->delete(
                $tableName,
                $this->connection->quoteInto('import_id < ?', $importRow['import_id'], \Zend_Db::INT_TYPE)
            );
        }
    }
}
