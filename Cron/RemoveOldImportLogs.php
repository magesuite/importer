<?php

namespace MageSuite\Importer\Cron;

class RemoveOldImportLogs
{
    const LOG_TABLE = 'import_log';

    const LOG_STEP_TABLE = 'import_log_step';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\Importer\Helper\Config $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
    }

    public function execute()
    {
        $firstRow = $this->getFirstRowToDelete();
        if ($firstRow) {
            $importId = $firstRow['import_id'];
            $this->resourceConnection->getConnection()->delete(self::LOG_TABLE, ['import_id <= ?' => $importId ]);
            $this->resourceConnection->getConnection()->delete(self::LOG_STEP_TABLE, ['import_id <= ?' => $importId ]);
        }
    }

    protected function getFirstRowToDelete()
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(self::LOG_STEP_TABLE)
            ->where('started_at < ?', [$this->getClearOlderThan()])
            ->order('import_id DESC')
            ->limit(1);

        return $this->resourceConnection->getConnection()->fetchRow($select);
    }

    protected function getClearOlderThan()
    {
        $clearOlderThan = $this->config->getDeleteOlderThanValue();
        return date('Y-m-d', strtotime(date('Y-m-d') . '-' . $clearOlderThan . ' days'));
    }
}
