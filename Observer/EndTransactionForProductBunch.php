<?php

namespace MageSuite\Importer\Observer;

class EndTransactionForProductBunch implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\Importer\Helper\Config $config;

    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    public static array $bunch = [];

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->shouldUseTransactions()) {
            return;
        }

        /** @var \Magento\CatalogImportExport\Model\Import\Product $adapter */
        $adapter = $observer->getAdapter();
        $connection = $this->resourceConnection->getConnection();

        if ($this->isErrorInImportedBunch($adapter)) {
            $connection->rollBack();
            $this->doRollbackInImportAdapter($adapter);
        } else {
            $connection->commit();
        }
    }

    protected function isErrorInImportedBunch(\Magento\CatalogImportExport\Model\Import\Product $adapter) : bool
    {
        foreach (self::$bunch as $rowNumber => $rowData) {
            if ($adapter->getErrorAggregator()->isRowInvalid($rowNumber) ||
                $adapter->getErrorAggregator()->getErrorByRowNumber($rowNumber)
            ) {
                return true;
            }
        }

        return false;
    }

    protected function doRollbackInImportAdapter(\Magento\CatalogImportExport\Model\Import\Product $adapter)
    {
        $adapter->getCategoryProcessor()->reinitializeCategories();
        $adapter->getDataSourceModel()->deleteLastBunch();
    }
}
