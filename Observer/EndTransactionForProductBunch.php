<?php

namespace MageSuite\Importer\Observer;

class EndTransactionForProductBunch implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var int
     */
    protected static $errorAmount = 0;

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
        $currentErrorAmount = count($adapter->getErrorAggregator()->getAllErrors());
        $hasNewErrors = self::$errorAmount < $currentErrorAmount;

        self::$errorAmount = $currentErrorAmount;
        return $hasNewErrors;
    }

    protected function doRollbackInImportAdapter(\Magento\CatalogImportExport\Model\Import\Product $adapter)
    {
        $adapter->getCategoryProcessor()->reinitializeCategories();
    }
}
